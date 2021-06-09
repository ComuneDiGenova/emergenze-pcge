#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Lorenzo Benvenuto, Roberta Fagandini
# copyleft 2021


import logging
import os
import aiogram.utils.markdown as md
from aiogram.types import callback_query, message
from aiogram.types.reply_keyboard import ReplyKeyboardRemove
from aiogram.dispatcher import FSMContext
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
    motivo = State() # Will be represented in storage as 'Form: name'
    orario= State() # Will be represented in storage as 'Form: age'
    tipopresa= State() # Will be represented in storage as 'Form: gender'


def keyboard (kb_config):
    _keyboard= types.InlineKeyboardMarkup ()
    for rows in kb_config:
        btn= types.InlineKeyboardButton (
            callback_data= rows [0],
            text= rows [1]
        )
        _keyboard.insert (btn)
    return _keyboard

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
        
        
    
    elif callback_query.data=='basso':
        testo='Hai inserito {} e il tuo chat id è {}, da qui bisogna continuare l\'implementazione del comando'.format(callback_query.data,callback_query.from_user.id)
        await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='medio':
        testo='Hai inserito {} e il tuo chat id è {}, da qui bisogna continuare l\'implementazione del comando'.format(callback_query.data,callback_query.from_user.id)
        await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='alto':
        testo='Hai inserito {} e il tuo chat id è {}, da qui bisogna continuare l\'implementazione del comando'.format(callback_query.data,callback_query.from_user.id)
        await bot.delete_message(callback_query.from_user.id,callback_query.message.message_id)
        await bot.send_message (callback_query.from_user.id, text= testo)
        
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
    # default row_width is 3, so here we can omit it actually
    # kept for clearness

    #text_and_data = (
    #    ('Yes!', 'yes'),
    #    ('No!', 'no'),
    #    ('Maybe!','maybe')
    #)
    # in real life for the callback_data the callback data factory should be used
    # here the raw string is used for the simplicity
    #row_btns = (types.InlineKeyboardButton(text, callback_data=data) for text, data in text_and_data)

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



@dp.message_handler(commands='registra_uscita')
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
        



@dp.message_handler(commands=['inserisci_mira'])
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/registra_presenza` command
    """

    await bot.send_message(
        chat_id=message.from_user.id,
        text="Come valuti il livello dell'acqua per il torrente in questione?",
        reply_markup= keyboard ([
                                ["basso", "Basso {}".format(emoji.emojize(":green_circle:",use_aliases=True)), "message text", None],
                                ["medio", "Medio {}".format(emoji.emojize(":yellow_circle:",use_aliases=True)), "message text", None],
                                ["alto", "Alto {}".format(emoji.emojize(":red_circle:",use_aliases=True)), "message text", None]
                                ])
                            )
    #elimino messaggio con comando per evitare tocchi maldestri
    #await bot.delete_message(message.chat.id,message.message_id)

# Check orario è numerico
@dp.message_handler(lambda message: not message.text.isdigit(), state=Form.orario)
async def process_orario_invalid(message: types.Message):
    return await message.reply("I minuti inseriti non sono validi, devi inserire un numero")

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

#funzione che controlla che schiaccino un bottone
@dp.message_handler(lambda message: message.text not in ["Regolare", "Parziale"], state=Form.tipopresa)
async def process_gender_invalid(message: types.Message):
    return await message.reply("Il valore inserito non è valido. Seleziona il valore dalla tastiera.")
    
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
                md.text('Una volta completato l\'incarico ricordati di chiuderlo digitando /chiudi.\n'),
            ),
            reply_markup=markup,
            parse_mode=ParseMode.MARKDOWN,
        )
    inizio_incarico = datetime.now() + timedelta(minutes=int(data['orario']))
    inizio_preview = inizio_incarico.replace(second=0, microsecond=0)
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    query_incarico2= '''select us.matricola_cf, vc.id, vc.nome_squadra, viilu.id, vii.id_lavorazione
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
    # Finish conversation
    await state.finish()
    
  
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


@dp.message_handler(state= Form.motivo)
async def process_motivo(message: types.Message, state: FSMContext):

    #Process user name

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
        query_incarico2= '''select us.matricola_cf, vc.id, vc.nome_squadra, viilu.id, vii.id_lavorazione
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
        #notifica che l'incarico è stato rifiutato? a chi?
    await state.finish () 
      
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
            #print(id_incarico)
            #queste due query vanno spostate nella funzione che gestisce il messaggio altrimenti poi non si riesce a recuperare l'id incarico perchè cambia lo stato
            ''' query_stato = "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico) VALUES ({}, 4)".format(id_incarico)
            stato = esegui_query(con,query_stato,'i')
            print('id={}'.format(id_squadra))
            query_squadra = "UPDATE users.t_squadre SET id_stato=2 WHERE id={}".format(id_squadra)
            squadra = esegui_query(con,query_squadra,'u') '''
        else:
            await bot.send_message(message.chat.id,'''{} Al momento non risultano incarichi assegnati alla tua squadra'''.format(emoji.emojize(":warning:",use_aliases=True)))
        #await bot.delete_message(message.chat.id,message.message_id)
    else:
        await bot.send_message(message.chat.id,'''{} Il tuo utente non è registrato nel sistema e pertanto non puoi usare questo comando.
                               \nContatta un amministratore di sistema per registrarti, e dopo esser stato abilitato ripeti questo comando'''.format(emoji.emojize(":no_entry_sign:",use_aliases=True)))

#questa funzione deve essere l'ultima dello script altrimenti entra qui dentro e ignora le funzioni successive
@dp.message_handler()
async def echo(message: types.Message):
    # old style:
    # await bot.send_message(message.chat.id, message.text)
    #print(message.text, message.chat.id)
    #await message.answer(message.text)
    await bot.send_message(message.chat.id, 'hai inserito testo senza schiacciare nessun comando. In particolare hai scritto \'{}\''.format(message.text))
    

if __name__ == '__main__':
    executor.start_polling(dp, skip_updates=True)
