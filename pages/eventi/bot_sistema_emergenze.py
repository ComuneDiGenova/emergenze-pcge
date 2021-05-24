#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Lorenzo Benvenuto copyleft 2021


import logging

from aiogram.types import callback_query, message
from aiogram.types.reply_keyboard import ReplyKeyboardRemove
import credenziali
from aiogram import Bot, Dispatcher, executor, types
from datetime import datetime
import sqlite3

import emoji


db_name='./sistema'
table_name='presenze_operatori'
conn=sqlite3.connect(db_name)




API_TOKEN = '1829248321:AAFUbfI7evTquQkOCbZ2YuIEHtTH8w7DHUY'

# Configure logging
logging.basicConfig(level=logging.INFO)

# Initialize bot and dispatcher
bot = Bot(token=API_TOKEN)
dp = Dispatcher(bot)


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
    if callback_query.data=='2':
        
        testo='Hai inserito {} ore e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='4':
        testo='Hai inserito {} ore e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='6':
        testo='Hai inserito {} ore e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='8':
        testo='Hai inserito {} ore e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)

    elif callback_query.data=='basso':
        testo='Hai inserito {} e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='medio':
        testo='Hai inserito {} e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)
    elif callback_query.data=='alto':
        testo='Hai inserito {} e il tuo chat id è {}'.format(callback_query.data,callback_query.from_user.id)
        await bot.send_message (callback_query.from_user.id, text= testo)


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
    selquery='''select * from presenze_operatori where operatore=\'{}\''''.format(message.chat.id)

    conn=sqlite3.connect(db_name)

    c=conn.cursor()
    try:
        c.execute(selquery)
    except Exception as e:
        print(e)
    
    result=c.fetchall()
    print(result)
    if len(result)==0:
        await message.reply("Ciao {}, il tuo utente non riuslta registrato nel sistema.\nPer favore contatta l'amministratore di sistema e comunicagli il tuo chat id che è {}".format(message.from_user.first_name,message.chat.id))
    elif result[0][1]==0:
        await message.reply("Ciao {}, attualmente risulti già operativo. Se hai finito il tuo turno usa il comando /registra_uscita".format(message.from_user.first_name))
    elif result[0][1]==1:
        now = datetime.now()
        query="UPDATE presenze_operatori SET Operativo = 0 WHERE Operatore ='{}'".format(message.chat.id)
        c=conn.cursor()
        try:
            c.execute(query)
            await bot.send_message(
                chat_id=message.from_user.id,
                text="Ciao {}, quante ore prevedi di rimanere operativo?".format(message.from_user.first_name),
                reply_markup= keyboard ([
                                        ["2", "2 ore", "message text", None],
                                        ["4", "4 ore", "message text", None],
                                        ["6", "6 ore", "message text", None],
                                        ["8", "8 ore", "message text", None]
                                        ])
                                    )
            mess_id=message.message_id
            print(mess_id)
            #await message.reply("Gentile {} hai registrato la tua presenza il {}-{}-{}  alle ore {}".format(message.from_user.first_name,now.strftime("%d"),now.strftime("%m"),now.strftime("%Y"),now.strftime("%H:%M")))
        except Exception as e:
            print(e)
            await message.reply("{} la tua registrazione non è andata a buon fine".format(message.from_user.first_name))
            



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
    mess_id=message.message_id
    #print(mess_id)
    #await message.reply("Gentile {} hai registrato la tua presenza il {}-{}-{}  alle ore {}".format(message.from_user.first_name,now.strftime("%d"),now.strftime("%m"),now.strftime("%Y"),now.strftime("%H:%M")))



@dp.message_handler()
async def echo(message: types.Message):
    # old style:
    # await bot.send_message(message.chat.id, message.text)
    #print(message.text, message.chat.id)
    #await message.answer(message.text)
    await bot.send_message(message.chat.id, 'hai inserito testo senza schiacciare nessun comando. In particolare hai scritto \'{}\''.format(message.text))

if __name__ == '__main__':
    executor.start_polling(dp, skip_updates=True)