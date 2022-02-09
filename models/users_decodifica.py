# -*- coding: utf-8 -*-

from ..common import db, Field

SCHEMA = 'users'

db.define_table('tipo_stato_squadra',
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean'),
    rname = '{SCHEMA}.tipo_stato_squadre'
)
