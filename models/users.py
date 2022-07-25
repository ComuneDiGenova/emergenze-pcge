# -*- coding: utf-8 -*-

from .. import settings
from .users_decodifica import SCHEMA, db, Field
from .tools import new_id

from pydal.validators import *

db.define_table('squadra',
    Field('id', 'id', default=lambda: new_id(db['squadra'])),
    Field('nome', required=True, notnull=True),
    Field('evento_id', 'reference evento',
        required = True,
        requires = IS_IN_DB(db(db.evento.valido==True), db.evento.id),
        rname='id_evento'
    ),
    Field('stato_id', 'reference stato_squadra',
        required = True,
        requires = IS_IN_DB(db(db.stato_squadra.valido==True), db.stato_squadra.id, db.stato_squadra.descrizione, zero=None),
        rname='id_stato'
    ),
    Field('afferenza', required=True, notnull=True, rname='cod_afferenza'),
    Field('nascosta', 'boolean', notnull=True, default=False, rname='da_nascondere'),
    rname = f'{SCHEMA}.t_squadre'
)

db.define_table('storico_squadra',
    Field('squadra_id', 'reference squadra', notnull=True, rname='id_squadra'),
    Field('aggiornamento', rname='log_aggiornamento'),
    Field('timeref', 'datetime', notnull=True, rname='data_ora'),
    primarykey = ['squadra_id', 'timeref'],
    rname = f'{SCHEMA}.t_storico_squadre'
)

db.define_table('componente',
    Field('squadra_id', required=True, notnull=True, rname='id_squadra'),
    Field('matricola', required=True, notnull=True, rname='matricola_cf'),
    Field('capo', 'boolean', notnull=True, default=False, rname='capo_squadra'),
    Field('inizio', 'datetime', notnull=True, rname='data_start'),
    Field('fine', 'datetime', notnull=True, rname='data_end'),
    primarykey = ['squadra_id', 'matricola', 'inizio'],
    rname = f'{SCHEMA}.t_componenti_squadre'
)

db.define_table('componenti',
    Field('nome', rname='nome_squadra'),
    Field('matricola', rname='matricola_cf'),
    Field('capo_squadra'),
    Field('cognome'),
    Field('nome'),
    Field('livello1'),
    Field('livello2'),
    Field('livello3'),
    Field('email', rname='mail'),
    Field('telefono'),
    Field('squadra_id', rname='id_squadra'),
    Field('start', 'datetime', rname='data_start'),
    Field('end', 'datetime', rname='data_end'),
    rname = f'{SCHEMA}.v_componenti_squadre' # <- VISTA!
)

db.define_table('telefono',
    Field('codice', required=True, notnull=True,
        requires = IS_IN_DB(db(db.squadra), db.squadra.id),
        rname='cod'
    ),
    Field('telefono',
        label = 'Numero di telefono',
        required=True, notnull=True
    ),
    Field('matricola', required=True, notnull=True, rname='matricola_cf'),
    primarykey = ['codice', 'telefono', 'matricola'],
    rname = f'{SCHEMA}.t_telefono_squadre'
)

db.define_table('email',
    Field('codice', label='Identificativo squadra',
        required=True, notnull=True,
        requires = IS_IN_DB(db(db.squadra), db.squadra.id),
        rname='cod'
    ),
    Field('email',
        label = 'Indirizzo email',
        required=True, notnull=True, rname='mail',
        requires = IS_EMAIL()
    ),
    Field('matricola', required=True, notnull=True, rname='matricola_cf'),
    primarykey = ['codice', 'email', 'matricola'],
    rname = f'{SCHEMA}.t_mail_squadre'
)

db.define_table('agente',
    Field('matricola', label='Matricola/CF',
        required=True, notnull=True, unique=True,
        rname='matricola_cf'
    ),
    Field('cognome', required=True, notnull=True),
    Field('nome', required=True, notnull=True),
    Field('livello1'),
    Field('livello2', requires=IS_EMPTY_OR(IS_IN_DB(db(db.livello2), db.livello2.descrizione))),
    Field('livello3'),
    migrate = settings.MIGRATE_AGENTE,
    rname = 'verbatel.agenti_pm'
)

db.define_table('personale',
    Field('matricola', label='Matricola/CF', rname='matricola_cf'),
    Field('cognome'),
    Field('nome'),
    Field('livello1'),
    Field('livello2'),
    Field('livello3'),
    rname = f'{SCHEMA}.v_personale_squadre' # <- VISTA!
)
