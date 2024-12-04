# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

SCHEMA = 'eventi'

db.define_table('tipo_evento',
    Field('descrizione'),
    Field('valido', 'boolean', default=True, reuired=True, notnull=True),
    Field('notifiche', 'boolean', default=True, reuired=True, notnull=True),
    rname=f'{SCHEMA}.tipo_evento'
)

db.define_table('tipo_allerta',
    Field('descrizione', reuired=True, notnull=True),
    Field('valido', 'boolean', default=True, reuired=True, notnull=True),
    Field('colore', rname='rgb_hex'),
    rname=f'{SCHEMA}.tipo_allerta'
)

db.define_table('tipo_foc',
    Field('descrizione', reuired=True, notnull=True),
    Field('valido', 'boolean', default=True, reuired=True, notnull=True),
    Field('colore', rname='rgb_hex'),
    rname=f'{SCHEMA}.tipo_foc'
)
