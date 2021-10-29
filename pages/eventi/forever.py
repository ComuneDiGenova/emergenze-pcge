#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Roberto Marzocchi (Gter srl) copyleft 2019

from subprocess import Popen
import os, sys
import datetime

x = datetime.datetime.now()
#print(x)

filename = sys.argv[1]
nome_file = filename.split('/')[-1]

#recupero il percorso al file
path=os.path.realpath(__file__).replace('forever.py','')
#print('il percorso è {}'.format(path))
logfile="{0}crash_{1}.log".format(path, nome_file[:-3])
pidfilename ="{0}{1}.pid".format(path, nome_file[:-3])

#print('il logfile è {}'.format(logfile))
f = open(logfile, "a")

#quit()

f.write("\n{} - Partito lo script forever.py".format(x))
f.close
#filename = sys.argv[1]


while True:
    # ricalcolo le ore
    x = datetime.datetime.now()
    f = open(logfile, "a")
    pidfile = open(pidfilename, 'w')
    f.write("\n{} - Ripartito lo script {}".format(x,filename))
    f.close
    #print("\nStarting " + filename)
    #per server test
    #p = Popen("/usr/local/bin/python3.8 " + filename, shell=True)
    #per server in esercizio
    p = Popen("/opt/rh/rh-python38/root/usr/bin/python3.8 " + filename, shell=True)
    pidfile.write(str(p.pid))
    pidfile.close()
    p.wait()

