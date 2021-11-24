# -*- coding: utf-8 -*-

from .segnalazione_decodifica import SCHEMA, db, Field

# from pydal.validators import *

db.define_table('segnalazione',
    Field('inizio', 'datetime', rname='data_ora'),
    Field('segnalante_id', required=True, notnull=True),
    Field('descrizione', required=True, notnull=True),
    Field('id_criticita', 'integer', required=True, notnull=True),
    Field('rischio', 'boolean'),
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('civico_id', 'reference civico', rname='id_civico'),
    Field('geom', 'geometry()', required=True, notnull=True),
    Field('municipio_id', 'reference municipio', required=True, notnull=True, rname='id_municipio'),
    Field('operatore', required=True, notnull=True, rnane='id_operatore'),
    Field('note'),
    Field('uo_ins'),
    Field('nverde', 'boolean', default=False, notnull=True),
    rname=f'{SCHEMA}.t_segnalazioni'
)



