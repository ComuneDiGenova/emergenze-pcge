# -*- coding: utf-8 -*-

from ..common import db, Field

SCHEMA = 'varie'

db.define_table('letture_mire',
    Field('id', 'text'),
    Field('nome'),
    Field('tipo'),
    Field('geom', 'geometry()'),
    Field('arancio', 'double', readable=False),
    Field('rosso', 'double', readable=False),
    Field('lettura', 'double', rname='lettura0'),
    Field('last_update', 'datetime', rname='lettura0_update'),
    Field('livello', rname='livello_allerta_0'),
    primarykey = ['id',],
    migrate = False,
    rname = f'{SCHEMA}.v_mire_pioggia'
)
