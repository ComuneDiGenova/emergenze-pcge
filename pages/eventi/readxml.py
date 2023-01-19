#! /usr/bin/env python
# -*- coding: utf-8 -*-
#   Gter Copyleft 2018
#   Roberto Marzocchi
#   Edit: 01-2023 - Simone Parmeggiani

import os, sys
import ssl
import urllib.request
import xml.etree.ElementTree as et

import psycopg2
from conn import *

import datetime
import telepot
import json

import config

# Il token non è aggiornato su GitHub per evitare usi impropri
TOKEN = config.TOKEN
TOKENCOC = config.TOKEN_COC

bot = telepot.Bot(TOKEN)
botCOC = telepot.Bot(TOKENCOC)

# Link
sito_allerta = "https://allertaliguria.regione.liguria.it"
abs_path_bollettini = "/opt/rh/httpd24/root/var/www/html"

# Elenco messaggi del bollettino ARPAL da scaricare, con relative sigle
messages = {'MessaggioProtezioneCivile': 'PC',      # Prot. Civ.
            'MessaggioMeteoARPAL': 'Met_A',         # Meteo ARPAL
            'MessaggioIdrologicoARPAL': 'Idr_A',    # Idrologico ARPAL
            'MessaggioNivologicoARPAL': 'Niv_A',    # Nivologico ARPAL
            }    

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
            print(data_read)
            
        f = urllibwrapper("{}/docs/{}".format(sito_allerta, nome))

        data = f.read()

        with open("{}/bollettini/{}/{}".format(abs_path_bollettini, tipo, nome), "wb") as code:
            code.write(data)
        conn = psycopg2.connect(host=ip, dbname=db, user=user, password=pwd, port=port)
        curr = conn.cursor()
        conn.autocommit = True
        if ora != 'NULL':
            query = "INSERT INTO eventi.t_bollettini(tipo, nomefile, data_ora_emissione)VALUES ('{}', '{}', '{}');".format(tipo, nome, data_read)
        else:
            query = "INSERT INTO eventi.t_bollettini(tipo, nomefile)VALUES ('{}', '{}');".format(tipo, nome)

        curr.execute(query)
        
        print("Download of type {} completed...".format(tipo))
        print(datetime.datetime.now())
        
        # Send message with bot 
        if tipo == 'PC':
            print("Bollettino di PC")
            messaggio = "{}/docs/{}".format(sito_allerta, nome)
            
            # ciclo su tutte le chat_id
            query_chat_id = "SELECT telegram_id from users.v_utenti_sistema where telegram_id !='' and telegram_attivo='t' and (id_profilo='1' or id_profilo ='2' or id_profilo ='3');"
            curr.execute(query_chat_id)
            # print(datetime.datetime.now())
            # print(query_chat_id)
            lista_chat_id = curr.fetchall() 

            for row in lista_chat_id:
                chat_id = row[0]
                # print(chat_id)
                try:
                    bot.sendMessage(chat_id, "Nuovo bollettino Protezione civile!\n\n{}".format(messaggio))
                except:
                    print("Problema invio messaggio all' utente con chat_id = {}".format(chat_id))

            
            query_coc= "SELECT telegram_id from users.utenti_coc;"
            curr.execute(query_coc)
            lista_coc = curr.fetchall() 
            for row_coc in lista_coc:
                chat_id_coc=row_coc[0]
                try:
                    msg_bollettino = os.popen("curl -d '{\"chat_id\":%s, \"text\":\"Nuovo bollettino Protezione civile!\n\n%s\"}' -H \"Content-Type: application/json\" -X POST https://api.telegram.org/bot%s/sendMessage"
                                              %(chat_id_coc, messaggio, TOKENCOC)).read()
                    msg_bollettino_j = json.loads(msg_bollettino)
                    if msg_bollettino_j['ok'] == True:
                        os.system("curl -d '{\"chat_id\":%s, \"text\":\"Protezione Civile informa che è stato emanato lo stato di Allerta meteorologica come indicato nel Messaggio allegato. Si prega di dare riscontro al presente messaggio premendo il tasto OK sotto indicato\", \"reply_markup\": {\"inline_keyboard\": [[{\"text\":\"OK\", \"callback_data\": \"ricevuto\"}]]} }' -H \"Content-Type: application/json\" -X POST https://api.telegram.org/bot%s/sendMessage"
                                  %(chat_id_coc, TOKENCOC))
                        
                        #query insert DB
                        query_convocazione="INSERT INTO users.t_convocazione(data_invio, id_telegram) VALUES (date_trunc('hour', now()) + date_part('minute', now())::int / 10 * interval '10 min', {});".format(chat_id_coc)
                        curr.execute(query_convocazione)
                except Exception as e:
                    print(e)
                    print('Problema invio messaggio all\'utente del coc con chat_id={}'.format(chat_id_coc))
                
    else:
        print("File of type '{}' already downloaded".format(tipo)) 


def main():
    url="{}/xml/allertaliguria.xml".format(sito_allerta);

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
    # scarica_bollettino("PC", "protciv_131299.pdf", "NULL")
    
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


if __name__ == "__main__":   
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
