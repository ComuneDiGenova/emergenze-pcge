#! /usr/bin/env python
# -*- coding: utf-8 -*-
#   Gter Copyleft 2021

import logging
import os
from aiogram import Bot, Dispatcher, executor, types
from aiogram.types import callback_query, message, message_entity, update
from aiogram.types import message
import psycopg2
import config
import conn


TOKENCOC=config.TOKEN_COC

# Configure logging
logfile='{}/bot_convocazione_coc.log'.format(os.path.dirname(os.path.realpath(__file__)))
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


# Configure logging
logging.basicConfig(level=logging.INFO)

# Initialize bot and dispatcher
bot = Bot(token=TOKENCOC)
dp = Dispatcher(bot)


@dp.message_handler(commands='start')
async def start_cmd_handler(message: types.Message):
   

    await message.reply("Ciao!\nBenvenuto nel BOT di convocazione del COC Direttivo")


# comando per telegram ID
@dp.message_handler(commands=['telegram_id'])
async def send_welcome(message: types.Message):
    """
    This handler will be called when user sends `/telegram_id` command
    """
    await message.reply("Ciao {}, il tuo codice (telegram id) è {}".format(message.from_user.first_name,message.chat.id))

@dp.callback_query_handler(text='ricevuto')
async def inline_kb_answer_callback_handler(query: types.CallbackQuery):
    answer_data = query.data
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    # always answer callback queries, even if you have nothing to say
    #await query.answer(f'You answered with {answer_data!r}')

    if answer_data == 'ricevuto':
        query_convocazione='''SELECT distinct on (u.telegram_id) u.matricola_cf,
        u.nome,
        u.cognome,
        u.telegram_id,
        tp.id,
        tp.data_invio,
        tp.lettura,
        tp.data_conferma
        FROM users.utenti_coc u
        right JOIN users.t_convocazione tp ON u.telegram_id::text = tp.id_telegram::text
        where tp.id_telegram = '{}'
        order by u.telegram_id, tp.data_invio desc;'''.format(query.from_user.id)
        result_s=esegui_query(con,query_convocazione,'s')

        #print(result_s)
        
        #if len(result_s) !=0:
        query_conferma="UPDATE users.t_convocazione SET lettura=true, data_conferma=now() WHERE id_telegram ='{}' and id ={}".format(query.from_user.id, result_s[0][4])
        result_c=esegui_query(con,query_conferma,'u')
        if result_c == 1:
            text='Si è verificato un problema nell\'invio della conferma di lettura.'
        else:
            text='Gentile {}, hai dato conferma di lettura dell\'emanazione dell\'allerta emanata in data {}.'.format(result_s[0][1], result_s[0][5])
            await bot.delete_message(query.from_user.id,query.message.message_id)
    else:
        text = f'Unexpected callback data {answer_data!r}!'

    await bot.send_message(query.from_user.id, text)
    
@dp.callback_query_handler(text='convocazione')
async def inline_kb_answer_callback_handler(query: types.CallbackQuery):
    answer_data = query.data
    con = psycopg2.connect(host=conn.ip, dbname=conn.db, user=conn.user, password=conn.pwd, port=conn.port)
    # always answer callback queries, even if you have nothing to say
    #await query.answer(f'You answered with {answer_data!r}')

    if answer_data == 'convocazione':
        testo=query.message.text
        query_convocazione2='''SELECT distinct on (u.telegram_id) u.matricola_cf,
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
        right JOIN users.t_convocazione tp ON u.telegram_id::text = tp.id_telegram::text
        where tp.id_telegram = '{}' and tp.data_invio_conv is not null
        order by u.telegram_id, tp.data_invio desc;'''.format(query.from_user.id)
        result_s2=esegui_query(con,query_convocazione2,'s')

        # #print(result_s)
        
        if len(result_s2) !=0:
            query_conferma2="UPDATE users.t_convocazione SET lettura_conv=true, data_conferma_conv=now() WHERE id_telegram ='{}' and id ={}".format(query.from_user.id, result_s2[0][4])
            result_c2=esegui_query(con,query_conferma2,'u')
        if result_c2 == 1:
            text='Si è verificato un problema nell\'invio della conferma di lettura.'
        else:
            text='Gentile {} hai dato conferma di lettura della Concovocazione del COC Direttivo\n\n{}'.format(query.from_user.first_name, testo)
            await bot.delete_message(query.from_user.id,query.message.message_id)
    else:
        text = f'Unexpected callback data {answer_data!r}!'

    await bot.send_message(query.from_user.id, text)


if __name__ == '__main__':
    executor.start_polling(dp, skip_updates=True)