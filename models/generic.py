# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

# from geomet import wkt

SCHEMA = 'geodb'

db.define_table('municipio',
    Field('geom', 'geometry()'),
    Field('codice', rname='codice_mun'),
    Field('nome', rname='nome_munic'),
    rname = f'{SCHEMA}.municipi'
)

# db.municipio.geometry = Field.Virtual('geometry',
#     lambda row: wkt.loads(row['municipio'].geom.replace(" Z ", " ").replace(" M ", " ").replace(" ZM ", " "))
# )
