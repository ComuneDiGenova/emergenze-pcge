# -*- coding: utf-8 -*-

from .tools import new_id
from .segnalazione_decodifica import SCHEMA, db, Field

# from pydal.validators import *

db.define_table('segnalazione',
    Field('id', 'id', compute=lambda _: new_id(db['segnalazione'])),
    Field('inizio', 'datetime', rname='data_ora'),
    Field('segnalante_id', required=True, notnull=True, rname='id_segnalante'),
    Field('descrizione', required=True, notnull=True),
    Field('criticita_id', 'integer', required=True, notnull=True, rname='id_criticita'),
    Field('rischio', 'boolean', label='Segnalazione presenza persone a rischio'),
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('civico_id', 'reference civico', rname='id_civico'),
    Field('geom', 'geometry()', required=True, notnull=True),
    Field('municipio_id', 'reference municipio', required=True, notnull=True, rname='id_municipio'),
    Field('operatore', label='Identificativo operatore',
        comment = 'fornire il numero di matricola o il codice fiscale',
        required=True, notnull=True, rname='id_operatore'
    ),
    Field('note', label='Note utili alla localizzazione'),
    Field('uo_ins'),
    Field('nverde', 'boolean', label='Richiesta attivazione numero verde', default=False),
    rname=f'{SCHEMA}.t_segnalazioni'
)

db.define_table('segnalante',
    Field('id', 'id', compute=lambda _: new_id(db['segnalante'])),
    Field('tipo_segnalante_id', 'reference tipo_segnalante', rname='id_tipo_segnalante'),
    Field('altro_tipo'),
    Field('nome', label='Nome segnalante',
        comment='Fornire nome e cognome del segnalante', rname='nome_cognome'
    ),
    Field('telefono', length=20,
        label='Telefono segnalante',
        comment='Fornire il telefono del segnalante'
    ),
    Field('note', label='Note', comment='Note del segnalante'),
    rname = f'{SCHEMA}.t_segnalanti'
)

db.define_table('join_oggetto_rischio',
    Field('segnalazione_id', 'reference segnalazione', rname='id_segnalazione'),
    Field('tipo_oggetto_id', 'reference tipo_oggetto_rischio', rname='id_tipo_oggetto'),
    Field('oggetto_id', 'integer', rname='id_oggetto'),
    Field('attivo', 'boolean', notnull=True, default=True),
    Field('aggiornamento', 'datetime', notnull=True),
    primarykey = ['segnalazione_id', 'tipo_oggetto_id', 'oggetto_id', 'attivo', 'aggiornamento'],
    rname = f'{SCHEMA}.join_oggetto_rischio'
)

db.define_table('segnalazione_riservata',
    Field('segnalazione_id', rname='id_segnalazione'),
    Field('mittente', label='Mittente'),
    Field('testo', label='Comunicazione riservata'),
    Field('timeref', rname='data_ora_stato'),
    Field('allegato'),
    primarykey=['segnalazione_id', 'timeref'],
    rname = f'{SCHEMA}.t_comunicazioni_segnalazioni_riservate'
)

db.define_table('segnalazione_lavorazione',
    Field('id', 'id', compute=lambda _: new_id(db['segnalazione_lavorazione'])),
    Field('in_lavorazione', 'boolean', notnull=True, default=True),
    Field('profilo_id', 'reference profilo_utilizatore', rname='id_profilo'),
    Field('invio_manutenzioni', 'boolean'),
    Field('geom', 'geometry()'),
    Field('descrizione_chiusura'),
    Field('id_man', 'integer'),
    rname=f'{SCHEMA}.t_segnalazioni_in_lavorazione'
)

db.define_table('join_segnalazione_lavorazione',
    Field('lavorazione_id', 'reference segnalazione_lavorazione', rname='id_segnalazione_in_lavorazione'),
    Field('segnalazione_id', 'reference segnalazione', rname='id_segnalazione'),
    Field('sospeso', 'boolean', notnull=True, default=False),
    primarykey = ['segnalazione_id'],
    rname = f'{SCHEMA}.join_segnalazioni_in_lavorazione'
)

db.define_table('storico_segnalazione_lavorazione',
    Field('lavorazione_id', 'reference segnalazione_lavorazione', rname='id_segnalazione_in_lavorazione'),
    Field('aggiornamento', rname='log_aggiornamento'),
    Field('timeref', 'datetime', rname='data_ora'),
    primarykey = ['lavorazione_id', 'timeref'], # id_segnalazione_in_lavorazione, data_ora
    rname = f'{SCHEMA}.t_storico_segnalazioni_in_lavorazione'
)
