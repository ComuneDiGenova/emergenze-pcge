#! /usr/bin/env python
# -*- coding: utf-8 -*-
#   Gter Copyleft 2018
#   Roberto Marzocchi

import os
#import urllib2 #problema con python3
import urllib.request
import xml.etree.ElementTree as et

import psycopg2
from conn import *

import time
import datetime
import telepot

import logging
import tempfile

tmpdir=tempfile.gettempdir()


logging.basicConfig(
    format='%(asctime)s\t%(levelname)s\t%(message)s',
    filename='{}/omirl_run.log'.format(tmpdir),
    filemode='a',  
    level=logging.INFO)


import config
# Il token è contenuto nel file config.py e non è aggiornato su GitHub per evitare utilizzi impropri
TOKEN=config.TOKEN

bot = telepot.Bot(TOKEN)
#per ora solo un test su Roberto
#chat_id= config.chat_id

path='/home/local/COMGE/egter01/emergenze-pcge'



def scarica_bollettino(tipo,nome,ora):
    if os.path.isfile("{}/bollettini/{}/{}".format(abs_path_bollettini,tipo,nome))==False:
        if ora!='NULL':
            data_read=datetime.datetime.strptime(ora,"%Y%m%d%H%M")
            print(data_read)
        f = urllib.request.urlopen("{}/docs/{}".format(sito_allerta,nome))
        data = f.read()
        with open("{}/bollettini/{}/{}".format(abs_path_bollettini,tipo,nome), "wb") as code:
            code.write(data)
        conn = psycopg2.connect(host=ip, dbname=db, user=user, password=pwd, port=port)
        curr = conn.cursor()
        conn.autocommit = True
        if ora!='NULL':
            query = "INSERT INTO eventi.t_bollettini(tipo, nomefile, data_ora_emissione)VALUES ('{}', '{}', '{}')".format(tipo,nome,data_read);
        else:
            query = "INSERT INTO eventi.t_bollettini(tipo, nomefile)VALUES ('{}', '{}')".format(tipo,nome);
        #print(query)
        curr.execute(query)
        print("Download of type {} completed...".format(tipo))
        print(datetime.datetime.now())
        #SEND BOT
        if tipo == 'PC':
            print("Bollettino di PC")
            messaggio = "{}/docs/{}".format(sito_allerta,nome)
            # ciclo for sulle chat_id
            query_chat_id= "SELECT telegram_id from users.v_utenti_sistema where telegram_id !='' and telegram_attivo='t' and (id_profilo='1' or id_profilo ='2' or id_profilo ='3');"
            curr.execute(query_chat_id)
            print(datetime.datetime.now())
            print(query_chat_id)
            lista_chat_id = curr.fetchall() 
            #print("Print each row and it's columns values")
            for row in lista_chat_id:
                chat_id=row[0]
                print(chat_id)
                try:
                    bot.sendMessage(chat_id, "Nuovo bollettino Protezione civile!\n\n{}".format(messaggio))
                except:
                    print('Problema invio messaggio all\'utente con chat_id={}'.format(chat_id))
                #bot.sendMessage(chat_id, messaggio)
                #time.sleep(1)
    else:
        print("File of type {} already download".format(tipo))
        #if tipo == 'PC':
        #    messaggio = "{}/docs/{}".format(sito_allerta,nome)
        #    bot.sendMessage(chat_id, "Bollettino Protezione civile già scaricato!")
        #    bot.sendMessage(chat_id, messaggio)
        



def main():
    conn = psycopg2.connect(host=ip, dbname=db, user=user, password=pwd, port=port)
    curr = conn.cursor()
    conn.autocommit = True
    query = "SELECT shortcode from geodb.tipo_idrometri_arpa ; --where valido='t';"
    curr.execute(query)
    lista_idrometri = curr.fetchall()
    # print("Print each row and it's columns values")
    for row in lista_idrometri:
        print("Leggo idrometro ",row[0])
        try:
            os.system('/usr/bin/python3 {}/vendor/omirl_data_ingestion/xml2json.py Idro {}'.format(path, row[0]))
            logging.info('Download dati per Idrometro {} avvenuto correttamente'.format(row[0]))
        except Exception as e:
            logging.error('TIMEOUT? PROBLEM', e)
        
        
if __name__ == "__main__":
    main()
