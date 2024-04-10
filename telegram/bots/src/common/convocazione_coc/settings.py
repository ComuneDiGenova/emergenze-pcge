import os
from dotenv import load_dotenv
from .. import settings as _settings

load_dotenv()
LOGGERS = _settings.LOGGERS

APP_NAME = _settings.BOT_PROPS['emergenze_coc']['app_name']
BOT_TOKEN = _settings.BOT_PROPS['emergenze_coc']['bot_token']

class conn(object):
    ip = os.environ.get('conn_ip')
    db = os.environ.get('conn_db')
    user = os.environ.get('conn_user')
    pwd = os.environ.get('conn_pwd')
    port = os.environ.get('conn_port')