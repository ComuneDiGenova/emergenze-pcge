## Configuration

just create a file called `settings_private.py` with the following content replacing
the placeholders with the right values:

```py
# -*- coding: utf-8 -*-

from pydal._compat import urllib_quote

UPLOAD_FOLDER = None

DB_PASSWORD = urllib_quote('<password>')
DB_URI = f"postgres://<username>:{DB_PASSWORD}@<hostname or IP>/<db name>"
DB_POOL_SIZE = 10
DB_MIGRATE = False
DB_DECODE_CREDENTIALS = True # Just in case password contains strange characters

# logger settings
LOGGERS = [
    "debug:stdout"
]  # syntax "severity:filename" filename can be stderr or stdout

```
