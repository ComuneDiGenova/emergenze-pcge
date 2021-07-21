#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Lorenzo Benvenuto, Roberta Fagandini
# copyleft 2021


from dataclasses import dataclass
import logging
import os
from aiogram.types.inline_keyboard import InlineKeyboardButton
import aiogram.utils.markdown as md
from aiogram.types import callback_query, message, message_entity, update
from aiogram.types.reply_keyboard import ReplyKeyboardRemove
from aiogram.dispatcher import FSMContext, middlewares
from aiogram.dispatcher.filters.state import State, StatesGroup
import conn
from aiogram.types import ParseMode
from aiogram import Bot, Dispatcher, executor, types
from aiogram.contrib.fsm_storage.memory import MemoryStorage
from datetime import datetime, timedelta
#import sqlite3
import psycopg2
import emoji
import config
import time
import asyncio

API_TOKEN = config.TOKEN

# Configure logging
logfile='{}/bot_sistema_emergenze.log'.format(os.path.dirname(os.path.realpath(__file__)))
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.INFO)




def esegui_query(connection,query,query_type):
    '''
    Function to execute a generic query in a postresql DB
    
    Query_type:
    
        i = insert
        u = update
        s = select
       
    The function returns:
    
        1 = if the query didn't succeed
        0 = if the query succeed (for query_type u and i)
        array of tuple with query's result = if the query succeed (for query_type s)
    '''
    
    if isinstance(query_type,str)==False:
        logging.warning('query type must be a str. The query {} was not executed'.format(query))
        return 1
    elif query_type != 'i' and query_type !='u' and query_type != 's':
        logging.warning('query type non recgnized for query: {}. The query was not executed'.format(query))
        return 1
    
    
    curr = connection.cursor()
    connection.autocommit = True
    try:
        curr.execute(query)
    except Exception as e:
        logging.error('Query non eseguita per il seguente motivo: {}'.format(e))
        return 1
    if query_type=='s':
        result= curr.fetchall() 
        curr.close()   
        return result
    else:
        return 0
    

# Initialize bot and dispatcher
bot = Bot(token=API_TOKEN)
storage = MemoryStorage()
dp = Dispatcher(bot, storage=storage)

class Form (StatesGroup):
    motivo = State()
    orario= State()
    tipopresa= State()
    chiudo= State()
    orarioPresidio= State()
    stop= State()


def keyboard (kb_config):
    _keyboard= types.InlineKeyboardMarkup ()
    for rows in kb_config:
        btn= types.InlineKeyboardButton (
            callback_data= rows [0],
            text= rows [1]
        )
        _keyboard.insert (btn)
    return _keyboard

''' async def invia_notifica(idinc, txt):
    print('ciao')
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    query_profilo = "SELECT id_profilo, max(id_segnalazione) as id_segn FROM segnalazioni.v_incarichi_interni WHERE id = {} group by id_profilo;".format(idinc)
    profilo= esegui_query(con,query_profilo,'s')
    if profilo[0][0] == 3:
        query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';"
    else:
        query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= {} and telegram_id !='' and telegram_attivo='t';".format(profilo[0][0])
    notifica =  esegui_query(con,query_notifica,'s')
    print(notifica)
    for tid in notifica:
        print(tid[0])
        await bot.send_message(tid[0], txt) '''


@ dp.callback_query_handler ()
async def callback (callback_query: types.CallbackQuery):

    await bot.answer_callback_query (callback_query.id, text= callback_query.data,)
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    
    if callback_query.data=='2' or callback_query.data=='4' or callback_query.data=='6' or callback_query.data=='8':
        await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
        query_time_operativo='''INSERT INTO users.t_presenze (operativo, data_inizio, durata, id_telegram, data_fine_hp)
        VALUES(true, now(), {0}, '{1}', now() + interval '{0} hours')'''.format(callback_query.data,callback_query.from_user.id)
        result=esegui_query(con,query_time_operativo,'i')
        if result ==0:
            termine_turno=datetime.now()+ timedelta(hours=int(callback_query.data))
            testo='{} Caro {}, la tua presenza è stata registrata. Il tuo turno comincia adesso e terminerà alle ore {}'.format(emoji.emojize(":white_check_mark:",use_aliases=True),callback_query.from_user.first_name,termine_turno.strftime("%H:%M"))
            await bot.send_message (callback_query.from_user.id, text= testo)
        else:
            await bot.send_message(callback_query.from_user.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))

    elif callback_query.data=='termina_turno':
        await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
        query_fine_turno="UPDATE users.t_presenze SET operativo=false, data_fine=now() WHERE operativo =true and id_telegram ='{}'".format(callback_query.from_user.id)
        result=esegui_query(con,query_fine_turno,'u')
        if result==0:
            #await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
            await bot.send_message(callback_query.from_user.id,"Caro {}, il tuo turno è stato chiuso correttamente.".format(callback_query.from_user.first_name))
        else:
            #await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
            await bot.send_message(callback_query.from_user.id,"Caro {}, si è verificato un problema nella chiusura del tuo turno.\nPer favore contatta un amministratore di sistema".format(callback_query.from_user.first_name))
    elif callback_query.data=='continua_turno':
        await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
        await bot.send_message(callback_query.from_user.id,"Hai annullato il comando e il tuo turno proseguirà. Utilizza nuovamente il comando /registra_uscita per terminare il turno")
    
    elif callback_query.data =='Carica' or callback_query.data =='Annulla':
        markup = types.ReplyKeyboardRemove()
        if callback_query.data == "Carica":
            await bot.send_message("Foto caricata a sistema!",reply_markup=markup)
        else:
            await bot.send_message("Comando annullato!",reply_markup=markup)
            
            
                

    """ if callback_query.data !='':
        testo='Gentile {} hai fornito la seguente motivazione {}'.format(callback_query.from_user.first_name, callback_query.data)
        await bot.send_message (callback_query.from_user.id, text= testo) """

#first command handler

