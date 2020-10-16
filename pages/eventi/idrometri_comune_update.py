#! /usr/bin/env python
# -*- coding: utf-8 -*-

# Gter copyleft 2020
# Author: Roberto Marzocchi
# Script che interroga il DB MS-SQL con i dati degli idrometri comunali:


import os, sys, re  # ,shutil,glob

import getopt  # per gestire gli input

#import datetime
from datetime import date, timedelta, datetime

import pymssql
import conn_mssql as c

import psycopg2
import conn as p


def standardize_date(date_str):
    data=str(date_str)
    #print(data[8:10])
    h=int(float(data[8:10])%24)
    if h < 10:
        h='0{}'.format(h) 
    t1='{}{}{}'.format(data[0:8],h,data[10:])
    return t1






def main():
    con = psycopg2.connect(host=p.ip, dbname=p.db, user=p.user, password=p.pwd, port=p.port)
    cur = con.cursor()
    con.autocommit = True

    conn = pymssql.connect(server=c.server, user=c.user, password=c.password, database=c.database)
    cursor = conn.cursor()
    query1 = """
    SELECT s.IDStation,s.station, max(t.first_rec), max(t.last_rec), t.input_name 
    FROM stations s 
    JOIN TAGS t ON s.IDstation = t.IDstation where t.IDMea=9 
    GROUP BY s.IDStation,s.station, t.input_name;"""
    print('#####################################################################################')
    print(query1)
    print('#####################################################################################')
    cursor.execute(query1)
    #row = cursor.fetchone()
    #while row:
    #    print("ID={}, Name={}".format(row[0], row[1]))
    #    row = cursor.fetchone
    id_aste = cursor.fetchall()
    print(len(id_aste))
    # print("Print each row and it's columns values")
    i=0
    for row in id_aste:
        if i==0:
            print(len(row))
        i+=1
        # 5 
        print('{}, {}, {}, {}, {}'.format(row[0],row[1],row[2],row[3], row[4]))
        query_pg="SELECT id FROM geodb.tipo_idrometri_comune where id='{}';".format(row[0])
        cur.execute(query_pg)
        check = cur.fetchall()
        #print(row[2])
        start=standardize_date(row[2])
        stop=standardize_date(row[3])
        #print(stop)
        stop_date=datetime.strptime(stop, '%Y%m%d%H%M%S')
        #print(stop_date)
        yesterday = datetime.today() - timedelta(days=1)
        if stop_date < yesterday:
            us='f'
        else:
            us='t'
        print(us)
        if len(check) ==0:
            query2="""INSERT INTO geodb.tipo_idrometri_comune(id, nome, first_rec, last_rec, usato) 
            VALUES ('{0}','{1}',TO_TIMESTAMP('{2}', 'YYYYMMDDHH24MISS'),TO_TIMESTAMP('{3}', 'YYYYMMDDHH24MISS'), '{4}');
            """.format(row[0],row[1],start, stop, us)
        else:
            query2="""UPDATE geodb.tipo_idrometri_comune SET id='{0}', nome='{1}', 
            first_rec=TO_TIMESTAMP('{2}', 'YYYYMMDDHH24MISS'), 
            last_rec=TO_TIMESTAMP('{3}', 'YYYYMMDDHH24MISS'), usato='{4}' 
            WHERE id='{0}';""".format(row[0],row[1],start, stop, us)
        print(query2)
        cur.execute(query2);
        
    exit()
    # print("id_asta={}".format(id_asta))
    return 200
    # ORA SI RICHIAMA IL WS


if __name__ == "__main__":
    main()
