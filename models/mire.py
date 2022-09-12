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

    Field('lettura_1h', 'double', rname='lettura1'),
    Field('last_update_1h', 'datetime', rname='lettura1_update'),
    Field('livello_1h', rname='livello_allerta_1'),
    Field('lettura_2h', 'double', rname='lettura2'),
    Field('last_update_2h', 'datetime', rname='lettura2_update'),
    Field('livello_2h', rname='livello_allerta_2'),
    Field('lettura_3h', 'double', rname='lettura3'),
    Field('last_update_3h', 'datetime', rname='lettura3_update'),
    Field('livello_3h', rname='livello_allerta_3'),
    Field('lettura_4h', 'double', rname='lettura4'),
    Field('last_update_4h', 'datetime', rname='lettura4_update'),
    Field('livello_4h', rname='livello_allerta_4'),
    Field('lettura_5h', 'double', rname='lettura5'),
    Field('last_update_5h', 'datetime', rname='lettura5_update'),
    Field('livello_5h', rname='livello_allerta_5'),
    Field('lettura_6h', 'double', rname='lettura6'),
    Field('last_update_6h', 'datetime', rname='lettura6_update'),
    Field('livello_6h', rname='livello_allerta_6'),
    primarykey = ['id',],
    migrate = False,
    rname = f'{SCHEMA}.v_mire_pioggia'
)
