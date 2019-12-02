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

import datetime
import telepot



import config
# Il token è contenuto nel file config.py e non è aggiornato su GitHub per evitare utilizzi impropri
TOKEN=config.TOKEN

bot = telepot.Bot(TOKEN)
#per ora solo un test su Roberto
#chat_id= config.chat_id


sito_allerta="http://www.allertaliguria.gov.it"
abs_path_bollettini="/opt/rh/httpd24/root/var/www/html"


 


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
        print("Download completed...")
        #SEND BOT
        if tipo == 'PC':
            messaggio = "{}/docs/{}".format(sito_allerta,nome)
            # ciclo for sulle chat_id
            query_chat_id= "SELECT telegram_id from users.v_utenti_sistema where telegram_id !='' and telegram_attivo='t' and (id_profilo='1' or id_profilo ='2' or id_profilo ='3');"
            curr.execute(query_chat_id)
            lista_chat_id = curr.fetchall() 
            #print("Print each row and it's columns values")
            for row in lista_chat_id:
                chat_id=row[0]
                bot.sendMessage(chat_id, "Nuovo bollettino Protezione civile!")
                bot.sendMessage(chat_id, messaggio)
                time.sleep(1)
    else:
        print("File already download")
        #if tipo == 'PC':
        #    messaggio = "{}/docs/{}".format(sito_allerta,nome)
        #    bot.sendMessage(chat_id, "Bollettino Protezione civile già scaricato!")
        #    bot.sendMessage(chat_id, messaggio)
        



def main():
    url="{}/xml/allertaliguria.xml".format(sito_allerta);
    file = urllib.request.urlopen(url)
    data = file.read()
    file.close()

    nomefile2="{}/bollettini/allerte.txt".format(abs_path_bollettini)
    log_file_allerte = open(nomefile2,"w")
    
    #questo da file
    #tree = et.parse("allertaliguria.xml")
    #root = tree.getroot()


    #questo direttamente dalla stringa letta su web
    root = et.fromstring(data)
    #print(root)

    #data ora emissione
    update = root.attrib['dataEmissione']
    print(update)
    update2=datetime.datetime.strptime(update,"%Y%m%d%H%M")
    print(update2)
    log_file_allerte.write("Ultimo aggiornamento: {}".format(update2))
        
    # messaggio PROTEZIONE CIVILE
    for elem in root.findall('MessaggioProtezioneCivile'):
        bollettino = elem.attrib['nomeFilePDF']
        #emissione = elem.attrib['dataEmissione']
        if bollettino!='':
            scarica_bollettino("PC",bollettino,'NULL')
        #datatake = elem.find('Testo')
        #print(datatake.text)

    # meteo ARPA
    for elem in root.findall('MessaggioMeteoARPAL'):
        bollettino=elem.attrib['nomeFilePDF']
        emissione = elem.attrib['dataEmissione']
        #print(emissione)
        if bollettino!='':
            scarica_bollettino("Met_A",bollettino,emissione)
        #datatake = elem.find('Testo')
        #print(datatake.text)

    # Idrologico ARPA
    for elem in root.findall('MessaggioIdrologicoARPAL'):
        bollettino=elem.attrib['nomeFilePDF']
        emissione = elem.attrib['dataEmissione']
        if bollettino!='':
            scarica_bollettino("Idr_A",bollettino,emissione)
        #datatake = elem.find('Testo')
        #print(datatake.text)

    # Idrologico ARPA
    for elem in root.findall('MessaggioNivologicoARPAL'):
        bollettino=elem.attrib['nomeFilePDF']
        emissione = elem.attrib['dataEmissione']
        if bollettino!='':
            scarica_bollettino("Niv_A",bollettino,emissione)
        #datatake = elem.find('Testo')
        #print(datatake.text)
    
    # Leggi allerte
    for elem in root.findall('Zone'):
        for zone in elem.findall('Zona'):
            zona = zone.attrib["id"]
            if zona == 'B':
                #print(zona)
                for allerte in zone.findall('AllertaIdrogeologica'):
                    log_file_allerte.write('<br><b>Allerta Idrogeologica Zona B</b>')
                    log_file_allerte.write("\n<br>PioggeDiffuse={}".format(allerte.attrib['pioggeDiffuse']))
                    log_file_allerte.write("\n<br>Temporali={}".format(allerte.attrib['temporali']))
                    log_file_allerte.write("\n<br>Tendenza={}".format(allerte.attrib['tendenza']))
                    #bollettino=elem.attrib['nomeFilePDF']
                for allerte in zone.findall('AllertaNivologica'):
                    log_file_allerte.write('<br><b>Allerta Nivologica Zona B</b>')
                    log_file_allerte.write("\n<br>Neve={}".format(allerte.attrib['neve']))
                    log_file_allerte.write("\n<br>tendenza={}".format(allerte.attrib['tendenza']))
                    
            
    log_file_allerte.close
        
        
        
if __name__ == "__main__":
    main()
