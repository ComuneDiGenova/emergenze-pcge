#! /home/local/COMGE/egter01/miniconda3/bin/python
# -*- coding: utf-8 -*-
#   Gter Copyleft 2018
#   Roberto Marzocchi
#   Edit: 01-2023 - Simone Parmeggiani

import os, sys
import ssl
import urllib.request
import xml.etree.ElementTree as et

import psycopg2
import datetime

from dotenv import load_dotenv

import telepot
import json

# Il token non è aggiornato su GitHub per evitare usi impropri
load_dotenv(os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.env'))
TOKEN = os.getenv('EMERGENZE_BOT_TOKEN')
TOKENCOC = os.getenv('EMERGENZE_COC_BOT_TOKEN')

bot = telepot.Bot(TOKEN)
botCOC = telepot.Bot(TOKENCOC)

DB_HOST = os.getenv('conn_ip')
DB_NAME = os.getenv('conn_db')
DB_USER = os.getenv('conn_user')
DB_PASSWORD = os.getenv('conn_pwd')
DB_PORT = os.getenv('conn_port', 5432)

# Link
sito_allerta = "https://allertaliguria.regione.liguria.it"
abs_path_bollettini = "/opt/rh/httpd24/root/var/www/html"

# Elenco messaggi del bollettino ARPAL da scaricare, con relative sigle
messages = {'MessaggioProtezioneCivile': 'PC',      # Prot. Civ.
            'MessaggioMeteoARPAL': 'Met_A',         # Meteo ARPAL
            'MessaggioIdrologicoARPAL': 'Idr_A',    # Idrologico ARPAL
            'MessaggioNivologicoARPAL': 'Niv_A',    # Nivologico ARPAL
            }


def get_cursor(host=DB_HOST, dbname=DB_NAME, user=DB_USER, password=DB_PASSWORD, port=DB_PORT, autocommit=True):
    """
    Funzione di creazione di un cursore per il lancio query di psycopg
    """
    try:
        conn = psycopg2.connect(host=host, dbname=dbname, user=user, password=password, port=port)
        conn.autocommit = autocommit
        conn.cursor().execute("SET TIMEZONE TO 'Europe/Rome';")
        curr = conn.cursor()

        return curr
    except psycopg2.DatabaseError as e:
        print(f"Errore di connessione al database: {e}")
        raise


def urllibwrapper(url):
    """
    Questa funzione apre una connessione ad un sito web e scarica un file
    usando  urllib.request.urlopen
    Se la connessione fallisce, usa la libreria ssl per creare un nuovo contesto
    senza bypassando il certificato https
    """

    try:
        f = urllib.request.urlopen(url)
    except urllib.error.URLError:
        ctx = ssl.create_default_context()
        ctx.check_hostname = False
        ctx.verify_mode = ssl.CERT_NONE
        f = urllib.request.urlopen(url, context=ctx)

    return f


def messageDownloader(xml, name, abbr):
    """
    Questa funzione esegue il download di uno dei messaggi ARPAL presenti nel bollettino

    Args:
        xml (_type_): xml parsed con xml.etree.ElementTree
        name (_type_): Nome completo del bollettino da scaricare (come compare nell'xml)
        abbr (_type_): Nome abbreviato del bollettino da scaricare (come compare nell'xml)
    """

    for e in xml.findall(name):
        pdf = e.attrib['nomeFilePDF']
        try:
            emissione = e.attrib['dataEmissione']
        except KeyError:
            emissione = 'NULL'

        if pdf:
            scarica_bollettino(abbr, pdf, emissione)
        else:
            print(f"No file of type '{abbr}' to download")


def scarica_bollettino(tipo, nome, ora):
    """
    Questa funzione scarica un bollettino ARPAL sulla base dei parametri
    Nel caso si tratti di un messaggio di Protezione Civile, attiva i bot
    per inviare i messaggi di allerta e Convocazione COC

    Args:
        tipo (str): Abbreviazione del nome bollettino da scaricare
        nome (str): Campo 'nomeFilePDF' dell'xml
        ora (str): Campo 'dataEmissione' dell'xml

    Example:
    scarica_bollettino("PC", "protciv_131299.pdf", "NULL")
    """

    if not os.path.isfile("{}/bollettini/{}/{}".format(abs_path_bollettini, tipo, nome)):
        if ora != 'NULL':
            data_read = datetime.datetime.strptime(ora,"%Y%m%d%H%M")

        f = urllibwrapper("{}/docs/{}".format(sito_allerta, nome))

        data = f.read()

        with open("{}/bollettini/{}/{}".format(abs_path_bollettini, tipo, nome), "wb") as code:
            code.write(data)

        curr = get_cursor()
        if ora != 'NULL':
            query = "INSERT INTO eventi.t_bollettini(tipo, nomefile, data_ora_emissione) VALUES ('{}', '{}', '{}');".format(tipo, nome, data_read)
        else:
            query = "INSERT INTO eventi.t_bollettini(tipo, nomefile) VALUES ('{}', '{}');".format(tipo, nome)

        curr.execute(query)
        curr.close()

        print("Download of type {} completed...".format(tipo))
        print(datetime.datetime.now())

        # Send message with bot 
        if tipo == 'PC':
            print("Bollettino di PC")
            messaggio = f"{sito_allerta}/docs/{nome}"
            convoca_utenti_sistema(messaggio)
            convoca_coc(messaggio)

    else:
        print(f"File of type 'tipo' already downloaded") 


def convoca_utenti_sistema(messaggio):
    curr = get_cursor()

    # ciclo su tutte le chat_id
    query_chat_id = """SELECT telegram_id 
                        FROM users.v_utenti_sistema 
                        WHERE telegram_id !='' AND telegram_attivo='t' AND (id_profilo='1' or id_profilo ='2' or id_profilo ='3');"""
    curr.execute(query_chat_id)

    lista_chat_id = curr.fetchall()

    # per ogni chat id invio messaggio telegram "nuovo bollettino"
    for row in lista_chat_id:
        chat_id = row[0]
        print(chat_id)
        try:
            bot.sendMessage(chat_id, f"Nuovo bollettino Protezione civile!\n\n{messaggio}")
        except:
            print(f"Problema invio messaggio all' utente con chat_id = {chat_id}")
    curr.close()


def convoca_coc(messaggio):
    """
    Questa funzione raccoglie tutti i telegram_id presenti a sistema nella tabella users.utenti_coc
    e invia loro una notifica di Nuovo Bollettino di Protezione civile
    """
    
    curr = get_cursor()
    
    query_bollettino = "SELECT id from eventi.t_bollettini WHERE tipo='PC' ORDER BY id DESC LIMIT 1;"
    curr.execute(query_bollettino)
    id_bollettino = curr.fetchone()[0]
    print(f'Id bollettino: {id_bollettino}')

    query_coc= "SELECT telegram_id from users.utenti_coc;"
    curr.execute(query_coc)
    lista_coc = curr.fetchall()
    print('Lista utenti coc:', lista_coc)
    
    for row_coc in lista_coc:
        chat_id_coc=row_coc[0]
        try:
            msg_bollettino = os.popen(
                "curl -d '{\"chat_id\":%s, \"text\":\"Nuovo bollettino Protezione civile!\n\n%s\"}' "
                "-H \"Content-Type: application/json\" -X POST https://api.telegram.org/bot%s/sendMessage"
                % (chat_id_coc, messaggio, TOKENCOC)).read()
            msg_bollettino_j = json.loads(msg_bollettino)
            
            if msg_bollettino_j['ok'] == True:
                message_text = (
                    "Protezione Civile informa che è stato emanato lo stato di Allerta "
                    "meteorologica come indicato nel Messaggio allegato. Si prega di dare riscontro "
                    "al presente messaggio premendo il tasto OK sotto indicato"
                )

                inline_keyboard = {
                    "inline_keyboard": [[{"text": "OK", "callback_data": f"ricevuto"}]]
                }

                curl_command = f"""
                curl -d '{{
                    "chat_id": "{chat_id_coc}",
                    "text": "{message_text}",
                    "reply_markup": {json.dumps(inline_keyboard)}
                }}' -H "Content-Type: application/json" -X POST https://api.telegram.org/bot{TOKENCOC}/sendMessage
                """

                # query insert DB
                query_convocazione = f"""INSERT INTO users.t_lettura_bollettino(data_invio, id_telegram, id_bollettino) 
                                        VALUES (date_trunc('hour', NOW()) + date_part('minute', NOW())::int / 10 * interval '10 min', 
                                                {chat_id_coc}, {id_bollettino});"""
                curr.execute(query_convocazione)

                # Execute the command
                os.system(curl_command)
                print(inline_keyboard)

        except Exception as e:
            print(e)
            print(f"Problema invio messaggio all'utente del coc con chat_id={chat_id_coc}")

    curr.close()


def main():
    url=f"{sito_allerta}/xml/allertaliguria.xml"

    file = urllibwrapper(url)

    data = file.read()
    file.close()

    root = et.fromstring(data)

    nomefile = "{}/bollettini/allerte.txt".format(abs_path_bollettini)
    log_file_allerte = open(nomefile, "w")

    # stampo la data di emissione a log
    dataEmissione = datetime.datetime.strptime(root.attrib['dataEmissione'],"%Y%m%d%H%M")
    print("Ultimo aggiornamento: {}".format(dataEmissione))
    log_file_allerte.write("Ultimo aggiornamento: {}\n".format(dataEmissione))

    # DEBUG
    # scarica_bollettino("PC", "protciv_175989.pdf", "NULL")

    # Scarico i messaggi  
    for k, v in messages.items():
        messageDownloader(root, k, v)

    # Leggo allerte e compilo relativo log_file solo per prov. di Genova (Zona)
    for elem in root.findall('Zone'):
        for zone in elem.findall('Zona'):
            zona = zone.attrib["id"]

            if zona == 'B':
                for allerte in zone.findall('AllertaIdrogeologica'):
                    log_file_allerte.write('\n<br><b>Allerta Idrogeologica Zona B</b>')
                    log_file_allerte.write("\n<br>PioggeDiffuse={}".format(allerte.attrib['pioggeDiffuse']))
                    log_file_allerte.write("\n<br>Temporali={}".format(allerte.attrib['temporali']))
                    log_file_allerte.write("\n<br>Tendenza={}".format(allerte.attrib['tendenza']))

                for allerte in zone.findall('AllertaNivologica'):
                    log_file_allerte.write('\n<br><b>Allerta Nivologica Zona B</b>')
                    log_file_allerte.write("\n<br>Neve={}".format(allerte.attrib['neve']))
                    log_file_allerte.write("\n<br>Tendenza={}".format(allerte.attrib['tendenza']))

    log_file_allerte.close

def simula_nuovo_bollettino(bollettino='protciv_175989.pdf'):
    """
    bollettino: Nome del file PDF del bollettino (es. protciv_175989.pdf)
    1. Rimuovo file da percorso
    2. Rimuovo record da tabella
    3. Lancio funzione scarica_bollettino
    """
    
    # 1.
    try:
        os.remove(f"{abs_path_bollettini}/bollettini/PC/{bollettino}")
    except OSError:
        pass

    # 2. 
    rm_query = f"DELETE FROM eventi.t_bollettini WHERE nomefile='{bollettino}';"
    curr = get_cursor()
    curr.execute(rm_query)
    
    # 3.
    scarica_bollettino('PC', bollettino, 'NULL')
    
    print('\nFATTO!!')
    

if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description='Scarico bollettino meteo regionale.')
    
    parser.add_argument('--force-redownload', action=argparse.BooleanOptionalAction)

    args = parser.parse_args()
    
    if args.force_redownload:
        simula_nuovo_bollettino()
    else:

        # Cerco il percorso al file python
        path = os.path.dirname(os.path.abspath(__file__))

        # redirigo i print su un log message
        old_stdout = sys.stdout
        logfile = "{}/readxml.log".format(path)
        log_file = open(logfile, "w")
        sys.stdout = log_file
        
        print(datetime.datetime.now())
        main()

        # Chiusura file di log
        sys.stdout = old_stdout
        log_file.close()
