# -*- coding: utf-8 -*-

from .. import settings
from .tools import new_id, incarichi_new_id
from .segnalazione_decodifica import SCHEMA, db, Field
from datetime import datetime
from pytz import timezone

from pydal.validators import *

db.define_table('segnalazione',
    Field('id', 'id', default=lambda: new_id(db['segnalazione'])),
    Field('inizio', 'datetime',
        # default = lambda: datetime.now(timezone('Europe/Rome')).replace(tzinfo=None),
        rname='data_ora',
    ),
    Field('segnalante_id', 'integer', required=True, notnull=True, rname='id_segnalante'),
    Field('descrizione', required=True, notnull=True),
    Field('criticita_id', 'reference tipo_criticita', required=True, notnull=True,
        label = 'Id tipo di criticità',
        comment = 'Identificativo univoco del tipo di criticità segnalata',
        rname='id_criticita'
    ),
    Field('rischio', 'boolean', label='Segnalazione presenza persone a rischio'),
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('civico_id', 'reference civico', rname='id_civico'),
    Field('geom', 'geometry()', required=True, notnull=True),
    Field('municipio_id', 'reference municipio', required=True, notnull=True, rname='id_municipio'),
    Field('operatore', label='Identificativo operatore',
        comment = 'fornire il numero di matricola o il codice fiscale',
        required=True, notnull=True, requires=IS_NOT_EMPTY(),
        rname='id_operatore'
    ),
    Field('note', label='Note utili alla localizzazione'),
    Field('uo_ins'),
    Field('nverde', 'boolean', label='Richiesta attivazione numero verde', default=False),
    rname=f'{SCHEMA}.t_segnalazioni'
)

