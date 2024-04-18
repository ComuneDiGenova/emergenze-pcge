#! /home/local/COMGE/egter01/miniconda3/envs/dbupdate/bin/python
# -*- coding: utf-8 -*-
#   Gter Copyleft 2018
#   Roberto Marzocchi,
#   Edit 17.11.2022 - Corretto un bug che bloccava l'update a causa di API concorrenti
#   Simone Parmeggiani

#   percorso a crontab: nano /etc/crontab

import os, sys
from datetime import datetime
# import ogr
import psycopg2
import cx_Oracle
import subprocess
import config
#import traceback

# controlla se update viene eseguito correttamente o meno 
# e compila una lista con viste non correttamente aggiornate
check_web = 1  
oracle_view_error = [] 

# definisco le variabili di connessione
dbname = config.dbname
port = config.port
user = config.user
password = config.password
host = config.host

host_vm = config.host_vm
port_vm = config.port_vm

def db_updater():
    # Apro connessione PostGIS
    conn = psycopg2.connect(dbname=dbname, port=port, user=user, password=password, host=host)
    curr = conn.cursor()
    conn.autocommit = True

    # Apro connessione ORACLE per check esistenza mappe (bug gdal)
    con = cx_Oracle.connect('PC_EMERGENZE/$Allerta45$@vm-oraprod-linux2/georef.comune.genova.it')
    cur = con.cursor()
  
    # Query Postgres per collezionare tutte le tabelle da aggiornare
    sql = """SELECT * FROM geodb."m_tables" WHERE nome_oracle IS NOT NULL AND valido='t';"""
    curr.execute(sql)
    query_result = curr.fetchall()
    print(sql)

    # Trasferisco tutte le tabelle su DB PostgreSQL con relativi controlli,
    # passando attraverso tabelle temporanee per non dover interrompere i servizi concorrenti
    for result in query_result:
        id = result[0]
        schema_pg = result[5]
        nome_pg = result[2]
        geom_name = result[3]
        geom_type = result[4]
        oracle_view = result[1]
        now = datetime.now()
        print("\n*****************************************\n")    
        print("Inizio trasferimento mappa {} a {}.{} alle ore {}".format(
            oracle_view, schema_pg, nome_pg, now.strftime("%H:%M:%S")))
        
        # a causa di un baco di gdal (segnalato su github in data 19-09-2018)
        # devo verificare a mano l'esistenza o meno della vista oracle
        check_oracle = 1  # Suppongo che la vista esista in oracle
        query_oracle = "SELECT * FROM {}".format(oracle_view)
        try:
            cur.execute(query_oracle)
        except:
            check_oracle = 0
            print("ERROR: la vista {} non esiste su DB Oracle o l'utente in questione non ha i privilegi per visualizzarla".format(
                oracle_view))
            check_web = 0
            oracle_view_error.append(oracle_view)
        if check_oracle == 1:

            stringa2 = "ls"
            mycmd = "/usr/local/bin/ogr2ogr"
            print(geom_name, file=sys.stdout)
            if geom_name == 'no':
                myarg = """ -append -a_srs "EPSG:3003" -f "PostgreSQL" -nln "{0}"."{1}" -lco LAUNDER=YES -lco FID=gid -lco OVERWRITE=NO --config OGR_TRUNCATE YES --config PG_USE_COPY YES PG:"host='{2}' user='{3}' password='{4}' dbname='{5}'" OCI:'PC_EMERGENZE/$Allerta45$@(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = {6})(PORT = {7})))(CONNECT_DATA = (SID = georef))):{8}'""".format(
                    schema_pg, nome_pg, host, user, password, dbname, host_vm, port_vm, oracle_view
                )
            else:
                myarg = """ -append -a_srs "EPSG:3003" -f "PostgreSQL" -nln "{0}"."{1}" -lco LAUNDER=YES -lco FID=gid -lco OVERWRITE=NO --config OGR_TRUNCATE YES --config PG_USE_COPY YES -nlt "{2}" -lco GEOMETRY_NAME={3} PG:"host='{4}' user='{5}' password='{6}' dbname='{7}'" OCI:'PC_EMERGENZE/$Allerta45$@(DESCRIPTION = (ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = {8})(PORT = {9})))(CONNECT_DATA = (SID = georef))):{10}'""".format(
                    schema_pg, nome_pg, geom_type, geom_name, host, user, password, dbname, host_vm, port_vm, oracle_view
                )
            print(myarg)
            try:
                ##### BUG di Gdal --> in caso di assenza dell'input layer su Oracle non mi restituisce nessun error code--> bisogna in qualche modo fare un check a parte...
                retcode = subprocess.call(mycmd + myarg, shell=True)
                if retcode == 0:
                    print("Child returned", retcode, file=sys.stdout)
                    now = datetime.now()
                    print("Update concluso correttamente il {}".format(
                        now.strftime('%d/%m/%Y alle %H:%M:%S')
                    ))      
                else:
                    print("ERROR: Child returned", retcode, file=sys.stdout)
                    print("Execution failed", file=sys.stdout)
                    print(mycmd + myarg)                
                    check_web = 0
                    oracle_view_error.append(oracle_view)
            except OSError as e:
                print("ERROR: Execution failed:", e, file=sys.stdout)
                check_web = 0
                oracle_view_error.append(oracle_view)

    # Completo aggiornando tabella dipendenti, senza l'uso di tabelle temporanee
    query_postgis_dipendenti = """INSERT INTO varie.dipendenti_storici 
                                    SELECT * FROM varie.dipendenti 
                                    WHERE matricola NOT IN (
                                                            SELECT matricola 
                                                            FROM varie.dipendenti_storici
                                                            );
                                """
    curr.execute(query_postgis_dipendenti)

    # Make the changes to the database persistent
    conn.commit()

    # Chiudo connessioni PostGIS
    curr.close()
    conn.close()

    # Chiudo connessione ORACLE
    con.close()
    
    return

