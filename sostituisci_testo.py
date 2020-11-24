#!/usr/bin/env python

#Gter copyleft

import os, fnmatch

import logging
import tempfile


#il file di log lo salviamo nella cartella temporanea di sistema
tmpfolder=tempfile.gettempdir() # get the current temporary directory
logfile='{}/sostituzione_testo.log'.format(tmpfolder)

 
logging.basicConfig(
    format='%(asctime)s\t%(levelname)s\t%(message)s',
    filename=logfile,
    filemode='w',
    level=logging.INFO)


def findReplace(directory, find, replace, filePattern):
    for path, dirs, files in os.walk(os.path.abspath(directory)):
        for filename in fnmatch.filter(files, filePattern):
            filepath = os.path.join(path, filename)
            logging.info('{} file changed'.format(filepath))
            with open(filepath) as f:
                s = f.read()
            old_s = s
            s = s.replace(find, replace)
            if s != old_s:
                logging.info('{} replaced with {}'.format(find, replace))
            with open(filepath, "w") as f:
                f.write(s)

a=os.walk('pages')
i=0
for x in a:
    if i>0:
        print(x[0])
        findReplace(x[0], "require('../validate_input.php');", "require('../validate_input.php');", "*.php")
    i+=1
    

#findReplace("pages", "require('../validate_input.php')", "//require('../validate_input.php');", "*.php")