@dp.message_handler(commands='start')
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/start` or `/help` command
    """
    await bot.send_message(message.chat.id,"Ciao {}!\nBenvenuto nel BOT del Sistema di Gestione Emergenze del Comune di Genova.\nScopri cosa può fare questo BOT andando sul comando /help".format(message.from_user.first_name))

@dp.message_handler(commands='help')
async def send_help(message: types.Message):
    """
    This handler will be called when user sends `/help` command
    """
    await bot.send_message(message.chat.id,"""Le funzioni BOT del Sistema di Gestione Emergenze del Comune di Genova sono:\n
    \n{0} start: avvia il bot per la prima volta
    \n{0} help: illustrazione funzionalità del BOT
    \n{0} telegram_id: restituisce il codice telegram da inserire nel portale
    \n{0} sito: restituisce il link per connettersi al portale delle emergenze
    \n{0} registra_presenza: comando per registrare la propria presenza all'inizio della fase operativa
    \n{0} registra_uscita: comando per registrare la conclusione della propria fase operativa
    \n{0} inserisci_mira: comando per inserire il valore della mira
    \n{0} comunicazione: comando per inserire una comunicazione su una data segnalazione
    \n\nPuoi accedere a questi comandi cliccando nel riquadro con il cararrete / in basso a destra. 
    \n\nTramite questo BOT potrai anche ricevere notifiche dal sistema. Per fare questo devi inserire il tuo telegram_id nel portale e attivare le notifiche.""".format(emoji.emojize(":arrow_forward:",use_aliases=True)))

# comando per telegram ID
@dp.message_handler(commands=['telegram_id'])
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/telegram_id` command
    """
    await message.reply("Ciao {}, il tuo codice (telegram id) da inserire nell'applicazione è {}".format(message.from_user.first_name,message.chat.id))

#comando per sito emergenze
@dp.message_handler(commands='sito')
async def start_cmd_handler(message: types.Message):
    keyboard_markup = types.InlineKeyboardMarkup(row_width=4)

    keyboard_markup.row(
        # url buttons have no callback data
        types.InlineKeyboardButton('Link al sito    {}{}'.format(emoji.emojize(":link:",use_aliases=True),emoji.emojize(":globe_with_meridians:",use_aliases=True)), url='https://emergenze.comune.genova.it/emergenze'),
    )

    await message.reply("Ciao {}, cliccando sul seguente bottone verrai reindirizzato al sito del Sistema di Getione Emergenze".format(message.from_user.first_name), reply_markup=keyboard_markup)



@dp.message_handler(commands=['registra_presenza'])
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/registra_presenza` command
    """
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    
    query_tlgrm_id= "select * from users.utenti_sistema where telegram_id ='{}'".format(message.chat.id)   
    registrato = esegui_query(con,query_tlgrm_id,'s')
    
    # if len(registrato==0:
    #     query_tlgrm_id= "select * from users.utenti_esterni where telegram_id ='{}'".format(message.chat.id)   
    #     registrato = esegui_query(con,query_tlgrm_id,'s')
    
    
    if registrato ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(registrato) !=0:
        #messaggio superfluo usato per debug
        #await bot.send_message(message.chat.id,'{} Il tuo utente è registrato nel sistema'.format(emoji.emojize(":white_check_mark:",use_aliases=True)))

        #controllo se utente risulta operativo
        query_operativo="select * from users.t_presenze where id_telegram ='{}' and operativo = 't'".format(message.chat.id)
        operativo=esegui_query(con,query_operativo,'s')
        #print(operativo)
        if operativo ==1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(operativo)!=0:
            await bot.send_message(message.chat.id,'''{} Caro {}, in questo momento risulti già operativo. 
                                   \nProva a chiudere il tuo turno con il comando /registra_uscita, oppure contatta un amministratore di sistema'''.format(emoji.emojize(":warning:",use_aliases=True) ,message.from_user.first_name))
        elif len(operativo)==0:

            await bot.send_message(
                chat_id=message.from_user.id,
                text='''{} Caro {}, al momento non risulti operativo, quindi puoi iniziare il tuo turno.
                \n\n{} Quante ore prevedi di rimanere operativo?'''.format(emoji.emojize(":white_check_mark:",use_aliases=True),message.from_user.first_name,emoji.emojize(":hourglass_flowing_sand:",use_aliases=True)),
                reply_markup= keyboard ([
                                        ["2", "2 ore", "message text", None],
                                        ["4", "4 ore", "message text", None],
                                        ["6", "6 ore", "message text", None],
                                        ["8", "8 ore", "message text", None]
                                        ])
                                    )
            #elimino messaggio con comando per evitare tocchi maldestri
            await bot.delete_message(message.chat.id,message.message_id)
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                               \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))



