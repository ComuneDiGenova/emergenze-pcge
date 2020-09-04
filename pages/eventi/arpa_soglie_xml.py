#! usr/bin/env python
#Script per leggere i valori di soglia dagli XML per i grafici della rete OMIRL in json per highchart
# Gter copyleft 2020
# author: Roberto Marzocchi
###############################################################################
import os,sys

#from sys import argv
#from os.path import exists

import simplejson as json 

import urllib.request, json
import xml.etree.ElementTree as et

import datetime

import psycopg2
from conn import *

# assegno l'input dello script in maniera semplice
#script, input1, input2 = argv

def soglie(input1,input2):
    try:
        indirizzo = "https://omirl.regione.liguria.it/Omirl/rest/charts/{0}/{1}".format(input2,input1)
    except:
        print("Occorre specificare un input corretto es. python3 arpa_soglie_xml.py Idro MONTG")
        sys.exit(2)

    #leggo il file xml
    print(indirizzo)
    file = urllib.request.urlopen(indirizzo)
    data = file.read()
    file.close()

    #print(data)

    root = et.fromstring(data)

    print(len(root))
    print(root[1].attrib)
    #serie = root.attrib
    print(root[5][1][0].text)



    #cerco la soglia arancione (livello 7, root[6], tag value)
    for dataSeries in root[6]:
        #print(dataSeries.tag)
        if dataSeries.tag =='value':
            #print("OK sono entrato correttamente")
            arancio=dataSeries.text
            #print("Soglia arancione = ", arancio)

    #cerco la soglia arancione (livello 8, root[7], tag value)
    for dataSeries in root[7]:
        #print(dataSeries.tag)
        if dataSeries.tag =='value':
            #print("OK sono entrato correttamente")
            rossa=dataSeries.text
            #print("Soglia rossa = " rossa)
    return arancio, rossa


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
        s=soglie('Idro', row[0])
        arancio=s[0]
        rossa=s[1]
        print('Soglia arancio = {0}, Soglia rossa = {1}'.format(arancio,rossa))
        try:
            query="INSERT INTO geodb.soglie_idrometri_arpa(cod, liv_arancione, liv_rosso) VALUES ('{2}',{0},{1});".format(arancio,rossa,row[0])
            curr.execute(query)
        except: 
            query= "UPDATE geodb.soglie_idrometri_arpa SET liv_arancione={0}, liv_rosso={1} WHERE cod='{2}';".format(arancio,rossa,row[0])
            curr.execute(query)
        print('**************************************************************')
        
if __name__ == "__main__":
    main()