if __name__ == "__main__":
    # Disattivo temporaneamente i container Docker che bloccano le tabelle
    print("SWITCHING OFF DOCKER CONTAINERS")
    os.chdir('/home/local/COMGE/egter01/emergenze_verbatel/Emergenze-Verbatel')
    
    # PROD: tiro giù i container
    docker_down = os.popen('docker compose -f docker-compose-dev.yml down -v')
    
    # TEST: tiro giù i container
    # docker_down = os.popen('sudo docker-compose down -v')
    
    docker_down.read()

    # Cerco il percorso al file python
    path = os.path.dirname(os.path.abspath(__file__))

    # Da questo punto in poi, redirigo i print su un log message
    old_stdout = sys.stdout
    nomefile1 = "{}/message.log".format(path)
    log_file = open(nomefile1, "w")
    sys.stdout = log_file

    # Log file per pagina web
    nomefile2 = "{}/web.log".format(path)
    log_file_web = open(nomefile2, "w")

    print("#### Avvio Aggiornamento Tabelle PostGIS(DA ORACLE) ####")

    try:
        db_updater()
    except Exception as err:
        print(err)


    # Chiusura file di log
    sys.stdout = old_stdout
    log_file.close()

    # Riattivo i servizi Docker
    print("RESTARTING DOCKER CONTAINERS")
    os.chdir('/home/local/COMGE/egter01/emergenze_verbatel/Emergenze-Verbatel')
    
    # PROD: tiro su i container
    docker_up = os.popen("docker compose -f docker-compose-dev.yml up -d")
    
    # TEST: tiro su i container
    # docker_up = os.popen("sudo docker-compose up -d")
    
    docker_up.read()

    # Scrivo il file di log per il web
    now = datetime.now()
    if check_web == 1:
        log_file_web.write(
            "Ultimo aggiornamento delle tabelle di sistema terminato correttamente il {}".format(
                now.strftime('%d/%m/%Y alle %H:%M:%S')
            ))
    else:
        log_file_web.write(
            "Ultimo aggiornamento delle tabelle di sistema terminato il {}".format(
                now.strftime('%d/%m/%Y alle %H:%M:%S')
            ))
        log_file_web.write('\n<br> <i class="fas fa-exclamation-triangle"></i>')
        i = 0
        while i < len(oracle_view_error):
            log_file_web.write('\n<br>Problema con la view oracle {}'.format(oracle_view_error[i]))
            i += 1
        log_file_web.write('\n<br> <i class="fas fa-exclamation-triangle"></i>')
        log_file_web.write('\n<br>Contatta l\'amministratore di sistema, ')
        log_file_web.write(
            '<a href="mailto:applicazionisit@comune.genova.it?cc=assistenzagis@gter.it&subject=ERRORE trasferimento dati Oracle-PostGIS">scrivi una mail</a>')