@dp.message_handler(commands=['registra_uscita','tuscita'])
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/start` or `/help` command
    """
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    #await bot.send_message(message.chat.id,"Ciao {} stai per terminare il tuo turno".format(message.from_user.first_name))
    #controlli
    
    controllo_operativo="SELECT operativo, data_inizio, durata, data_fine, id, id_telegram FROM users.t_presenze where operativo =true and id_telegram ='{}'".format(message.chat.id)
    result_operativo=esegui_query(con,controllo_operativo,'s')
    if result_operativo ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(result_operativo)==0:
        await bot.send_message(message.chat.id,'''{} Caro {}, in questo momento non risulti operativo. 
                                   \nProva ad iniziare il tuo turno con il comando /registra_presenza, oppure contatta un amministratore di sistema'''.format(emoji.emojize(":warning:",use_aliases=True) ,message.from_user.first_name))
    elif len(result_operativo)==1:
        await bot.send_message(message.chat.id,text='Sei sicuro di voler terminare ora il tuo turno?',
        reply_markup= keyboard ([
                                        ["termina_turno", "si", "message text", None],
                                        ["continua_turno", "annulla", "message text", None]
                                        ])
                                    )
    else:
        print('errore che non ho ancora scoperto')
        

##### INIZIO BOT MIRE #####

class FormPresa (StatesGroup):
    rivo = State()
    mira = State ()

@dp.message_handler(state=FormPresa.rivo)
async def process_presa(message: types.Message, state: FSMContext):
    async with state.proxy() as data:       
        if message.text not in data['rivi'].keys():
            await message.reply('Il rio inserito non è valido. Seleziona il rio usando le opzioni presenti sulla tastiera.')
        else:
            data['rivo'] = message.text             
            markup_old = types.ReplyKeyboardRemove()
            await bot.send_message(message.chat.id,'Hai inserito {}'.format(data['rivo']),reply_markup=markup_old)
            await FormPresa.next()
            markup_new = types.ReplyKeyboardMarkup(resize_keyboard=True, selective=True)
            markup_new.add("Verde {}".format(emoji.emojize(":green_circle:",use_aliases=True)), "Giallo {}".format(emoji.emojize(":yellow_circle:",use_aliases=True)),"Rosso {}".format(emoji.emojize(":red_circle:",use_aliases=True)))
            #await message.reply("Hai indicato {} minuti quindi l'ora di inizio è {} circa.\n La presa in carico è:".format(data['orario'], timepreview), reply_markup=markup)
            await message.reply("Come valuti la mira per il rivo scelto?", reply_markup=markup_new)
        
#funzione che controlla che schiaccino un bottone tra quelli proposti nella tastiera
@dp.message_handler(lambda message: message.text not in ["Verde {}".format(emoji.emojize(":green_circle:",use_aliases=True)), "Giallo {}".format(emoji.emojize(":yellow_circle:",use_aliases=True)),"Rosso {}".format(emoji.emojize(":red_circle:",use_aliases=True))], state=FormPresa.mira)
async def process_gender_invalid(message: types.Message):
    return await message.reply("Il valore inserito non è valido. Seleziona un valore usando le opzioni presenti sulla tastiera.")
        
@dp.message_handler(state=FormPresa.mira)
async def process_presa(message: types.Message, state: FSMContext):
    async with state.proxy() as data:
        data['mira'] = message.text

        #assegno il valore della mira all'id corrispondente        
        if data['mira'][:-2]=='Verde':
            id_lettura=1
        elif data['mira'][:-2]=='Giallo':
            id_lettura=2
        elif data['mira'][:-2]=='Rosso':
            id_lettura=3
        
        # l'id del rivo lo recupero dal dizionario mettendo come chiave il nome del rivo selezionato
        query_update_mira='INSERT INTO geodb.lettura_mire (num_id_mira,id_lettura,data_ora) VALUES({},{},now()::timestamp(0))'.format(data['rivi'][data['rivo']][0],id_lettura)
        query_log='''INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('geodb','{}', 'Inserita lettura mira . {}')'''.format(data['user'],data['rivi'][data['rivo']][0])
        
        con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
        inserimento_mira=esegui_query(con,query_update_mira,'i')
        resultlog=esegui_query(con,query_log,'i')
                
        if inserimento_mira==0 and resultlog==0:
            # Remove keyboard
            markup = types.ReplyKeyboardRemove()

            # And send message
            await bot.send_message(message.chat.id,'''Mira inserita correttamente.\nRiepilogo dei dati inseriti:
                                \nRivo/Mira: {}
                                \nValore: {}
                                '''.format(data['rivo'],data['mira'],),reply_markup=markup)        

            await state.finish()
        else:
            # Remove keyboard
            markup = types.ReplyKeyboardRemove()

            # And send message
            await bot.send_message(message.chat.id,'''Si è verificato un problema nell'inserimento della mira nel database.
                                   \nSe visualizzi questo messaggio prova ad usare nuovamente il comando /inserisci_mira o a contattare un tecnico''',reply_markup=markup)        

            await state.finish()
            

@dp.message_handler(commands=['inserisci_mira'])
async def send_welcome(message: types.Message,state: FSMContext):
    """
    This handler will be called when user sends `/inserisci_mira` command
    """
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')
    
    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e non è possibile capire se il tuo utente è registrato nel sistema:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    
    elif len(registered_user) !=0:
        
        query_presidio_mobile= '''select * from users.v_componenti_squadre vcs left join segnalazioni.v_sopralluoghi_mobili_last_update vsmlu on vcs.id::text = vsmlu.id_squadra::text
        where vcs.matricola_cf ='{}' and vcs.data_end is null and vsmlu.id_stato_sopralluogo =2'''.format(registered_user[0][0])
        pm_assegnato = esegui_query(con,query_presidio_mobile,'s')
                
        if pm_assegnato == 1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        
        elif len(pm_assegnato) !=0:
            percorso_assegnato=pm_assegnato[0][17]
            id_evento=pm_assegnato[0][25]
            
            #controllo se presenza FOC
            
            queryfoc="SELECT * FROM eventi.v_foc WHERE id_evento={} and data_ora_fine_foc > now()".format(id_evento)
            foc=esegui_query(con,queryfoc,'s')
            if foc==1:
                 await bot.send_message(message.chat.id,'''{} Si è verificato un problema sulla verifica della presenza di una F.O.C.:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
            elif len(foc)!=0:
               
                if foc[0][2]=='Attenzione':
                    nome_perc='perc_al_g'
                elif foc[0][2]=='Pre-allarme':
                    nome_perc='perc_al_a'
                elif foc[0][2]=='Allarme':
                    nome_perc='perc_al_r'
                
                query_mire='''SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome
			                    FROM geodb.punti_monitoraggio_ok p WHERE p.id is not null and "{}"= '{}'
                       order by nome'''.format(nome_perc,percorso_assegnato)
                testo_dati=esegui_query(con,query_mire,'s') 
                
                if len(testo_dati)==0:
                    await bot.send_message(message.chat.id,'''Non ci sono rivi per cui inserire la mira.
                                           \nDurante la F.O.C. {}, infatti il percorso {}, che è stato assegnato alla tua squadra, è disabilitato.'''.format(foc[0][2],percorso_assegnato))
                    
                else:              
                    mire={}               
                    markup = types.ReplyKeyboardMarkup(resize_keyboard=True, selective=True)
                    for i in testo_dati:
                        markup.add(i[1],)
                        mire[i[1]]=i   
                    await FormPresa.rivo.set()
                    async with state.proxy() as data:
                        data['rivi']=mire
                        data['user']=registered_user[0][0]
                    
                    await bot.send_message(message.chat.id,'Per quale tra i seguenti rivi vuoi inserire la mira?',reply_markup=markup)

            else:
                await bot.send_message(message.chat.id,'''{} Al momento non risultano F.O.C. attive sul tuo evento, per cui non puoi usare questo comando.'''.format(emoji.emojize(":warning:",use_aliases=True)))
        
        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano presidi mobili assegnati e/o accettati alla tua squadra, per cui non puoi usare questo comando.'''.format(emoji.emojize(":warning:",use_aliases=True)))
        #await bot.delete_message(message.chat.id,message.message_id)
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                            \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando.'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))
    
##### FINE BOT MIRE #####


##### INIZIO BOT COMUNICAZIONE #####

class FormComunicazione (StatesGroup):

    testo_com = State()
    foto_flag = State ()
    foto = State ()


@dp.message_handler(state=FormComunicazione.testo_com)
async def process_testo(message: types.Message, state: FSMContext):
    async with state.proxy() as data:
        data['testo_com'] = message.text
        
        await FormComunicazione.next()
        markupnext=types.ReplyKeyboardMarkup(resize_keyboard=True, selective=True)

        markupnext.add("Foto","Invia")
        
        await message.reply("{} Vuoi allegare una foto alla comunicazione inserita o inviarla senza foto?".format(emoji.emojize(":camera:",use_aliases=True)),reply_markup=markupnext)
       
        
@dp.message_handler(lambda message: message.text not in ["Foto","Invia"], state=FormComunicazione.foto_flag)
async def process_gender_invalid(message: types.Message):
    return await message.reply("Comando non valido. Seleziona il comando usando le opzioni presenti sulla tastiera.")

@dp.message_handler(state=FormComunicazione.foto_flag)
async def process_presa(message: types.Message, state: FSMContext):
    async with state.proxy() as data:
        data['foto_com'] = message.text
        markupend=types.ReplyKeyboardRemove()
        if data['foto_com']=='Foto':
            
           
            await message.reply("Scatta una foto dal tuo dispositivo ",reply_markup=markupend)
            await FormComunicazione.next()        
        else:

            if data['tipo']=='presidio mobile':
                
                qinsertpm='''INSERT INTO segnalazioni.t_comunicazioni_sopralluoghi_mobili(id_sopralluogo, testo) VALUES ({},'{}')'''.format(data['id_pm'],data['testo_com'])
                query_logpm='''INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','{}', 'Inviata comunicazione a PC (presidio mobile {})')'''.format(data['user'],data['testo_com'])
                con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port) 
                
                resultinspm=esegui_query(con, qinsertpm,'i')
                resultlogpm=esegui_query(con,query_logpm,'i')
                
                if resultinspm==1 or resultlogpm==1:
                    await message.reply('Si è verificato un problema tecnico nell\'invio della comunicazione',reply_markup=markupend)
                else:
                    await message.reply('{}{} Comunicazione senza foto inviata'.format(emoji.emojize(":arrow_right:",use_aliases=True),emoji.emojize(":email:",use_aliases=True)),reply_markup=markupend)
                await state.finish()
                
            else:
            
                con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port) 
                qinsertcom='''INSERT INTO segnalazioni.t_comunicazioni_segnalazioni(id_lavorazione, mittente, testo) VALUES({},'{}','{}') '''.format(data['id_lavorazione'],data['mittente'],data['testo_com'])
                query_log=''' INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','{}', 'Inviata comunicazione a PC su segnalazione {}') '''.format(data['user'], data['id_segnalazione'])
                resultins=esegui_query(con, qinsertcom,'i')
                resultlog=esegui_query(con,query_log,'i')
                
                if resultins==1 or resultlog==1:
                    await message.reply('Si è verificato un problema tecnico nell\'invio della comunicazione',reply_markup=markupend)
                else:
                    await message.reply('{}{} Comunicazione senza foto inviata'.format(emoji.emojize(":arrow_right:",use_aliases=True),emoji.emojize(":email:",use_aliases=True)),reply_markup=markupend)
                await state.finish()
            

@dp.message_handler(content_types=types.ContentType.ANY, state=FormComunicazione.foto)
async def process_foto(message: types.Message, state: FSMContext):
    if message.content_type is types.ContentType.PHOTO:      
        async with state.proxy() as data: 
            data['foto'] = message.photo[-1]
            photo_name='{}_{}.jpg'.format(datetime.now().strftime("%Y%m%d%H%M"),message.chat.id)

            if data['tipo']=='presidio mobile':
                
                destination='/home/local/COMGE/egter01/emergenze_uploads/telegram/e_{}/pm_{}'.format(data['id_evento'],data['id_pm'])

                if not os.path.exists(destination):

                    os.system('mkdir -p {}'.format(destination))

                await data['foto'].download('{}/{}'.format(destination,photo_name))
                markupend=types.ReplyKeyboardRemove()
                con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port) 
                allegato='{}/{}'.format(destination[26:],photo_name)
                
                qinsertpm='''INSERT INTO segnalazioni.t_comunicazioni_sopralluoghi_mobili(id_sopralluogo, testo, allegato) VALUES ({},'{}','{}')'''.format(data['id_pm'],data['testo_com'],allegato)
                query_logpm='''INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','{}', 'Inviata comunicazione a PC (presidio mobile {})')'''.format(data['user'],data['testo_com'])
                
                resultinspm=esegui_query(con, qinsertpm,'i')
                resultlogpm=esegui_query(con,query_logpm,'i')
                
                if resultinspm==1 or resultlogpm==1:
                    await message.reply('Si è verificato un problema tecnico nell\'invio della comunicazione',reply_markup=markupend)
                else:
                    await message.reply('{}{} Comunicazione senza foto inviata'.format(emoji.emojize(":arrow_right:",use_aliases=True),emoji.emojize(":email:",use_aliases=True)),reply_markup=markupend)
                await state.finish()
                
                
                
            else:
                destination='/home/local/COMGE/egter01/emergenze_uploads/telegram/e_{}/s_{}'.format(data['id_evento'],data['id_segnalazione'])

                if not os.path.exists(destination):

                    os.system('mkdir -p {}'.format(destination))

                await data['foto'].download('{}/{}'.format(destination,photo_name))
                
                con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port) 

                allegato='{}/{}'.format(destination[26:],photo_name)
                
                qinsertcom='''INSERT INTO segnalazioni.t_comunicazioni_segnalazioni(id_lavorazione, mittente, testo, allegato) VALUES({},'{}','{}','{}') '''.format(data['id_lavorazione'],data['mittente'],data['testo_com'],allegato)
                query_log=''' INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','{}', 'Inviata comunicazione a PC su segnalazione {}') '''.format(data['user'], data['id_segnalazione'])
                resultins=esegui_query(con, qinsertcom,'i')
                resultlog=esegui_query(con,query_log,'i')
                markupend=types.ReplyKeyboardRemove()
            
                if resultins==1 or resultlog==1:
                    await message.reply('Si è verificato un problema tecnico nell\'invio della comunicazione',reply_markup=markupend)
                else:
                    await message.reply('{}{} Comunicazione con foto inviata al sistema'.format(emoji.emojize(":arrow_right:",use_aliases=True), emoji.emojize(":email:",use_aliases=True)),reply_markup=markupend)

                await state.finish()
    else:
        await message.reply('Contenuto del messaggio non valido. Inserisci una foto.')
        
        
            
@dp.message_handler(commands='comunicazione')
async def comunication(message: types.Message,state=FSMContext):
    """
    This handler will be called when user sends `/comunicazione` command
    """
    
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')
    
    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e non è possibile capire se il tuo utente è registrato nel sistema:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    
    elif len(registered_user) !=0:
        
        q1='''select * from users.v_componenti_squadre vcs left join users.v_squadre_notifica vsn on vcs.id_squadra ::text = vsn.id ::text 
                where vcs.matricola_cf ='{}' and  (vsn.id_incarico_interno is not null or vsn.id_sopralluogo is not null or vsn.id_sm is not null)'''.format(registered_user[0][0])
        #lo scopo di questa query è capire se alla squadra è assegnato un incarico interno, un presidio fisso o un presidio mobile e ricavarne l id.
        incarico=esegui_query(con,q1,'s')
        tipi_compiti=['incarico interno','presidio fisso','presidio mobile']
        if incarico==1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e non è possibile se alla tua squadra sono stati assegnati dei compiti:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(incarico)!=0:
            inizio_com=False
            if incarico[0][-3]:
                
                #caso incarico interno
                inizio_com=True
                tipo= 'incarico interno'
                queryii='''select * from users.v_componenti_squadre vcs left join segnalazioni.v_incarichi_interni vii on vcs.id ::text =vii.id_squadra ::text 
                            where vcs.matricola_cf ='{}' and vii.id_stato_incarico=2 and time_stop is null'''.format(registered_user[0][0])
                resultii=esegui_query(con,queryii,'s')
                
                if resultii==1:
                    await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e non è possibile risalite all'id dell'evento:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
                elif len(resultii)!=0:
                    #print(resultii)
                    user=resultii[0][2]
                    mittente='{} {} ({})'.format(resultii[0][4],resultii[0][5],resultii[0][6])
                    id_segnalazione=resultii[0][-10]
                    id_lavorazione=resultii[0][-9]
                    id_evento=resultii[0][-8]
                    id_pm=''
                else:
                    await bot.send_message(message.chat.id,'''{} Attenzione: l'incarico assegnato alla tua squadra potrebbe non esser ancora stato preso in carico, pertanto non è possibile inserire una comunicazione'''.format(emoji.emojize(":warning:",use_aliases=True)))           
                
            elif incarico[0][-2]:
                #caso presidio fisso
                inizio_com=True
                tipo='presidio fisso'
                querypf='''select * from users.v_componenti_squadre vcs left join segnalazioni.v_sopralluoghi vs on vcs.id_squadra  ::text = vs.id_squadra ::text
                           where vcs.matricola_cf ='{}' and vs.id_stato_sopralluogo =2 and vs.time_stop is null'''.format(registered_user[0][0])
                resultpf=esegui_query(con,querypf,'s')
                if resultpf==1:
                    await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e non è possibile risalite all'id dell'evento:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
                elif len(resultpf)!=0:
                    
                    user=resultpf[0][2]
                    mittente='{} {} ({})'.format(resultpf[0][4],resultpf[0][5],resultpf[0][6])
                    id_segnalazione=resultpf[0][-10]
                    id_lavorazione=resultpf[0][-9]
                    id_evento=resultpf[0][-8]
                    id_pm=''

                else:
                    await bot.send_message(message.chat.id,'''{} Attenzione: il presidio fisso assegnato alla tua squadra potrebbe non esser ancora stato preso in carico, pertanto non è possibile inserire una comunicazione'''.format(emoji.emojize(":warning:",use_aliases=True)))
                
            elif incarico[0][-1]:
                #caso presidio mobile
                tipo='presidio mobile'
                
                querypm='''select * from users.v_componenti_squadre vcs left join segnalazioni.v_sopralluoghi_mobili vsm on vcs.id ::text =vsm.id_squadra ::text 
                            where vcs.matricola_cf ='{}' and vsm.id_stato_sopralluogo =2 and time_stop is null''' .format(registered_user[0][0])
                resultpm=esegui_query(con,querypm,'s')
                
                if resultpm ==1:
                    await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e non è possibile risalite all'id dell'evento:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
                elif len(resultpm)!=0:
                    
                    user=resultpm[0][2]
                    mittente='{} {} ({})'.format(resultpm[0][4],resultpm[0][5],resultpm[0][6])
                    id_segnalazione=''
                    id_lavorazione=''
                    id_evento=resultpm[0][-5]
                    id_pm=resultpm[0][14]
                    queryfoc="SELECT * FROM eventi.v_foc WHERE id_evento={} and data_ora_fine_foc > now()".format(id_evento)
                    
                    foc=esegui_query(con,queryfoc,'s')
                    if foc==1:
                        await bot.send_message(message.chat.id,'''{} Si è verificato un problema sulla verifica della presenza di una F.O.C.:
                                    \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
                        
                    elif len(foc)!=0:
                        inizio_com=True
                        
                    else:
                        await bot.send_message(message.chat.id,'''{} Stai cercando di inserire una comunicazione sul presidio mobile a te assegnato, ma al momento non risultano F.O.C. attive su questo tuo evento. Per questo motivo non puoi usare questo comando, riprova ad utilizzarlo quando ci sarà una F.O.C. attiva.'''.format(emoji.emojize(":warning:",use_aliases=True)))

                else:
                    await bot.send_message(message.chat.id,'''{} Attenzione: il presidio mobile assegnato alla tua squadra potrebbe non esser ancora stato preso in carico, pertanto non è possibile inserire una comunicazione'''.format(emoji.emojize(":warning:",use_aliases=True)))
            
            if inizio_com==True:
                await FormComunicazione.testo_com.set()
                async with state.proxy() as data:
                    data['tipo']=tipo
                    data['user']=user
                    data['mittente']=mittente
                    data['id_segnalazione']=id_segnalazione
                    data['id_lavorazione']=id_lavorazione
                    data['id_evento']=id_evento
                    data['id_pm']=id_pm
        
                await bot.send_message(message.chat.id,"""Alla tua squadra risulta assegnato un {} sulla segnalazine {} nell\'ambito dell\'evento {}.
                                    \n{} Inserisci il testo della comunicazione che vuoi mandare alla centrale""".format(tipo,id_segnalazione,id_evento,emoji.emojize(":memo:",use_aliases=True)))
                        
        else:
            await bot.send_message(message.chat.id,'''{} Alla tua squadra in questo momento non sono stati assegnati incarichi o presidi, pertanto non è possibile inserire una comunicazione.'''.format(emoji.emojize(":warning:",use_aliases=True)))
    
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                            \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando.'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))
   
    

##### FINE BOT COMUNICAZIONE #####
    
##### INIZIO BOT INCARICHI INTERNI #####
# Check orario inizio incarico interno è numerico
@dp.message_handler(lambda message: not message.text.isdigit(), state=Form.orario)
async def process_orario_invalid(message: types.Message):
    return await message.reply("I minuti inseriti non sono validi, devi inserire un numero")

# gestione orario inizio incarico interno  
@dp.message_handler(lambda message: message.text.isdigit(), state= Form.orario)
async def process_orario(message: types.Message, state: FSMContext):

    async with state.proxy() as data:
        data['orario']= message.text
        inizio_incarico = datetime.now() + timedelta(minutes=int(data['orario']))
        timepreview = inizio_incarico.replace(second=0, microsecond=0).time()

    await Form.next()
    markup = types.ReplyKeyboardMarkup(resize_keyboard=True, selective=True)
    markup.add("Regolare", "Parziale")
    #await message.reply("Hai indicato {} minuti quindi l'ora di inizio è {} circa.\n La presa in carico è:".format(data['orario'], timepreview), reply_markup=markup)
    await message.reply("La presa in carico è regolare o parziale?", reply_markup=markup)
    #await state.finish ()

#funzione che controlla che schiaccino un bottone per presa in carico
@dp.message_handler(lambda message: message.text not in ["Regolare", "Parziale"], state=Form.tipopresa)
async def process_gender_invalid(message: types.Message):
    return await message.reply("Il valore inserito non è valido. Seleziona il valore dalla tastiera.")

# accettazione incarico interno  scrittura su DB  
@dp.message_handler(state=Form.tipopresa)
async def process_presa(message: types.Message, state: FSMContext):
    async with state.proxy() as data:
        data['tipopresa'] = message.text

        # Remove keyboard
        markup = types.ReplyKeyboardRemove()

        # And send message
        await bot.send_message(
            message.chat.id,
            md.text(
                md.text('La presa in carico è ', md.bold(data['tipopresa']), '.\n'),
                md.text('Prevedi di iniziare l\'incarico tra ', md.bold(data['orario']), ' minuti.\n'),
                md.text('Una volta completato l\'incarico ricordati di chiuderlo digitando /chiudo.\n'),
            ),
            reply_markup=markup,
            parse_mode=ParseMode.MARKDOWN,
        )
    inizio_incarico = datetime.now() + timedelta(minutes=int(data['orario']))
    inizio_preview = inizio_incarico.replace(second=0, microsecond=0)
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    query_incarico2= '''select us.matricola_cf, vc.id, vc.nome_squadra, viilu.id, vii.id_lavorazione, vii.id_profilo, vii.id_segnalazione
            from users.utenti_sistema us 
            left join users.v_componenti_squadre vc on us.matricola_cf = vc.matricola_cf 
            left join segnalazioni.v_incarichi_interni_last_update viilu on vc.id::text = viilu.id_squadra::text
            left join segnalazioni.v_incarichi_interni vii on viilu.id = vii.id
            where us.telegram_id = '{}' and vc.data_end is null and viilu.id_stato_incarico =1'''.format(message.chat.id)
    incarico_assegnato2 = esegui_query(con,query_incarico2,'s')
    print(incarico_assegnato2)
    query_time= "UPDATE segnalazioni.t_incarichi_interni SET time_preview='{}' WHERE id={};".format(inizio_preview, incarico_assegnato2[0][3])
    time_inizio = esegui_query(con,query_time,'u')
    if data['tipopresa'] == 'Parziale':
        query_stato_p= "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico, parziale) VALUES ({}, 2 , 'true');".format(incarico_assegnato2[0][3])
        stato_p = esegui_query(con,query_stato_p,'i')
        query_storico_p = '''INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento)
            VALUES ({0}, ' Incarico interno {1} preso in carico (parzialmente) dalla seguente squadra: {2} - <a class="btn btn-info" href="dettagli_incarico_interno.php?id={1}"> Visualizza dettagli </a>');'''.format(incarico_assegnato2[0][4], incarico_assegnato2[0][3], incarico_assegnato2[0][2])
        storico_p = esegui_query(con,query_storico_p,'i')
    else:
        query_stato_r= "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico) VALUES ({}, 2 );".format(incarico_assegnato2[0][3])
        stato_r = esegui_query(con,query_stato_r,'i')
        query_storico_r = '''INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento)
            VALUES ({0}, ' Incarico interno {1} preso in carico dalla seguente squadra: {2} - <a class="btn btn-info" href="dettagli_incarico_interno.php?id={1}"> Visualizza dettagli </a>');'''.format(incarico_assegnato2[0][4], incarico_assegnato2[0][3], incarico_assegnato2[0][2])
        storico_r = esegui_query(con,query_storico_r,'i')
    query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','{}', 'Incarico interno {} preso in carico');".format(incarico_assegnato2[0][0], incarico_assegnato2[0][3])
    log = esegui_query(con,query_log,'i')
    """ if incarico_assegnato2[0][5] == 3:
        query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';"
    else:
        query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= {} and telegram_id !='' and telegram_attivo='t';".format(incarico_assegnato2[0][5])
    notifica =  esegui_query(con,query_notifica,'s')
    print(notifica)
    for tid in notifica:
        print(tid[0])
        await bot.send_message(tid[0], '{} L\'incarico interno assegnato alla squadra {} sulla segnalazione {} è stato accettato'.format(emoji.emojize(":thumbsup:",use_aliases=True), incarico_assegnato2[0][2], incarico_assegnato2[0][6])) """
    # Finish conversation
    await state.finish()
    
# accettazione incarico interno   
@dp.message_handler(commands=['accetto'])
async def send_accetto(message: types.Message):
    """
    This handler will be called when user sends `/accetto` command
    """
    
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')
    #print(registered_user[0][0])
    
    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(registered_user) !=0:
        query_incarico= '''select * from users.v_componenti_squadre vcs 
        left join segnalazioni.v_incarichi_interni_last_update viilu on vcs.id::text = viilu.id_squadra::text 
        where vcs.matricola_cf = '{}' and vcs.data_end is null and viilu.id_stato_incarico =1'''.format(registered_user[0][0])
        incarico_assegnato = esegui_query(con,query_incarico,'s')
        #id_squadra=incarico_assegnato[0][0]
        #print(id_squadra)
        #id_incarico=incarico_assegnato[0][14]
        if incarico_assegnato == 1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(incarico_assegnato) !=0:
            await Form.orario.set()
            await message.reply("Ciao {} hai accettato l'incarico {}. Tra quanti minuti sarai sul posto?".format(message.from_user.first_name, emoji.emojize(":thumbs_up:",use_aliases=True)))

        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano incarichi assegnati alla tua squadra'''.format(emoji.emojize(":warning:",use_aliases=True)))
        #await bot.delete_message(message.chat.id,message.message_id)
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                            \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))

# rifiuto incarico interno  scrittura su DB
@dp.message_handler(state= Form.motivo)
async def process_motivo(message: types.Message, state: FSMContext):

    async with state.proxy() as data:
        data['motivo']= message.text
        await bot.send_message(
            message.chat.id,
            md.text(
                md.text('Hai fornito questo motivo: ', md.bold(data['motivo'])),
            ),
        )
        print(message.text, message.chat.id)
        con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
        query_incarico2= '''select us.matricola_cf, vc.id, vc.nome_squadra, viilu.id, vii.id_lavorazione, vii.id_profilo, vii.id_segnalazione
            from users.utenti_sistema us 
            left join users.v_componenti_squadre vc on us.matricola_cf = vc.matricola_cf 
            left join segnalazioni.v_incarichi_interni_last_update viilu on vc.id::text = viilu.id_squadra::text
            left join segnalazioni.v_incarichi_interni vii on viilu.id = vii.id
            where us.telegram_id = '{}' and vc.data_end is null and viilu.id_stato_incarico =1'''.format(message.chat.id)
        incarico_assegnato2 = esegui_query(con,query_incarico2,'s')
        #print(message.chat.id, incarico_assegnato2)
        query_motivo= "UPDATE segnalazioni.t_incarichi_interni SET note_rifiuto='{}' WHERE id={};".format(message.text, incarico_assegnato2[0][3])
        update_motivo = esegui_query(con,query_motivo,'u')
        query_stato = "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico) VALUES ({}, 4)".format(incarico_assegnato2[0][3])
        stato = esegui_query(con,query_stato,'i')
        #print('id={}'.format(incarico_assegnato2[0][1]))
        query_squadra = "UPDATE users.t_squadre SET id_stato=2 WHERE id={}".format(incarico_assegnato2[0][1])
        squadra = esegui_query(con,query_squadra,'u')
        query_storico = '''INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) 
            VALUES ({0}, ' Incarico {1} rifiutato dalla seguente squadra: {2} - <a class="btn btn-info" href="dettagli_incarico_interno.php?id={1}"> Visualizza dettagli </a>');'''.format(incarico_assegnato2[0][4], incarico_assegnato2[0][3], incarico_assegnato2[0][2])
        storico = esegui_query(con,query_storico,'i')
        query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','{}', 'Incarico interno {} rifiutato');".format(incarico_assegnato2[0][0], incarico_assegnato2[0][3])
        log = esegui_query(con,query_log,'i')
        if incarico_assegnato2[0][5] == 3:
            query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';"
        else:
            query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= {} and telegram_id !='' and telegram_attivo='t';".format(incarico_assegnato2[0][5])
        notifica =  esegui_query(con,query_notifica,'s')
        print(notifica)
        for tid in notifica:
            print(tid[0])
            await bot.send_message(tid[0], '{} L\'incarico interno assegnato alla squadra {} sulla segnalazione {} è stato rifiutato con le seguenti note: {}'.format(emoji.emojize(":x:",use_aliases=True), incarico_assegnato2[0][2], incarico_assegnato2[0][6], message.text))
        #notifica che l'incarico è stato rifiutato? a chi?
    await state.finish () 

# rifiuto incarico interno   
@dp.message_handler(commands='rifiuto')
async def send_rifiuto(message: types.Message):
    """
    This handler will be called when user sends `/rifiuto` command
    """
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')
    #print(registered_user[0][0])
    
    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(registered_user) !=0:
        query_incarico= '''select * from users.v_componenti_squadre vcs 
        left join segnalazioni.v_incarichi_interni_last_update viilu on vcs.id::text = viilu.id_squadra::text 
        where vcs.matricola_cf = '{}' and vcs.data_end is null and viilu.id_stato_incarico =1'''.format(registered_user[0][0])
        incarico_assegnato = esegui_query(con,query_incarico,'s')
        #id_squadra=incarico_assegnato[0][0]
        #print(id_squadra)
        #id_incarico=incarico_assegnato[0][14]
        if incarico_assegnato == 1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(incarico_assegnato) !=0:
            await Form.motivo.set()
            await message.reply("Ciao {} hai rifiutato l'incarico {}. Per favore fornisci la motivazione digitando un breve testo.".format(message.from_user.first_name, emoji.emojize(":thumbsdown:",use_aliases=True)))
        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano incarichi assegnati alla tua squadra'''.format(emoji.emojize(":warning:",use_aliases=True)))
        #await bot.delete_message(message.chat.id,message.message_id)
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                               \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))

# chiusura incarico interno scrittura su DB
@dp.message_handler(state= Form.chiudo)
async def process_chiudo_note(message: types.Message, state: FSMContext):

    async with state.proxy() as data:
        data['chiudo']= message.text
        await bot.send_message(
            message.chat.id,
            md.text(
                md.text('Hai fornito queste note: ', md.bold(data['chiudo']), '.'),
            ),
        )
        print(message.text, message.chat.id)
        con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
        query_incarico2= '''select us.matricola_cf, vc.id, vc.nome_squadra, viilu.id, vii.id_lavorazione, vii.id_profilo, vii.id_segnalazione
            from users.utenti_sistema us 
            left join users.v_componenti_squadre vc on us.matricola_cf = vc.matricola_cf 
            left join segnalazioni.v_incarichi_interni_last_update viilu on vc.id::text = viilu.id_squadra::text
            left join segnalazioni.v_incarichi_interni vii on viilu.id = vii.id
            where us.telegram_id = '{}' and vc.data_end is null and viilu.id_stato_incarico =2
            group by us.matricola_cf, vc.id, vc.nome_squadra, viilu.id, vii.id_lavorazione, vii.id_profilo, vii.id_segnalazione'''.format(message.chat.id)
        incarico_assegnato2 = esegui_query(con,query_incarico2,'s')
        print(message.chat.id, incarico_assegnato2)
        query_note= "UPDATE segnalazioni.t_incarichi_interni SET note_ente='{}', time_stop=now() WHERE id={};".format(message.text, incarico_assegnato2[0][3])
        update_motivo = esegui_query(con,query_note,'u')
        query_stato_c = "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico) VALUES ({}, 3)".format(incarico_assegnato2[0][3])
        stato = esegui_query(con,query_stato_c,'i')
        #print('id={}'.format(incarico_assegnato2[0][1]))
        query_squadra_c = "UPDATE users.t_squadre SET id_stato=2 WHERE id={}".format(incarico_assegnato2[0][1])
        squadra = esegui_query(con,query_squadra_c,'u')
        query_storico_c = '''INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) 
            VALUES ({0}, ' Incarico interno {1} chiuso dalla seguente squadra: {2} con il seguente messaggio: <br><i>{3}</i><br> - <a class="btn btn-info" href="dettagli_incarico_interno.php?id={1}"> Visualizza dettagli </a>');'''.format(incarico_assegnato2[0][4], incarico_assegnato2[0][3], incarico_assegnato2[0][2], message.text)
        storico = esegui_query(con,query_storico_c,'i')
        query_log_c= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','{}', 'Incarico interno {} chiuso');".format(incarico_assegnato2[0][0], incarico_assegnato2[0][3])
        log = esegui_query(con,query_log_c,'i')
        #txt_notifica = '{} L\'incarico interno assegnato alla squadra {} sulla segnalazione {} è stato chiuso con le seguenti note: {}'.format(emoji.emojize(":white_check_mark:",use_aliases=True), incarico_assegnato2[0][2], incarico_assegnato2[0][6], message.text)
        ''' if incarico_assegnato2[0][5] == 3:
            query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';"
        else:
            query_notifica = "select telegram_id from users.utenti_sistema where id_profilo <= {} and telegram_id !='' and telegram_attivo='t';".format(incarico_assegnato2[0][5])
            notifica =  esegui_query(con,query_notifica,'s')
            print(notifica)
            for tid in notifica:
                print(tid[0])
                await bot.send_message(tid[0], '{} L\'incarico interno assegnato alla squadra {} sulla segnalazione {} è stato chiuso con le seguenti note: {}'.format(emoji.emojize(":white_check_mark:",use_aliases=True), incarico_assegnato2[0][2], incarico_assegnato2[0][6], message.text)) '''
            #asyncio.run(invia_notifica(incarico_assegnato2[0][3], txt_notifica))
        #notifica che l'incarico è stato rifiutato? a chi?
    await state.finish () 

# chiusura incarico interno      
@dp.message_handler(commands='chiudo')
async def send_chiudo(message: types.Message):
    """
    This handler will be called when user sends `/chiudo` command
    """
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')

    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(registered_user) !=0:
        query_incarico= '''select * from users.v_componenti_squadre vcs 
        left join segnalazioni.v_incarichi_interni_last_update viilu on vcs.id::text = viilu.id_squadra::text 
        where vcs.matricola_cf = '{}' and vcs.data_end is null and viilu.id_stato_incarico =2'''.format(registered_user[0][0])
        incarico_assegnato = esegui_query(con,query_incarico,'s')
        if incarico_assegnato == 1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(incarico_assegnato) !=0:
            await Form.chiudo.set()
            await message.reply("Ciao {} stai chiudendo l'incarico. Per favore fornisci una nota di chiusura digitando un breve testo.".format(message.from_user.first_name))
        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano incarichi assegnati alla tua squadra'''.format(emoji.emojize(":warning:",use_aliases=True)))
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                               \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))

##### FINE BOT INCARICHI INTERNI #####

##### INIZIO BOT PRESIDI #####

# Check orario inizio incarico interno è numerico
@dp.message_handler(lambda message: not message.text.isdigit(), state=Form.orarioPresidio)
async def process_orario_invalid(message: types.Message):
    return await message.reply("I minuti inseriti non sono validi, devi inserire un numero")

# gestione orario inizio presidio e scrittura sul DB  
@dp.message_handler(lambda message: message.text.isdigit(), state= Form.orarioPresidio)
async def process_orario_presidio(message: types.Message, state: FSMContext):

    async with state.proxy() as data:
        data['orarioPresidio']= message.text
        inizio_incarico = datetime.now() + timedelta(minutes=int(data['orarioPresidio']))
        timepreview = inizio_incarico.replace(second=0, microsecond=0).time()
        await bot.send_message(
            message.chat.id,
            md.text(
                md.text('Hai indicato', md.bold(data['orarioPresidio']), 'minuti quindi l\'ora di inizio è', md.bold(timepreview), 'circa.'),
                md.text('Una volta terminato il presidio ricordati di chiuderlo digitando /stop.\n'),
            ),
        )
    print(timepreview)
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    query_presidio2= '''select us.matricola_cf, vc.id, vc.nome_squadra, vslu.id, vs.id_lavorazione, vs.id_profilo, vs.id_segnalazione
            from users.utenti_sistema us 
            left join users.v_componenti_squadre vc on us.matricola_cf = vc.matricola_cf 
            left join segnalazioni.v_sopralluoghi_last_update vslu on vc.id::text = vslu.id_squadra::text
            left join segnalazioni.v_sopralluoghi vs on vslu.id = vs.id
            where us.telegram_id = '{}' and vc.data_end is null and vslu.id_stato_sopralluogo=1'''.format(message.chat.id)
    presidio_assegnato2 = esegui_query(con,query_presidio2,'s')
    print(presidio_assegnato2)
    query_time= "UPDATE segnalazioni.t_sopralluoghi SET time_preview='{}' WHERE id={};".format(timepreview, presidio_assegnato2[0][3])
    time_inizio = esegui_query(con,query_time,'u')
    query_stato_presidio= "INSERT INTO segnalazioni.stato_sopralluoghi(id_sopralluogo, id_stato_sopralluogo, parziale) VALUES ({}, 2 , 'false');".format(presidio_assegnato2[0][3])
    stato_presidio = esegui_query(con,query_stato_presidio,'i')
    if (presidio_assegnato2[0][6] != ''):
        query_storico_presidio = '''INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento)
            VALUES ({0}, ' Sopralluogo {1} preso in carico dalla seguente squadra: {2} - <a class="btn btn-info" href="dettagli_sopralluogo.php?id={1}"> Visualizza dettagli </a>');'''.format(presidio_assegnato2[0][4], presidio_assegnato2[0][3], presidio_assegnato2[0][2])
        storico_presidio = esegui_query(con,query_storico_presidio,'i')
    query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','{}', 'Presidio (o sopralluogo) {} preso in carico');".format(presidio_assegnato2[0][0], presidio_assegnato2[0][3])
    log = esegui_query(con,query_log,'i')
    #await message.reply("Hai indicato {} minuti quindi l'ora di inizio è {} circa.".format(data['orarioPresidio'], timepreview))
    await state.finish ()

# accettazione presidio   
@dp.message_handler(commands=['presidio'])
async def send_accetto(message: types.Message):
    """
    This handler will be called when user sends `/presidio` command
    """
    
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')
    #print(registered_user[0][0])
    
    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(registered_user) !=0:
        query_presidio= '''select * from users.v_componenti_squadre vcs 
        left join segnalazioni.v_sopralluoghi_last_update vslu on vcs.id::text = vslu.id_squadra::text 
        where vcs.matricola_cf = '{}' and vcs.data_end is null and vslu.id_stato_sopralluogo =1;'''.format(registered_user[0][0])
        presidio_assegnato = esegui_query(con,query_presidio,'s')
        #id_squadra=incarico_assegnato[0][0]
        #print(id_squadra)
        #id_incarico=incarico_assegnato[0][14]
        if presidio_assegnato == 1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                            \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(presidio_assegnato) !=0:
            await Form.orarioPresidio.set()
            await message.reply("Ciao {} hai accettato il presidio {}. Tra quanti minuti sarai sul posto?".format(message.from_user.first_name, emoji.emojize(":thumbs_up:",use_aliases=True)))

        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano incarichi assegnati alla tua squadra'''.format(emoji.emojize(":warning:",use_aliases=True)))
        #await bot.delete_message(message.chat.id,message.message_id)
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                            \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))

# chiusura presidio scrittura su DB
@dp.message_handler(state= Form.stop)
async def process_chiudo_note(message: types.Message, state: FSMContext):

    async with state.proxy() as data:
        data['stop']= message.text
        await bot.send_message(
            message.chat.id,
            md.text(
                md.text('Hai fornito queste note: ', md.bold(data['stop']), '.'),
            ),
        )
        print(message.text, message.chat.id)
        con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
        query_presidio2= '''select us.matricola_cf, vc.id, vc.nome_squadra, vslu.id, vs.id_lavorazione
            from users.utenti_sistema us 
            left join users.v_componenti_squadre vc on us.matricola_cf = vc.matricola_cf 
            left join segnalazioni.v_sopralluoghi_last_update vslu on vc.id::text = vslu.id_squadra::text
            left join segnalazioni.v_sopralluoghi vs on vslu.id = vs.id
            where us.telegram_id = '{}' and vc.data_end is null and vslu.id_stato_sopralluogo =2
            group by us.matricola_cf, vc.id, vc.nome_squadra, vslu.id, vs.id_lavorazione'''.format(message.chat.id)
        presidio_assegnato2 = esegui_query(con,query_presidio2,'s')
        print(message.chat.id, presidio_assegnato2)
        query_note= "UPDATE segnalazioni.t_sopralluoghi SET note_ente='{}', time_stop=now() WHERE id={};".format(message.text, presidio_assegnato2[0][3])
        update_motivo = esegui_query(con,query_note,'u')
        query_stato_cp = "INSERT INTO segnalazioni.stato_sopralluoghi(id_sopralluogo, id_stato_sopralluogo) VALUES ({}, 3)".format(presidio_assegnato2[0][3])
        stato = esegui_query(con,query_stato_cp,'i')
        #print('id={}'.format(incarico_assegnato2[0][1]))
        query_squadra_cp = "UPDATE users.t_squadre SET id_stato=2 WHERE id={}".format(presidio_assegnato2[0][1])
        squadra = esegui_query(con,query_squadra_cp,'u')
        query_storico_cp = '''INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) 
            VALUES ({0}, ' Presidio {1} chiuso dalla seguente squadra: {2} con il seguente messaggio: <br><i>{3}</i><br> - <a class="btn btn-info" href="dettagli_sopralluogo.php?id={1}"> Visualizza dettagli </a>');'''.format(presidio_assegnato2[0][4], presidio_assegnato2[0][3], presidio_assegnato2[0][2], message.text)
        storico = esegui_query(con,query_storico_cp,'i')
        query_log_cp= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('sopralluoghi','{}', 'Presidio (o sopralluogo) {} chiuso');".format(presidio_assegnato2[0][0], presidio_assegnato2[0][3])
        log = esegui_query(con,query_log_cp,'i')
        #notifica che l'incarico è stato rifiutato? a chi?
    await state.finish () 

# chiusura presidio      
@dp.message_handler(commands='stop')
async def send_chiudo(message: types.Message):
    """
    This handler will be called when user sends `/stop` command
    """
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)   
    query_telegram_id= "select * from users.v_utenti_sistema where telegram_id ='{}'".format(message.chat.id)
    
    registered_user = esegui_query(con,query_telegram_id,'s')

    if registered_user ==1:
        await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
    elif len(registered_user) !=0:
        query_presidio= '''select * from users.v_componenti_squadre vcs 
        left join segnalazioni.v_sopralluoghi_last_update vslu on vcs.id::text = vslu.id_squadra::text 
        where vcs.matricola_cf = '{}' and vcs.data_end is null and vslu.id_stato_sopralluogo=2'''.format(registered_user[0][0])
        presidio_assegnato = esegui_query(con,query_presidio,'s')
        if presidio_assegnato == 1:
            await bot.send_message(message.chat.id,'''{} Si è verificato un problema, e la registrazione non è anadata a buon fine:
                               \nSe visualizzi questo messaggio prova a contattare un tecnico'''.format(emoji.emojize(":warning:",use_aliases=True)))
        elif len(presidio_assegnato) !=0:
            await Form.stop.set()
            await message.reply("Ciao {} stai chiudendo il presidio. Per favore fornisci una nota di chiusura digitando un breve testo.".format(message.from_user.first_name))
        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano presidi assegnati alla tua squadra'''.format(emoji.emojize(":warning:",use_aliases=True)))
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                               \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))


##### FINE BOT PRESIDI #####

#questa funzione deve essere l'ultima dello script altrimenti entra qui dentro e ignora le funzioni successive
@dp.message_handler(content_types=types.ContentTypes.ANY)
async def bot_echo_all(message: types.Message):
    await message.reply('Hai questo messaggio senza utilizzare nessun comando. Il contenuto di questo messaggio pertanto verrà ignorato dal sistema.')

if __name__ == '__main__':
    executor.start_polling(dp, skip_updates=True)
