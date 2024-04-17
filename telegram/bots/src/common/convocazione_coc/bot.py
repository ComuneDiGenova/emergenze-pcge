#! /usr/bin/env python
# -*- coding: utf-8 -*-
#   Gter Copyleft 2021

from . import settings
import logging
import os
from aiogram import Bot, Dispatcher, executor, types
from aiogram.types import callback_query, message, message_entity, update
from aiogram.types import message
import psycopg2


connection = settings.conn

API_TOKEN = settings.BOT_TOKEN


# Configure logging
logfile = f'/home/{os.getenv("ENVUSER")}/log/bot_convocazione_coc.log'

# if os.path.exists(logfile):
#     os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.INFO)


def esegui_query(query, query_type, connection=connection):
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
    
    if isinstance(query_type, str) == False:
        logging.warning('query type must be a str. The query {} was not executed'.format(query))
        return 1
    elif query_type != 'i' and query_type != 'u' and query_type != 's':
        logging.warning('query type non recgnized for query: {}. The query was not executed'.format(query))
        return 1
    
    conn = psycopg2.connect(host=connection.ip, dbname=connection.db, user=connection.user, password=connection.pwd, port=connection.port)
    cur = conn.cursor()
    conn.autocommit = True
    try:
        cur.execute(query)
    except Exception as e:
        logging.error(f'Query non eseguita per il seguente motivo: {e}')
        # logging.warning(query)
        return 1
    if query_type=='s':
        result= cur.fetchall() 
        cur.close()
        conn.close()
        return result
    else:
        cur.close()
        conn.close()
        return 0


# Initialize bot and dispatcher
bot = Bot(token=API_TOKEN)
dp = Dispatcher(bot)


@dp.message_handler(commands='start')
async def start_cmd_handler(message: types.Message):
   
    await message.reply("Ciao!\nBenvenuto nel BOT di convocazione del COC Direttivo")


# comando per ottenere telegram ID
@dp.message_handler(commands=['telegram_id'])
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/telegram_id` command
    """
    await message.reply(f"Ciao {message.from_user.first_name}, il tuo codice (telegram id) è {message.chat.id}")


@dp.callback_query_handler(text='ricevuto')
async def inline_kb_answer_callback_handler(query: types.CallbackQuery):
    answer_data = query.data
    
    # always answer callback queries, even if you have nothing to say
    #await query.answer(f'You answered with {answer_data!r}')

    if answer_data == 'ricevuto':
        tg_id = query.from_user.id
        query_convocazione=f"""SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
                                                u.nome,
                                                u.cognome,
                                                u.telegram_id,
                                                tp.id,
                                                tp.data_invio,
                                                tp.lettura,
                                                tp.data_conferma
                                FROM users.utenti_coc u
                                JOIN users.t_convocazione tp 
                                    ON u.telegram_id::text = tp.id_telegram::text
                                WHERE tp.id_telegram = '{tg_id}'
                                ORDER BY u.telegram_id, tp.data_invio DESC, tp.data_invio_conv DESC;"""
        
        result_s=esegui_query(query_convocazione, 's')

        # logging.debug(result_s)
        # logging.info(query_convocazione)
        
        #if len(result_s) !=0:
        id = result_s[0][4]
        query_conferma=f"UPDATE users.t_convocazione SET lettura=true, data_conferma=now() WHERE id_telegram ='{tg_id}' and id = {id}"
        result_c=esegui_query(query_conferma, 'u')
        if result_c == 1:
            text="Si è verificato un problema nell'invio della conferma di lettura."
        else:
            name = result_s[0][1]
            data = result_s[0][5]
            text=f"Gentile {name}, hai dato conferma di lettura dell'emanazione dell'allerta emanata in data {data}."
            await bot.delete_message(tg_id, query.message.message_id)
    else:
        text = f'Unexpected callback data {answer_data!r}!'

    await bot.send_message(tg_id, text)
    
@dp.callback_query_handler(text='convocazione')
async def inline_kb_answer_callback_handler(query: types.CallbackQuery):
    answer_data = query.data

    # always answer callback queries, even if you have nothing to say
    #await query.answer(f'You answered with {answer_data!r}')

    if answer_data == 'convocazione':
        testo = query.message.text
        tg_id = query.from_user.id

        query_convocazione2=f"""SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
                                                    u.nome,
                                                    u.cognome,
                                                    u.telegram_id,
                                                    tp.id,
                                                    tp.data_invio,
                                                    tp.lettura,
                                                    tp.data_conferma,
                                                    tp.data_invio_conv,
                                                    tp.data_conferma_conv,
                                                    tp.lettura_conv 
                                FROM users.utenti_coc u
                                JOIN users.t_convocazione tp 
                                    ON u.telegram_id::text = tp.id_telegram::text
                                WHERE tp.id_telegram = '{tg_id}' AND tp.data_invio_conv IS NOT null
                                ORDER BY u.telegram_id, tp.data_invio_conv DESC, tp.data_invio_conv DESC;"""
        
        result_s2=esegui_query(query_convocazione2, 's')

        # logging.debug(result_s2)

        # if len(result_s2) != 0:
        user_id = result_s2[0][4]
        query_conferma2=f"UPDATE users.t_convocazione SET lettura_conv=true, data_conferma_conv=now() WHERE id_telegram ='{tg_id}' and id = {user_id}"
        result_c2 = esegui_query(query_conferma2, 'u')
        
        if result_c2 == 1:
            text="Si è verificato un problema nell'invio della conferma di lettura."
        else:
            text=f"Gentile {query.from_user.first_name} hai dato conferma di lettura della Concovocazione del COC Direttivo\n\n{testo}"
            await bot.delete_message(tg_id, query.message.message_id)
    else:
        text = f'Unexpected callback data {answer_data!r}!'

    await bot.send_message(tg_id, text)


if __name__ == '__main__':
    # logging.warning(f"ip={connection.ip}, dbname={connection.db}, user={connection.user}, password={connection.pwd}, port={connection.port}")
    executor.start_polling(dp, skip_updates=True)
