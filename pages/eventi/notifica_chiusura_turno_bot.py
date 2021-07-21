#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Lorenzo Benvenuto
# Gter copyleft 2021

import logging
import requests
import os

import psycopg2
import emoji
import config
import time
import conn
from datetime import datetime, timedelta
import urllib.parse


# Configure logging
logfile='{}/notifica_chiusura_turno_bot.log'.format(os.path.dirname(os.path.realpath(__file__)))
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.ERROR)

API_TOKEN = config.TOKEN


def telegram_bot_sendtext(bot_message,chat_id):
    
    urllib.parse.quote('/', safe='')
    send_text = 'https://api.telegram.org/bot' + API_TOKEN + '/sendMessage?chat_id=' + chat_id + '&parse_mode=Markdown&text=' + urllib.parse.quote(bot_message)
    response = requests.get(send_text)
    return response.json()







testo='{} {} Il tuo turno sta per terminare. Ricordati di usare il comando /tuscita per chiudere correttamente il turo.'.format(emoji.emojize(":warning:",use_aliases=True),emoji.emojize(":alarm_clock:",use_aliases=True))
#telegram_bot_sendtext(testo,'306530623')
con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
query='select * from users.t_presenze where operativo =true'
curr = con.cursor()
con.autocommit = True
try:
    curr.execute(query)
except Exception as e:
    logging.error('Query non eseguita per il seguente motivo: {}'.format(e))

result= curr.fetchall() 
curr.close()   
con.close()

for p in result:

    if datetime.now()>(p[-1]-timedelta(minutes=15)): 
        telegram_bot_sendtext(testo,p[5])
 
    else:
        continue


#
