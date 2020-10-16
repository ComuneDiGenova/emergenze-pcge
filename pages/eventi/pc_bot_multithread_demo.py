#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Roberto Marzocchi copyleft 2019


import os.path
from os import path
import asyncio

import sys, time
# da togliere
import random


import telepot

#python 3
from telepot.aio.loop import MessageLoop
#from telepot.aio.namedtuple import InlineKeyboardMarkup, InlineKeyboardButton
from telepot.aio.delegate import pave_event_space, per_chat_id, create_open, per_callback_query_origin
#python2 
#from telepot.loop import MessageLoop
#questo per i tastini
from telepot.namedtuple import InlineKeyboardMarkup, InlineKeyboardButton
#from telepot.delegate import pave_event_space, per_chat_id, create_open


from pprint import pprint
import time
import datetime
import json

import logging
import tempfile

tmpfolder=tempfile.gettempdir() # get the current temporary directory
logfile='{}/bot_telegram.log'.format(tmpfolder)
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',
    filename=logfile,
    level=logging.INFO)




try:
    # Fall back to Python 2's urllib2
    from urllib2 import urlopen
except:
    # For Python 3.0 and later
    from urllib.request import urlopen
import config

import psycopg2
import conn as p

# Il token è contenuto nel file config.py e non è aggiornato su GitHub per evitare utilizzi impropri
TOKEN=config.TOKEN
link=config.link

check=0
testo_segnalazione=''
testo_segnalazione20=''
alllegato=''
chat_id=''
id_mira=''

