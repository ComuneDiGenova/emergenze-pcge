#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Gter copyleft 2021

import logging
import requests
import os
from dotenv import load_dotenv
import psycopg2
import emoji
import config
import time
import conn
from datetime import datetime, timedelta
import urllib.parse
import pytz


load_dotenv(os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.env'))

# Configure logging
logfile='{}/notifica_conferma_lettura_conv_bot.log'.format(os.path.dirname(os.path.realpath(__file__)))
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.ERROR)

TOKENCOC=os.getenv('EMERGENZE_COC_BOT_TOKEN')

if not TOKENCOC:
    logging.error('EMERGENZE_COC_BOT_TOKEN non trovato. Assicurati che il file .env contenga la variabile TOKEN_COC.')
    raise ValueError('EMERGENZE_COC_BOT_TOKEN non trovato')


def telegram_bot_sendtext(bot_message,chat_id):
    
    urllib.parse.quote('/', safe='')
    send_text = 'https://api.telegram.org/bot' + TOKENCOC + '/sendMessage?chat_id=' + chat_id + '&parse_mode=Markdown&text=' + urllib.parse.quote(bot_message)
    response = requests.get(send_text)
    return response.json()


testo=f"""{emoji.emojize(':warning:',use_aliases=True)} {emoji.emojize(':bell:',use_aliases=True)} 
        Non è ancora stata inviata la conferma di avvenuta lettura della CONVOCAZIONE del COC. 
        Si prega di dare riscontro alla comunicazione precedentemente inviata premendo il tasto OK."""

con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
query="""SELECT 
            u.matricola_cf,
            u.nome,
            u.cognome,
            u.telegram_id,
            tlcc.data_invio_conv,
            tlcc.lettura_conv,
            tlcc.data_conferma_conv
        FROM 
            users.utenti_coc u
        RIGHT JOIN 
            users.t_lettura_conv_coc tlcc 
            ON u.telegram_id::text = tlcc.id_telegram::text
        WHERE 
            tlcc.data_invio_conv = (
                SELECT 
                    MAX(tlcc.data_invio_conv) 
                FROM 
                    users.t_lettura_conv_coc tlcc
            ) 
            AND tlcc.lettura_conv IS NOT TRUE
        GROUP BY 
            u.matricola_cf, 
            u.nome, 
            u.cognome, 
            u.telegram_id, 
            tlcc.data_invio_conv, 
            tlcc.lettura_conv, 
            tlcc.data_conferma_conv
        ORDER BY 
            tlcc.data_invio_conv DESC;"""
            
curr = con.cursor()
con.autocommit = True
try:
    curr.execute(query)
except Exception as e:
    logging.error(f'Query non eseguita per il seguente motivo: {e}')

result= curr.fetchall() 
curr.close()   
con.close()

# print(result)

for p in result:
    # print(datetime.now()<=(p[7]))
    # print(p)
    if datetime.now(pytz.timezone('Europe/Rome'))>=(p[4]+timedelta(minutes=5)):
        telegram_bot_sendtext(testo,p[3])
 
    else:
        print("messaggio non inviato")
