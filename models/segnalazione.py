# -*- coding: utf-8 -*-

from .segnalazione_decodifica import SCHEMA, db, Field

# from pydal.validators import *

db.define_table('segnalazione',
    Field('inizio', 'datetime', rname='data_ora'),
    Field('segnalante_id', required=True, notnull=True, rname='id_segnalante'),
    Field('descrizione', required=True, notnull=True),
    Field('id_criticita', 'integer', required=True, notnull=True),
    Field('rischio', 'boolean'),
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('civico_id', 'reference civico', rname='id_civico'),
    Field('geom', 'geometry()', required=True, notnull=True),
    Field('municipio_id', 'reference municipio', required=True, notnull=True, rname='id_municipio'),
    Field('operatore', required=True, notnull=True, rname='id_operatore'),
    Field('note'),
    Field('uo_ins'),
    Field('nverde', 'boolean', default=False),
    rname=f'{SCHEMA}.t_segnalazioni'
)

db.define_table('segnalante',
    Field('tipo_segnalante_id', 'reference tipo_segnalante', rname='id_tipo_segnalante'),
    Field('altro_tipo'),
    Field('nome_cognome'),
    Field('telefono'),
    Field('note'),
    rname = f'{SCHEMA}.t_segnalanti'
)

db.define_table('join_oggetto_richio',
    Field('segnalazione_id', 'reference segnalazione', rname='id_segnalazione'),
    Field('tipo_oggetto_id', 'reference tipo_oggetto_rischio', rname='id_tipo_oggetto'),
    Field('oggetto_id', 'integer', rname='id_oggetto'),
    Field('attivo', 'boolean', notnull=True, default=True),
    Field('aggiornamento', 'datetime', notnull=True),
    rname = f'{SCHEMA}.join_oggetto_rischio'
)

db.define_table('segnalazione_riservata',
    Field('segnalazione_id', rname='id_segnalazione'),
    Field('mittente'),
    Field('testo'),
    Field('timeref', rname='data_ora_stato'),
    Field('allegato'),
    primarykey=['segnalazione_id', 'timeref'],
    rname = f'{SCHEMA}.t_comunicazioni_segnalazioni_riservate'
)
