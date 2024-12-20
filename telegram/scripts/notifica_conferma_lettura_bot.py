#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Gter copyleft 2021

import logging
import requests
import os
from dotenv import load_dotenv
import psycopg2
import emoji
from datetime import datetime, timedelta
import urllib.parse
import pytz


load_dotenv(os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.env'))

# Configure logging
logfile='{}/notifica_conferma_lettura_bot.log'.format(os.path.dirname(os.path.realpath(__file__)))
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.ERROR)

TOKEN=os.getenv('EMERGENZE_COC_BOT_TOKEN')

if not TOKEN:
    logging.error('EMERGENZE_COC_BOT_TOKEN non trovato. Assicurati che il file .env contenga la variabile TOKEN_COC.')
    raise ValueError('EMERGENZE_COC_BOT_TOKEN non trovato')

DB_HOST = os.getenv('conn_ip')
DB_NAME = os.getenv('conn_db')
DB_USER = os.getenv('conn_user')
DB_PASSWORD = os.getenv('conn_pwd')
DB_PORT = os.getenv('conn_port', 5432)

if not all([DB_HOST, DB_NAME, DB_USER, DB_PASSWORD]):
    logging.error('Parametri di connessione al database non trovati. Assicurati che il file .env contenga conn_ip, conn_db, conn_user, e conn_pwd.')
    raise ValueError('Parametri di connessione al database non trovati')

def telegram_bot_sendtext(bot_message,chat_id):
    
    urllib.parse.quote('/', safe='')
    send_text = 'https://api.telegram.org/bot' + TOKEN + '/sendMessage?chat_id=' + chat_id + '&parse_mode=Markdown&text=' + urllib.parse.quote(bot_message)
    response = requests.get(send_text)
    return response.json()


testo=f"""{emoji.emojize(":warning:",use_aliases=True)} {emoji.emojize(":bell:",use_aliases=True)} 
        Non è ancora stata inviata la conferma di avvenuta lettura dell'emanazione dello STATO di ALLERTA. 
        Si prega di dare riscontro alla comunicazione precedentemente inviata premendo il tasto OK."""
#telegram_bot_sendtext(testo,'306530623')

try:
    con=psycopg2.connect(
        host=DB_HOST,
        dbname=DB_NAME,
        user=DB_USER,
        password=DB_PASSWORD,
        port=DB_PORT
    )
except psycopg2.Error as e:
    logging.error(f'Errore durante la connessione al database: {e}')
    raise

query='''SELECT 
            u.matricola_cf,
            u.nome,
            u.cognome,
            u.telegram_id,
            tlb.data_invio,
            tlb.lettura,
            tlb.data_conferma
        FROM 
            users.utenti_coc u
        RIGHT JOIN 
            users.t_lettura_bollettino tlb 
            ON u.telegram_id::text = tlb.id_telegram::text
        WHERE 
            tlb.data_invio = (
                SELECT 
                    MAX(tlb.data_invio) 
                FROM 
                    users.t_lettura_bollettino tlb
            ) 
            AND tlb.lettura IS NOT TRUE
        GROUP BY 
            u.matricola_cf, 
            u.nome, 
            u.cognome, 
            u.telegram_id, 
            tlb.lettura, 
            tlb.data_conferma, 
            tlb.data_invio
        ORDER BY 
            tlb.data_invio DESC;'''
curr = con.cursor()
con.autocommit = True

try:
    curr.execute(query)
except Exception as e:
    logging.error(f'Query non eseguita per il seguente motivo: {e}')

result= curr.fetchall() 
curr.close()   
con.close()

#print(result)

for p in result:
    # print(p[4])
    if datetime.now(pytz.timezone('Europe/Rome'))>=(p[4]+timedelta(minutes=5)): 
        #print(p[4]+timedelta(minutes=5))
        telegram_bot_sendtext(testo,p[3])
 
    else:
        continue