# questa classe usa il ChatHandler telepot.aio.helper.ChatHandler (ossia è in ascolto della chat del BOT)
class MessageCounter(telepot.aio.helper.ChatHandler):
    def __init__(self, *args, **kwargs):
        super(MessageCounter, self).__init__(*args, **kwargs)
        self._count = 0


    async def on_chat_message(self, msg):
        #contatore messaggi
        self._count += 1
        global check
        global testo_segnalazione
        global testo_segnalazione20
        global allegato
        global chat_id
        global id_mira
        self._check1 = check
        logging.info(self._check1)
        content_type, chat_type, chat_id = telepot.glance(msg)
        #content_type, chat_type, chat_id = telepot.glance(msg) #get dei parametri della conversazione e del tipo di messaggio
        try:
            command = msg['text'] #get del comando inviato
        except:
            logging.info("Non è arrivato nessun messaggio")
        try:
            if content_type == 'photo':
                await self.bot.download_file(msg['photo'][-1]['file_id'], 'C:\\Users\\assis\\Downloads\\file_bot.png')
                allegato = 'C:\\Users\\assis\\Downloads\\file_bot.png'
                logging.info("Immagine recuperata")
                command="foto"
        except:
            logging.info("Non è arrivato nessuna immagine")

        try:
            nome = msg["from"]["first_name"]
        except:
            nome= ""
        try:
            cognome = msg["from"]["last_name"]
        except:
            cognome= ""
        is_bot = msg["from"]["is_bot"]
        if is_bot=='True':
            await self.sender.sendMessage("ERROR: questo Bot non risponde ad altri bot!")
        elif command == '/telegram_id':
            message = "Gentile {1} {2} il tuo codice (telegram id) da inserire nell'applicazione è {3}".format(self._count,nome, cognome, chat_id)
            await self.sender.sendMessage(message)
        elif command == '/sito':
            message = "Gentile {1} {2} il sito del Sistema di Gestione Emergenze è {3} ".format(self._count,nome, cognome, link)
            await self.sender.sendMessage(message)
        elif command == '/mire_rivi':
            query_v='select count(telegram_id) from users.utenti_input_telegram where telegram_id like %s;'
            cod=(str(chat_id),)
            con = psycopg2.connect(host=p.ip, dbname=p.db, user=p.user, password=p.pwd, port=p.port)
            cur = con.cursor()
            con.autocommit = True
            cur.execute(query_v,cod)
            x = cur.fetchone()[0]
            if x == 1: 
                query_mire="""SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome
                FROM geodb.punti_monitoraggio_ok p
	            WHERE p.tipo ilike 'mira' OR p.tipo ilike 'rivo' 
	            order by nome;"""
                cur.execute(query_mire)
                mire = cur.fetchall()
                message= 'Elenco delle {} mire e rivi '.format(len(mire))
                inline_array = []
                for league in mire:
                    inline_array.append(InlineKeyboardButton(text=league[1], callback_data=mire(str(league[0]))))

                keyboard_elements = [[element] for element in inline_array]

                keyboard = InlineKeyboardMarkup(inline_keyboard=keyboard_elements )
                # vidlist = ""
                # for row in mire:
                #         vidlist = vidlist+"[InlineKeyboardButton(text='"+str(row[1])+"', callback_data='"+str(row[0])+"')],"
                # vidlist = vidlist+"]"
                # print(vidlist)
                #keyboard = InlineKeyboardMarkup(inline_keyboard=[vidlist])
                await self.sender.sendMessage(message, reply_markup=keyboard)
                con.close
            #elif x > 1: 
            #    
            else:
                keyboard = InlineKeyboardMarkup(inline_keyboard=[
                             [InlineKeyboardButton(text='Inserisci il CF', callback_data='codfisc')],
                             [InlineKeyboardButton(text='Recupera il tuo Telegram ID', callback_data='chat_id')],
                         ])
                message = """
                 Gentile {1} {2} il tuo telefono non è abilitato all'invio dei dati sulle mire.
                Verifica che di essere registrato sul Sistema di Gestione Emergenze e registra il tuo codice fiscale {3} 
                """.format(self._count,nome, cognome, link)
                await self.sender.sendMessage(message, reply_markup=keyboard)
        elif self._check1 == 1:
            try:
                check +=1
                logging.info("ok1")
                testo_segnalazione = command
                keyboard = InlineKeyboardMarkup(inline_keyboard=[
                             [InlineKeyboardButton(text='Sì', callback_data='Confermi')],
                             [InlineKeyboardButton(text='No', callback_data='Riscrivi')]
                         ])
                logging.info("ok2")
                logging.info(command)
                message = "Messaggio {0} - Gentile {1} {2} ho recuperato il seguente messaggio:\n\n {3}".format(self._count,nome, cognome, command)
                await self.sender.sendMessage(message, reply_markup=keyboard)
            except:
                message = "Messaggio {0} - Gentile {1} {2} la sintassi del messaggio non era comprensibile, prova a riscrivere".format(self._count,nome, cognome)
                await self.sender.sendMessage(message)
        elif self._check1 == 10:
            if path.isfile(allegato)==True:
                logging.info("ok foto recuperata")
                keyboard = InlineKeyboardMarkup(inline_keyboard=[
                    [InlineKeyboardButton(text='Conferma', callback_data='OK')],
                    [InlineKeyboardButton(text='Annulla', callback_data='annulla_com')]
                ])
                logging.info("ok2")
                logging.info(command)
                message = "Messaggio {0} - Gentile {1} {2} ho recuperato il seguente messaggio:\n\n {3} \n\n\n e la" \
                          "foto salvata sul server ".format(self._count, nome, cognome, command, allegato)
                await self.sender.sendMessage(message, reply_markup=keyboard)
                message = "Messaggio {0} - Gentile {1} {2} ho recuperato anche la foto:\n\n".format(self._count,nome, cognome, command)

            else:
                message = "Messaggio {0} - Gentile {1} {2} immagine non recuperata, riprova".format(self._count,nome, cognome)
                await self.sender.sendMessage(message)
        elif self._check1 == 20:
            try:
                check +=1
                logging.info("ok20")
                testo_segnalazione20 = command
                keyboard = InlineKeyboardMarkup(inline_keyboard=[
                             [InlineKeyboardButton(text='Sì', callback_data='ConfermiCF')],
                             [InlineKeyboardButton(text='No', callback_data='RiscriviCF')]
                         ])
                logging.info("ok20_2")
                logging.info(command)
                if len(command)==16:
                    message = "Messaggio {0} - Gentile {1} {2} ho recuperato il seguente CF:\n\n {3}".format(self._count,nome, cognome, command)
                    await self.sender.sendMessage(message, reply_markup=keyboard)
                else: 
                    check=20
                    message = "Messaggio {0} - Gentile {1} {2} il codice fiscale deve essere composto da 16 cifre, prova a riscrivere".format(self._count,nome, cognome)
                    await self.sender.sendMessage(message)  
            except:
                message = "Messaggio {0} - Gentile {1} {2} la sintassi del messaggio non era comprensibile, prova a riscrivere".format(self._count,nome, cognome)
                await self.sender.sendMessage(message)
        
        
        
        elif self._check1 > 1:
            try:
                message = "Gentile {1} {2} stai scrivendo una comunicazione e il messaggio appena " \
                          "inviato non è riconosciuto dal sistema. \n" \
                          "Prova ad attenerti alle ultime istruzioni".format(self._count,nome, cognome)
                await self.sender.sendMessage(message, reply_markup=keyboard)
            except:
                message = "Gentile {1} {2} la sintassi del messaggio non era comprensibile, prova a riscrivere".format(self._count,nome, cognome)
                await self.sender.sendMessage(message)
        else:
                keyboard = InlineKeyboardMarkup(inline_keyboard=[
                             #[InlineKeyboardButton(text='IP del server', callback_data='ip')],
                             #[InlineKeyboardButton(text='START', callback_data='start')],
                             #[InlineKeyboardButton(text='Demo comunicazione', callback_data='proposta')],
                             [InlineKeyboardButton(text='Inserisci il CF', callback_data='codfisc')],
                             #[InlineKeyboardButton(text='Sito Gter', callback_data='info')],
                             #[InlineKeyboardButton(text='Demo Comunicazione', callback_data='demo_com')],
                             [InlineKeyboardButton(text='Recupera il tuo Telegram ID', callback_data='chat_id')],
                             #[InlineKeyboardButton(text='Time', callback_data='time')],
                         ])
                #bot.sendMessage(chat_id, 'Gentile {0} {1} questo è un bot configurato per alcune operazioni minimali, quanto hai scritto non è riconosciuto, invece di fotterti prova con i seguenti tasti:'.format(nome,cognome), reply_markup=keyboard)
                message = "Gentile {} {}, questo è un bot configurato per alcune operazioni minimali in fase di test.\n" \
                          "\nIl comando che hai inserito non è riconosciuto dal sistema, " \
                          "prova a usare i comandi definiti o il tasto seguente:".format(nome, cognome)
                await self.sender.sendMessage(message, reply_markup=keyboard)


