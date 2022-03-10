# -*- coding: utf-8 -*-

from ..common import db, Field

SCHEMA = 'users'

# db.define_table('tipo_stato_squadra',
#     Field('descrizione', required=True, notnull=True),
#     Field('valido', 'boolean'),
#     rname = f'{SCHEMA}.tipo_stato_squadre'
# )

db.define_table('stato_squadra',
    Field('descrizione'),
    Field('valido', 'boolean', notnull=True, default=True),
    rname = f'{SCHEMA}.t_stato_squadre'
)

db.define_table('livello2',
    Field('id1', 'integer'),
    Field('id2', 'integer'),
    Field('descrizione'),
    Field('valido', 'boolean'),
    primarykey = ['id1', 'id2'],
    rname = f'{SCHEMA}.uo_2_livello'
)
