#!/usr/bin/env python3
# -*- coding: utf-8 -*-

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
logfile='{}/notifica_conferma_lettura_conv_bot.log'.format(os.path.dirname(os.path.realpath(__file__)))
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.ERROR)

TOKENCOC=config.TOKEN_COC


def telegram_bot_sendtext(bot_message,chat_id):
    
    urllib.parse.quote('/', safe='')
    send_text = 'https://api.telegram.org/bot' + TOKENCOC + '/sendMessage?chat_id=' + chat_id + '&parse_mode=Markdown&text=' + urllib.parse.quote(bot_message)
    response = requests.get(send_text)
    return response.json()







testo='{} {} Non è ancora stata inviata la conferma di avvenuta lettura della CONVOCAZIONE del COC. Si prega di dare riscontro alla comunicazione precedentemente inviata premendo il tasto OK.'.format(emoji.emojize(":warning:",use_aliases=True),emoji.emojize(":bell:",use_aliases=True))
#telegram_bot_sendtext(testo,'306530623')
con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
query='''SELECT u.matricola_cf,
u.nome,
u.cognome,
u.telegram_id,
tp.data_invio,
tp.lettura,
tp.data_conferma,
tp.data_invio_conv,
tp.lettura_conv,
tp.data_conferma_conv
FROM users.utenti_coc u
right JOIN users.t_convocazione tp ON u.telegram_id::text = tp.id_telegram::text
WHERE tp.data_invio_conv = (select max(tp.data_invio_conv) FROM users.t_convocazione tp) and tp.lettura_conv is not true
GROUP BY u.matricola_cf, u.nome, u.cognome, u.telegram_id, tp.lettura, tp.data_conferma, tp.data_invio, tp.data_invio_conv, tp.lettura_conv, tp.data_conferma_conv
order by tp.data_invio_conv desc;'''
curr = con.cursor()
con.autocommit = True
try:
    curr.execute(query)
except Exception as e:
    logging.error('Query non eseguita per il seguente motivo: {}'.format(e))

result= curr.fetchall() 
curr.close()   
con.close()

#print(result)

for p in result:
    print(p[7])
    if datetime.now()>=(p[7]+timedelta(minutes=5)): 
        #print(p[4]+timedelta(minutes=5))
        telegram_bot_sendtext(testo,p[3])
 
    else:
        continue


#