# questa classe usa il CallbackQueryOriginHandler telepot.aio.helper.CallbackQueryOriginHandler (ossia è in ascolto dei tasti schoacchiati dal BOT)
class Quizzer(telepot.aio.helper.CallbackQueryOriginHandler):
    def __init__(self, *args, **kwargs):
        super(Quizzer, self).__init__(*args, **kwargs)
        self._score = {True: 0, False: 0}
        self._answer = None
        self._messaggio = ''
        self.step = 1
        global check
        logging.info("sono dentro quizzer e check vale{}".format(check))

    async def _show_next_question(self):
        x = random.randint(1,50)
        y = random.randint(1,50)
        sign, op = random.choice([('+', lambda a,b: a+b),
                                  ('-', lambda a,b: a-b),
                                  ('x', lambda a,b: a*b)])
        answer = op(x,y)
        question = 'STEP  %d %d %s %d = ?' % (self.step, x, sign, y)
        choices = sorted(list(map(random.randint, [-49]*4, [2500]*4)) + [answer])

        await self.editor.editMessageText(question,
            reply_markup=InlineKeyboardMarkup(
                inline_keyboard=[
                    list(map(lambda c: InlineKeyboardButton(text=str(c), callback_data=str(c)), choices))
                ]
            )
        )
        return answer



    async  def _chatid(self):
        sent = "Gentile {1} {2} il tuo codice (telegram id) da inserire nell'applicazione {0}".format(self.chat_id,self.nome, self.cognome)
        logging.info(sent)
        logging.info(check)
        await self.editor.editMessageText(sent)

    async  def _propose(self):
        self.step += 1
        global check
        check += 1
        sent = "Scrivi il testo della tua comunicazione"
        logging.info(sent)
        logging.info(check)
        await self.editor.editMessageText(sent)
        #sent = await self.sender.sendMessage('%d. Would you marry me?' % self.step, reply_markup=self.keyboard)
        #self._editor = telepot.aio.helper.Editor(self.bot, sent)
        #self._edit_msg_ident = telepot.message_identifier(sent)
        #return self._check


    async  def _cf(self):
        self.step += 1
        global check
        global chat_id
        check = 20
        sent = "Scrivi il tuo codice fiscale"
        logging.info(sent)
        logging.info(check)
        await self.editor.editMessageText(sent)
        #sent = await self.sender.sendMessage('%d. Would you marry me?' % self.step, reply_markup=self.keyboard)
        #self._editor = telepot.aio.helper.Editor(self.bot, sent)
        #self._edit_msg_ident = telepot.message_identifier(sent)
        #return self._check
        
        
    async  def _image_ask(self):
        global check
        check += 1
        question = "Vuoi inviare una foto?"
        logging.info(check)
        await self.editor.editMessageText(question,
            reply_markup= InlineKeyboardMarkup(inline_keyboard=[
                [InlineKeyboardButton(text='Sì', callback_data='yes_pic')],
                [InlineKeyboardButton(text='No', callback_data='no_pic')]
            ])
        )

    async  def _image_ask2(self):
        global check
        check = 10
        question = "Invia la tua foto?"
        logging.info(check)
        await self.editor.editMessageText(question,
            reply_markup= InlineKeyboardMarkup(inline_keyboard=[
                [InlineKeyboardButton(text='Ci ho ripensato, nessuna foto', callback_data='no_pic')]
            ])
        )

    
    async  def _verifica_cf(self, codice, chat_id):
        #verifica del CF
        global check
        logging.info(chat_id)
        check += 1 # ora vale 21
        con = psycopg2.connect(host=p.ip, dbname=p.db, user=p.user, password=p.pwd, port=p.port)
        cur = con.cursor()
        con.autocommit = True
        query_v='select count(cf) from users.utenti_esterni where cf ilike %s;'
        cod=(codice,)
        cur.execute(query_v,cod)
        x = cur.fetchone()[0]
        query_v='select count(codice_fiscale) from varie.dipendenti where codice_fiscale=%s;'
        cod=(codice,)
        cur.execute(query_v,cod)
        y = cur.fetchone()[0]
        if x==1 or y==1:
            try:
                query2="""
                INSERT INTO users.utenti_input_telegram(
	            matricola_cf, telegram_id) 
	            VALUES (%s, %s);
                """
                data2=(codice,chat_id,)
                cur.execute(query2,data2)
            except:
                query2="""
                UPDATE users.utenti_input_telegram
	            SET matricola_cf=%s, telegram_id=%s
	            WHERE %s;
                """
                data2=(codice,chat_id,codice,)
                cur.execute(query2,data2)
            check=0
            question = "Trovati {} cf e inserito a sistema. Ora sei abilitato per l'inserimento dei dati sulle mire".format(x)
            #controllo='sono arrivato qua'
            
            await self.editor.editMessageText(question)
        else:
            check=0
            message = """
            Gentile {1} {2} il codice fiscale non è registrato a sistema. 
            Verifica di averlo inserito correttamente e in caso contrario contatta gli amministratori d sistema
            """.format(nome, cognome)
            await self.sender.sendMessage(message)
        logging.info(check)
        con.close()
        

        
        
        
    async  def _end(self):
        global testo_segnalazione
        global allegato
        logging.info(testo_segnalazione)
        global check
        check = 0
        question = "Questa è solo una demo, ma ecco il testo della tua comunicazione, " \
                   "che verrà inserito sul sistema: \n\n{0}".format(testo_segnalazione)
        try:
            if path.isfile(allegato)==True:
                question = "{0}. \n\n insieme all'allegato {1}".format(question,allegato)
        except:
            question = "{0}. \n\n senza nessun allegato".format(question)
        logging.info(check)
        await self.editor.editMessageText(question)


    async  def _azzera(self):
        global testo_segnalazione
        global allegato
        global check
        testo_segnalazione =''
        allegato =''
        check = 0
        question = "Operazione annullata. Digita qualcosa per ripartire"

        logging.info(check)
        await self.editor.editMessageText(question)


    async def on_callback_query(self, msg):
        global testo_segnalazione
        global testo_segnalazione20
        global check
        global id_mira
        query_id, from_id, query_data = telepot.glance(msg, flavor='callback_query')
        #content_type, chat_type, chat_id = telepot.glance(msg)
        self.chat_id=from_id
        #parte copiata
        logging.info('Callback Query:', query_id, query_data)
        try:
            command = msg['text'] #get del comando inviato
        except:
            command="Nessun comando"
        try:
            self.nome = msg["from"]["first_name"]
        except:
            self.nome= ""
        try:
            self.cognome = msg["from"]["last_name"]
        except:
            self.cognome= ""
        is_bot = msg["from"]["is_bot"]
        if query_data=='ip':
            my_ip = urlopen('http://ip.42.pl/raw').read()
            message = "Gentile {} {}, l'indirizzo IP del server che ti sta rispondendo è {}".format(nome, cognome,my_ip)
            logging.info(message)
            #bot.sendMessage(chat_id, message)
            #await self.sender.sendMessage(message)
        elif query_data == 'start':
            logging.info('ho effettivamente schiacciato il bottone start')
            self._answer = await self._show_next_question()
        elif query_data == 'chat_id':
            #message = "Gentile {1} {2} il tuo codice (telegram id) da inserire nell'applicazione è {3}".format(self._count,nome, cognome, chat_id)
            #bot.sendMessage(chat_id,message)
            #await self.sender.sendMessage(message)
            logging.info('Definire la chat_id')
            logging.info(self.chat_id)
            self._answer = await self._chatid()
        elif query_data == 'proposta':
            logging.info('ho effettivamente schiacciato il bottone proposta')
            self._answer = await self._propose()
        elif query_data == 'codfisc':
            logging.info('ho effettivamente schiacciato il bottone codice fiscale')
            self._answer = await self._cf()
        elif query_data == 'mire':
            id_mira
            logging.info('ho effettivamente schiacciato il bottone codice fiscale')
            self._answer = await self._cf() 
               
        elif query_data == 'Confermi':
            logging.info(testo_segnalazione)
            self._answer = await self._image_ask()
        elif query_data == 'ConfermiCF':
            logging.info(testo_segnalazione20)
            self._answer = await self._verifica_cf(testo_segnalazione20,from_id)
        elif query_data == 'Riscrivi':
            logging.info(testo_segnalazione)
            check=0
            self._answer = await self._propose()
        elif query_data == 'RiscriviCF':
            logging.info(testo_segnalazione20)
            check=0
            self._answer = await self.cf()
        elif query_data == 'yes_pic':
            logging.info(testo_segnalazione)
            self._answer = await self._image_ask2()
        elif query_data == 'no_pic' or query_data == 'OK':
            logging.info(testo_segnalazione)
            self._answer = await self._end()
        elif query_data == 'annulla_com':
            self._answer = await self._azzera()
        #qua va messo il 
        elif query_data != 'start':
            logging.info('ora ho capito cosa succede qua')
            self._score[self._answer == int(query_data)] += 1
            self.step +=1
            self._answer = await self._show_next_question()


    async def on__idle(self, event):
        global check
        text = 'Comunicazione in TimeOut:'
        if check >0:
            await self.editor.editMessageText(
            text + '\n\n Sono trascorsi 120 s senza interazione. La comunicazione è stata interrotta',
            reply_markup=None)

        await asyncio.sleep(5)
        #await self.editor.deleteMessage()
        self.close()











# questo è il "main" del BOT che è in ascolto 
bot = telepot.aio.DelegatorBot(TOKEN, [
    #chat
    pave_event_space()(
        per_chat_id(), create_open, MessageCounter, timeout=120),
    # bottoni    
    pave_event_space()(
        per_callback_query_origin(), create_open, Quizzer, timeout=120),
])

loop = asyncio.get_event_loop()
loop.create_task(MessageLoop(bot).run_forever())
logging.info('Listening ...')

loop.run_forever()




# vecchio "main
#bot = telepot.Bot(TOKEN)
#MessageLoop(bot, {'chat': on_chat_message,
#                  'callback_query': on_callback_query}).run_as_thread() 
#stampa su server
#logging.info('Listening ...')
 
 
#while 1:
#    time.sleep(10)


