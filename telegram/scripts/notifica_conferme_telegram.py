#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from logging_tools import make_logger
import requests
import os
from os import environ
from dotenv import load_dotenv
import psycopg2
import psycopg2.extras
import emoji
import urllib.parse

load_dotenv(os.path.join(os.path.dirname(os.path.dirname(os.path.realpath(__file__))), '.env'))

THIS_FILE_NAME = os.path.splitext(os.path.basename(__file__))[0]
THIS_FILE_PATH, THIS_FILE_FULL_NAME = os.path.split(__file__)
THIS_FILE_NAME, _ = os.path.splitext(THIS_FILE_FULL_NAME)
LOG_FILE = os.path.join(THIS_FILE_PATH, f'{THIS_FILE_NAME}.log')

LOGGERS = [
    "info:stdout",
    f"warning:{LOG_FILE}",
]  # syntax "severity:filename:format" filename can be stderr or stdout

logger = make_logger(f'script:{THIS_FILE_NAME}', LOGGERS)

if 'EMERGENZE_COC_BOT_TOKEN' not in environ:
    logger.critical('EMERGENZE_COC_BOT_TOKEN non trovato. Assicurati che il file ".env" contenga la variabile EMERGENZE_COC_BOT_TOKEN.')
    raise ValueError('EMERGENZE_COC_BOT_TOKEN non trovato')

if 'EMERGENZE_BOT_TOKEN' not in environ:
    logger.critical('EMERGENZE_BOT_TOKEN non trovato. Assicurati che il file ".env" contenga la variabile EMERGENZE_BOT_TOKEN.')
    raise ValueError('EMERGENZE_BOT_TOKEN non trovato')

TOKEN = os.getenv('EMERGENZE_COC_BOT_TOKEN')

def telegram_bot_sendtext(bot_message, chat_id, token):
    """ """
    urllib.parse.quote('/', safe='')
    send_text = 'https://api.telegram.org/bot' + token + '/sendMessage?chat_id=' + chat_id + '&parse_mode=Markdown&text=' + urllib.parse.quote(bot_message)
    response = requests.get(send_text)
    return response.json()


class DBIO():

    def __init__(self, **kwargs):
        self.load_env(**kwargs)
        self.check_params()    

    def __enter__(self):
        self.__connect()
        return self

    def __connect(self):
        try:
            self.connection = psycopg2.connect(
                host = self.DB_HOST,
                dbname = self.DB_NAME,
                user = self.DB_USER,
                password = self.DB_PASSWORD,
                port = self.DB_PORT
            )
        except psycopg2.Error as err:
            logger.error(f'Errore durante la connessione al database: {err}.')
            raise
        except Exception as err:
            logger.error(f'Unexpected error: {err}.')
            # TODO: Log del traceback.
            raise
        else:
            self.cursor = self.connection.cursor(cursor_factory=psycopg2.extras.RealDictCursor)

    def __exit__(self, type, value, traceback):
        self.cursor.close()
        self.connection.close()

    def check_params(self):
        if not all([self.DB_HOST, self.DB_NAME, self.DB_USER, self.DB_PASSWORD, self.DB_PORT]):
            logger.critical('''Parametri di connessione al database non trovati.
Assicurati che il file .env contenga conn_ip, conn_db, conn_user, e conn_pwd.''')
            raise ValueError('Parametri di connessione al database non trovati')

    def load_env(self, default_port=5432):
        self.DB_HOST = os.getenv('conn_ip')
        self.DB_NAME = os.getenv('conn_db')
        self.DB_USER = os.getenv('conn_user')
        self.DB_PASSWORD = os.getenv('conn_pwd')
        self.DB_PORT = os.getenv('conn_port', 5432)
        logger.debug('Env variables loaded')

    def fetch_all(self):
        query = '''SELECT * FROM users.v_notifiche_telegram;'''
        try:
            self.cursor.execute(query)
        except Exception as err:
            logger.error(f'Query non eseguita per il seguente motivo: {err}')
        else:
            return self.cursor.fetchall()


message = {
    'convocazione': f"""{emoji.emojize(':warning:',use_aliases=True)} {emoji.emojize(':bell:',use_aliases=True)} 
        Non è ancora stata inviata la conferma di avvenuta lettura della CONVOCAZIONE del COC. 
        Si prega di dare riscontro alla comunicazione precedentemente inviata premendo il tasto OK.""",
    'bollettino': f"""{emoji.emojize(":warning:",use_aliases=True)} {emoji.emojize(":bell:",use_aliases=True)} 
        Non è ancora stata inviata la conferma di avvenuta lettura dell'emanazione dello STATO di ALLERTA. 
        Si prega di dare riscontro alla comunicazione precedentemente inviata premendo il tasto OK."""
}

with DBIO() as dbio:
    results = dbio.fetch_all()
    for p in results:
        telegram_bot_sendtext(
            message[p['argomento']],
            p['telegram_id'],
            TOKEN
        )
