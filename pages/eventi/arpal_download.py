#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Gter copyleft 
# Roberto Marzocchi
# Questo script  scarica i dati da OMIRL e li carica sul DB PostgreSQL
# lo fa per i soli idrometri contenuti nella tabelle del DB geodb.tipo_idrometri_arpa


import sys,os

import shutil
import urllib.request as request
from contextlib import closing


# Connect to an existing database
import psycopg2
#sys.path.append(os.path.abspath("../../"))
from conn import *
conn = psycopg2.connect(host=ip, dbname=db, user=user, password=pwd, port=port)
#autocommit
conn.set_session(autocommit=True)
cur = conn.cursor()
cur2 = conn.cursor()


import json
import datetime


data=['Idro']
#dirname=os.path.dirname(__file__)
#dirname=os.path.dirname(os.path.realpath('__file__'))
dirname=sys.path[0]
#print(dirname)
dirname2=os.path.join(dirname, '../../vendor/omirl_data_ingestion')
dirname2 = os.path.abspath(os.path.realpath(dirname2))
#print(dirname2)
#exit()
#print(sys.path[0])
#print(os.path.abspath(json_path))

list_json = {}

for d in data:
    jsonFile=os.path.join(dirname2, '{}.json'.format(d))
    print('Read the file:', jsonFile)
    if os.path.exists(jsonFile): #check if the file esists
        #reads the .json file
        json_file = open(jsonFile, 'r')
        json_file_reader = json_file.read()
        sjson = json.loads(json_file_reader)
        json_feat = sjson['features']
        for i in json_feat:
            list_json[i['properties']['shortCode']] = [i['properties']['refDate'], str(i['properties']['value'])]
            #print(i['properties']['shortCode'], i['properties']['refDate'], str(i['properties']['value']))
            stazname=i['properties']['shortCode']
            check=0
            query1="SELECT shortcode FROM geodb.tipo_idrometri_arpa WHERE shortcode ilike '{}';".format(stazname)
            print(query1)
            cur2.execute(query1)
            x=cur2.fetchall()
            #print(x)
            for el in x:
                check=1
                id_station=el[0]
                print(id_station)
            #    ele=el[1]
            #print(id_station,ele)
            #leggo la data
            #questa parte c'è nel file omirl_run.py
            #if check==1:
            #    python_exe='/usr/bin/python3 /opt/rh/httpd24/root/var/www/html/emergenze/vendor/omirl_data_ingestion/xml2json.py Idro {0}'.format(stazname)
            #    ret=os.system(python_exe)
            #    if ret!=0:
            #        print('ERRORE scarico file json per i grafici')
            data_arpa=i['properties']['refDate']
            #leggo il valore
            valore=i['properties']['value']
            if check==1:
                # converto la data
                date_time_obj = datetime.datetime.strptime(data_arpa, '%Y-%m-%dT%H:%M:%S')
                data_utc=date_time_obj.strftime("%Y-%m-%d %H:%M:%S")
                #print("Data UTC:", data_utc)
                #controllo se c'è già un dato
                #print("OK devo inserire il dato sul DB")
                query2="SELECT id_station FROM geodb.lettura_idrometri_arpa WHERE id_station ilike '{}' and data_ora ='{}'".format(stazname,data_arpa)
                #print(query2)
                cur2.execute(query2)
                y=cur2.fetchall()
                #print(x)
                check_u=0
                for u in y:
                    check_u=1
                if d=='Idro':
                    if check_u==1:
                        query3= "UPDATE geodb.lettura_idrometri_arpa SET lettura={} WHERE id_station='{}' AND \"data_ora\"='{}';".format(valore,stazname,data_utc)
                    else:
                        query3= "INSERT INTO geodb.lettura_idrometri_arpa (id_station, data_ora, lettura) VALUES ('{}', '{}', {});".format(stazname,data_utc,valore)
                # elif d=='Press':
                #     valore_mare=valore-(1013.25* (-1 +(1 + 0.0000226*ele)**(-5.25593)))
                # elif d=='Termo':
                try:
                    cur2.execute(query3)
                    print('Aggiunto / aggiornato il dato di {} della stazione {} delle ore {}'.format(d,stazname,data_utc))
                except Exception as e:
                    print(e)
                    print(query3)
            #insert into DB if station exists
            #print(stazname)        
    else:
        print('{} not exists'.format(jsonFile))
exit()
    

    
    