db.define_table('segnalante',
    Field('id', 'id', default=lambda: new_id(db['segnalante'])),
    Field('tipo_segnalante_id', 'reference tipo_segnalante', label = 'Tipo segnalante',
        required = True, requires = IS_IN_DB(
            db(db.tipo_segnalante),
            db.tipo_segnalante.id,
            label = db.tipo_segnalante.descrizione,
            orderby = db.tipo_segnalante.descrizione,
            zero = None
        ),
        rname='id_tipo_segnalante'
    ),
    Field('altro_tipo'),
    Field('nome', label='Nome segnalante',
        comment='Fornire nome e cognome del segnalante',
        required=True, notnull=True, requires=IS_NOT_EMPTY(),
        rname='nome_cognome'
    ),
    Field('telefono', length=20,
        label='Telefono segnalante',
        comment='Fornire il telefono del segnalante',
        required=True, notnull=True, requires=IS_NOT_EMPTY()
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
    Field('id', 'id', default=lambda: new_id(db['segnalazione_lavorazione'])),
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

db.define_table('segnalazioni_utili',
    # ...
    Field('segnalante_id', 'integer', rname='id_segnalante'),
    Field('descrizione'),
    # ...
    Field('lavorazione_id', 'integer', rname='id_lavorazione'),
    migrate = False,
    rname = f'{SCHEMA}.v_segnalazioni' # <- VISTA!
)

db.define_table('segnalazioni_lista',
    Field('data_ora'),
    Field('segnalante_id', 'integer', rname='id_segnalante'),
    Field('descrizione'),
    Field('criticita_id', 'reference tipo_criticita',
        label = 'Id tipo di criticità',
        comment = 'Identificativo univoco del tipo di criticità segnalata',
        rname='id_criticita'
    ),
    Field('descrizione'),
    Field('rischio', 'boolean', label='Segnalazione presenza persone a rischio'),
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('tipo_evento'),
    Field('civico_id', 'reference civico', rname='id_civico'),
    Field('localizzazione'),
    Field('municipio_id', 'reference municipio', rname='id_municipio'),
    Field('municipio', rname='nome_munic'),
    Field('operatore', label='Identificativo operatore',
        comment = 'Numero di matricola o il codice fiscale',
        rname='id_operatore'
    ),
    Field('note', 'text'),
    Field('lavorazione_id', 'reference segnalazione_lavorazione', rname='id_lavorazione'),
    Field('in_lavorazione', 'boolean'),
    Field('id_profilo'),
    Field('fine_sospensione', 'datetime'),
    Field('id_man', 'integer'),
    Field('geom', 'geometry()'),
    migrate = False,
    rname = f'{SCHEMA}.v_segnalazioni_lista' # <- VISTA!
)

db.define_table("incarichi_lista",
    Field('data_ora', rname='data_ora_invio'),
    Field('segnalazione_id', rname='id_segnalazione'),
    Field('stato_id', rname='id_stato_incarico'),
    Field('stato', rname='descrizione_stato'),
    migrate = False,
    rname = f'{SCHEMA}.v_incarichi_last_update'
)

db.define_table('comunicazione',
    Field('lavorazione_id', 'integer', notnull=True, required=True,
        rname='id_lavorazione'
    ),
    Field('mittente', required=True),
    Field('testo', 'text'),
    Field('timeref', 'datetime', rname='data_ora_stato',
        default = lambda: datetime.now(timezone('Europe/Rome')).replace(tzinfo=None)
    ),
    Field('allegato'),
    primarykey = ['lavorazione_id', 'timeref'],
    rname = f'{SCHEMA}.t_comunicazioni_segnalazioni'
)

uo_value = "concat('com_','PO' || codice_mun::text)" # 'PO'::text || m.codice_mun::text AS cod,
uo_label = "'Distretto ' || codice_mun::text"
unita_operative = map(
    lambda row: (row[uo_value], row[uo_label],),
    db(db.municipio)(db.profilo_utilizatore.id==6).iterselect(uo_value, uo_label)
)

db.define_table('incarico',
    Field('id', 'id', default=lambda: incarichi_new_id(db['incarico'])),
    Field('invio', 'datetime',
        # default = lambda: datetime.now(timezone('Europe/Rome')).replace(tzinfo=None),
        rname='data_ora_invio',
        notnull=True
    ),
    Field('profilo_id', 'reference profilo_utilizatore',
        notnull=True, required=True,
        requires = IS_IN_DB(
            db(db.profilo_utilizatore),
            db.profilo_utilizatore.id,

        ),
        rname='id_profilo'
    ),
    Field('descrizione', required=True, notnull=True),
    Field('uo_id', rname='id_uo', required=True, notnull=True,
        requires = IS_IN_SET(list(unita_operative))
    ),
    Field('preview', 'datetime', label='Inizio incarico previsto',
        rname='time_preview'
    ),
    Field('start', 'datetime', label='Inizio incarico',
        comment = 'Inizio incarico effettivo',
        rname='time_start'
    ),
    Field('stop', 'datetime', label='Fine incarico',
        comment = 'Fine incarico',
        rname='time_stop'
    ),
    Field('note', rname='note_ente', label='Note di incarico/intervento'),
    Field('rifiuto', label='Note di rifiuto', rname='note_rifiuto'),
    rname = f'{SCHEMA}.t_incarichi'
)

db.define_table('stato_incarico',
    Field('incarico_id', 'reference incarico',
        required=True, notnull=True,
        requires=IS_IN_DB(db(db.incarico), db.incarico.id),
        rname='id_incarico'
    ),
    Field('stato_id', 'reference tipo_stato_incarico',
        label = "Stato dell'incarico o intervento",
        required=True, notnull=True,
        requires = IS_IN_DB(
            db(db.tipo_stato_incarico.valido!=False),
            db.tipo_stato_incarico.id,
            db.tipo_stato_incarico.descrizione
        ),
        rname='id_stato_incarico'
    ),
    Field('timeref', 'datetime', notnull=True,
        rname='data_ora_stato'
    ),
    Field('parziale', 'boolean', default=False, notnull=True),
    primarykey = ['incarico_id', 'stato_id', 'timeref'],
    rname = f'{SCHEMA}.stato_incarichi'
)

db.define_table('comunicazione_incarico',
    Field('incarico_id', 'reference incarico', rname='id_incarico'),
    Field('testo'),
    Field('timeref', 'datetime', rname='data_ora_stato'),
    Field('allegato'),
    primarykey = ['incarico_id', 'timeref'],
    rname=f'{SCHEMA}.t_comunicazioni_incarichi'
)

db.define_table('comunicazione_incarico_inviata',
    Field('incarico_id', 'reference incarico', rname='id_incarico'),
    Field('testo'),
    Field('timeref', 'datetime', rname='data_ora_stato'),
    Field('allegato'),
    primarykey = ['incarico_id', 'timeref'],
    rname=f'{SCHEMA}.t_comunicazioni_incarichi_inviate'
)

db.define_table('join_segnalazione_incarico',
    Field('incarico_id', 'reference incarico',
        required=True, notnull=True, unique=True,
        rname='id_incarico'
    ),
    Field('lavorazione_id', 'reference segnalazione_lavorazione',
        required=True, notnull=True,
        rname='id_segnalazione_in_lavorazione'
    ),
    primarykey = ['incarico_id'],
    rname = f'{SCHEMA}.join_segnalazioni_incarichi'
)

db.define_table('incarichi_utili',
    Field('invio', 'datetime', rname='data_ora_invio', notnull=True),
    Field('profilo_id', 'reference profilo_utilizatore',
        notnull=True, required=True,
        rname='id_profilo'
    ),
    Field('descrizione', required=True, notnull=True),
    Field('uo_id', rname='id_uo', required=True, notnull=True),
    # ...
    Field('segnalazione_id', 'integer', rname='id_segnalazione'),
    Field('lavorazione_id', 'integer', rname='id_lavorazione'),
    # ...
    Field('descrizione_segnalazione'),
    # ...
    Field('stato_incarico_id', rname='id_stato_incarico'),
    Field('timeref', 'datetime', rname='data_ora_stato'),
    rname = f'{SCHEMA}.v_incarichi',
    migrate = False
)

db.define_table('intervento',
    Field('intervento_id', 'integer',
        label = 'Identificativo intevento Verbatel',
        notnull=True, unique=True, required=True
    ),
    Field('incarico_id', 'reference incarico',
        required=True, notnull=True, unique=True,
        requires = IS_IN_DB(db(db.incarico), db.incarico.id)
    ),
    # Field('segnalazione_id', 'integer',
    #     notnull=True, unique=True, required=True
    # ),
    # TODO: Aggiungere data creazione, ultimo aggiornamento e log di risposta
    migrate = settings.MIGRATE_INTERVENTO,
    rname = 'verbatel.interventi'
)

db.define_table('segnalazione_da_vt',
    Field('intervento_id', 'integer',
        label = 'Identificativo intevento Verbatel',
        notnull=True, unique=True, required=True
    ),
    Field('segnalazione_id', 'reference segnalazione',
        required=True, notnull=True, unique=True,
        requires = IS_IN_DB(db(db.segnalazione), db.segnalazione.id)
    ),
    migrate = False, # settings.MIGRATE_INTERVENTO,
    rname = 'verbatel.segnalazioni_da_verbatel'
)

db.define_table('presidio',
    Field('id', 'id', default=lambda: new_id(db['presidio'])),
    Field('timeref', 'datetime', rname='data_ora_invio'),
    Field('profilo_id', 'reference profilo_utilizatore',
        required=True, notnull=True, rname='id_profilo'
    ),
    Field('descrizione'),
    Field('preview', 'datetime', rname='time_preview',
        requires = IS_EMPTY_OR(IS_DATETIME(
            format="%Y-%m-%d %H:%M",
            error_message="Inserire un formato data del tipo: %(format)s"
        ))
    ),
    Field('start', 'datetime', rname='time_start',
        requires = IS_EMPTY_OR(IS_DATETIME(
            format="%Y-%m-%d %H:%M",
            error_message="Inserire un formato data del tipo: %(format)s"
        ))),
    Field('stop', 'datetime', rname='time_stop',
        requires = IS_EMPTY_OR(IS_DATETIME(
            format="%Y-%m-%d %H:%M",
            error_message="Inserire un formato data del tipo: %(format)s"
        ))),
    Field('note', rname='note_ente'),
    Field('geom', 'geometry()', required=True, notnull=True),
    Field('evento_id', 'reference evento', rname='id_evento'),
    rname = f'{SCHEMA}.t_sopralluoghi_mobili'
)

db.define_table('stato_presidio',
    Field('presidio_id', 'reference presidio', rname='id_sopralluogo'),
    Field('stato_presidio_id', 'reference tipo_stato_sopralluogo', rname='id_stato_sopralluogo'),
    Field('timeref', 'datetime', rname='data_ora_stato'),
    Field('parziale', 'boolean'),
    # id_sopralluogo, id_stato_sopralluogo, data_ora_stato
    primarykey = ['presidio_id', 'stato_presidio_id', 'timeref'],
    rname = f'{SCHEMA}.stato_sopralluoghi_mobili'
)

db.define_table('join_presidio_squadra',
    Field('presidio_id', 'reference presidio', rname='id_sopralluogo'),
    Field('squadra_id', 'reference squadra', rname='id_squadra'),
    Field('valido', 'boolean'),
    Field('timeref', 'datetime', rname='data_ora'),
    Field('cambio', 'datetime', rname='data_ora_cambio'),
    # id_sopralluogo, id_squadra, data_ora
    primarykey = ['presidio_id', 'squadra_id', 'timeref'],
    rname = f'{SCHEMA}.join_sopralluoghi_mobili_squadra'
)

db.define_table('comunicazione_presidio',
    Field('presidio_id', 'reference presidio', rname='id_sopralluogo'),
    Field('testo'),
    Field('timeref', 'datetime', rname='data_ora_stato'),
    Field('allegato'),
    primarykey = ['presidio_id', 'timeref'],
    rname = f'{SCHEMA}.t_comunicazioni_sopralluoghi_mobili_inviate'
)

db.define_table('pattuglia_pm',
    Field('pattuglia_id', 'integer',
        label = 'Identificativo pattuglia Verbatel',
        notnull=True, unique=True, required=True
    ),
    Field('squadra_id', 'reference squadra',
        required=True, notnull=True, unique=True,
        requires = IS_IN_DB(db(db.squadra), db.squadra.id)
    ),
    Field('presidio_id', 'reference presidio',
        required=True, notnull=True, unique=True,
        requires = IS_IN_DB(db(db.presidio), db.presidio.id)
    ),
    migrate = settings.MIGRATE_PATTUGLIA_PM,
    rname = 'verbatel.pattuglia_pm'
)
