#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Gter copyleft 
# Roberto Marzocchi
# Questo script verifica se ci sono degli eventi in chiusura,
# quante segnalazioni aperte ancora ci sono 
# e nel caso in cui non ce ne siano chiude l'evento definitivamente inviando un alert sul bot telefgram
#
# Lo script dovrà essere integrato nel crontab del sistema
#


import sys,os



# logging
import logging
import tempfile

tmpfolder=tempfile.gettempdir() # get the current temporary directory
logfile='{}/log_chiusura_automatica_eventi.log'.format(tmpfolder)
if os.path.exists(logfile):
    os.remove(logfile)


logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',
    filename=logfile,
    level=logging.DEBUG)


# import telegram BOT
import time
import datetime
import telepot
import emoji

import config
# Il token è contenuto nel file config.py e non è aggiornato su GitHub per evitare utilizzi impropri
TOKEN=config.TOKEN
chat_id_roberto=config.chat_id_roberto

bot = telepot.Bot(TOKEN)



# Connect to an existing database
import psycopg2
#sys.path.append(os.path.abspath("../../"))
from conn import *
conn = psycopg2.connect(host=ip, dbname=db, user=user, password=pwd, port=port)
#autocommit
conn.set_session(autocommit=True)
cur = conn.cursor()





#-- cerco gli eventi in chiusura
#SELECT id From eventi.t_eventi WHERE valido IS NULL ORDER BY id;
#-- cerco se ci sono segnalazioni
#SELECT count(id) FROM segnalazioni.v_segnalazioni where id_evento=87 and (in_lavorazione='t' OR in_lavorazione is null);

query="SELECT id From eventi.t_eventi WHERE valido IS NULL ORDER BY id;"
logging.debug(query)
cur.execute(query)
x=cur.fetchall()
for ev in x:
    logging.debug('Evento {}'.format(ev[0]))
    query1='''SELECT count(id) 
        FROM segnalazioni.v_segnalazioni 
        WHERE id_evento={} 
        AND (in_lavorazione='t' OR in_lavorazione is null)'''.format(ev[0])
    logging.debug(query1)
    cur1 = conn.cursor()
    cur1.execute(query1)
    y=cur1.fetchall()
    for cc in y:
        if cc[0] > 0 :
            logging.info('L\'evento in questione ha ancora segnalazioni aperte')
        elif cc[0] == 0:
            query_c='''UPDATE eventi.t_eventi 
            SET data_ora_fine_evento=now(), 
            valido='FALSE' where id={};'''.format(ev[0])
            logging.debug(query_c)
            cur2 = conn.cursor()
            cur2.execute(query_c)
            cur2.close()
            query_log= '''INSERT INTO varie.t_log (schema,operatore, operazione) 
            VALUES ('eventi','Script automatico',
            'Chiusura evento definitiva {}');'''.format(ev[0])
            logging.debug(query_log)
            cur2 = conn.cursor()
            cur2.execute(query_log)
            cur2.close()
            # chiusura squadre appese
            query0='''SELECT c.matricola_cf from users.t_componenti_squadre c
            JOIN users.t_squadre s ON s.id=c.id_squadra
            WHERE s.id_evento={} and c.data_end is null;'''.format(ev[0])
            logging.debug(query0)
            cur2 = conn.cursor()
            cur2.execute(query0)
            z=cur2.fetchall()
            for sq in z:
                query_s='''UPDATE users.t_componenti_squadre 
	            SET data_end=now() 
	            WHERE matricola_cf = '{}';'''.format(sq[0])
                logging.debug(query_s)
                cur3 = conn.cursor()
                cur3.execute(query_s)
                cur3.close()
            cur2.close()
            #telegram 
            query_d='''SELECT descrizione 
            FROM eventi.v_eventi WHERE id={};'''.format(ev[0])
            logging.debug(query_d)
            cur2 = conn.cursor()
            cur2.execute(query_d)
            z=cur2.fetchall()
            for dd in z:
                descr=dd[0]
                logging.debug(descr)
            cur2.close
            
            query_t='''SELECT telegram_id from users.utenti_sistema 
            where id_profilo <= 3 and telegram_id !='' 
            and telegram_attivo='t';'''
            logging.debug(query_t)
            cur2 = conn.cursor()
            cur2.execute(query_t)
            z=cur2.fetchall()
            for tt in z:
                chat_id=tt[0]
                logging.debug(chat_id)
                try:
                    bot.sendMessage(chat_id, '''{0} Non essendoci più segnalazioni aperte, l'evento di tipo {1} (id={2}) è stato chiuso in maniera definitiva 
(ricevi questo messaggio in quanto operatore di protezione civile) {0}'''.format(emoji.emojize(" :red_circle:", use_aliases=True), descr, ev[0]))
                except:
                    logging.error('Problema invio messaggio all\'utente con chat_id={}'.format(chat_id))
            cur2.close()
    #fine ciclo   
    cur1.close()
cur.close()