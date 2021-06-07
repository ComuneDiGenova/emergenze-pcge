#!/usr/bin/env python3
# -*- coding: utf-8 -*-

# Lorenzo Benvenuto
# Gter copyleft 2021

import logging
import requests
import os

import psycopg2
import emoji
import config
import time

import aiogram.utils.markdown as md
from aiogram.types import callback_query, message
from aiogram.types.reply_keyboard import ReplyKeyboardRemove
from aiogram.dispatcher import FSMContext
from aiogram.dispatcher.filters.state import State, StatesGroup
import conn
from aiogram import Bot, Dispatcher, executor, types
from aiogram.contrib.fsm_storage.memory import MemoryStorage
from datetime import datetime, timedelta



# Configure logging
logfile='{}/notifica_chiusura_turno_bot.log'.format(os.path.dirname(os.path.realpath(__file__)))
if os.path.exists(logfile):
    os.remove(logfile)

logging.basicConfig(format='%(asctime)s\t%(levelname)s\t%(message)s',filename=logfile,level=logging.ERROR)

API_TOKEN = config.TOKEN
bot = Bot(token=API_TOKEN)
dp = Dispatcher(bot)

def telegram_bot_sendtext(bot_message,chat_id):
    
    send_text = 'https://api.telegram.org/bot' + API_TOKEN + '/sendMessage?chat_id=' + chat_id + '&parse_mode=Markdown&text=' + bot_message

    response = requests.get(send_text)

    return response.json()





#telegram_bot_sendtext(testo,'306530623')
@ dp.message_handler (content_types= ['text'])
async def send_message ():
    await bot.send_message (chat_id= '306530623', text= 'Hello!')
send_message()