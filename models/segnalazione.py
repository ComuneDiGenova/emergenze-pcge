# -*- coding: utf-8 -*-

from .segnalazione_decodifica import SCHEMA, db, Field

from pydal.validators import *

db.define_table('segnalazione',
    Field('inizio', 'datetime', rname='data_ora'),
    Field('segnalante_id', )

    rname = f'{SCHEMA}.t_segnalazioni'
)
