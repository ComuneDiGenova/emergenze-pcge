import os
from pathlib import Path

# logger settings
# This should be in a separate settings file

LOGGERS = [
    "debug:stdout"
]  # syntax "severity:filename" filename can be stderr or stdout

BOT_PROPS = {
    "emergenze": {
        "app_name": "emergenze-bot",
        "bot_token": os.environ.get('EMERGENZE_BOT_TOKEN')
    },
    "emergenze_coc": {
        "app_name": "emergenze-coc-bot",
        "bot_token": os.environ.get('EMERGENZE_COC_BOT_TOKEN')
    }
}