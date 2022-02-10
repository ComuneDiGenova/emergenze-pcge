# -*- coding: utf-8 -*-

from .. import settings
from .users_decodifica import SCHEMA, db, Field
from pydal.validators import *

db.define_table('squadra',
    Field('nome', required=True, notnull=True),
    Field('evento_id', 'reference evento',
        requires = IS_EMPTY_OR(IS_IN_DB(db(db.evento.valido==True), db.evento.id)),
        rname='id_evento'
    ),
    Field('stato_id', 'reference tipo_stato_squadra', rname='id_stato'),
    Field('afferenza', required=True, notnull=True, rname='cod_afferenza'),
    Field('da_nascondere', 'boolean', notnull=True, default=False),
    rname = 'users.t_squadre'
)

db.define_table('componente',
    Field('squadra_id', required=True, notnull=True, rname='id_squadra'),
    Field('matricola', required=True, notnull=True, rname='matricola_cf'),
    Field('capo', 'boolean', notnull=True, default=False, rname='capo_squadra'),
    Field('inizio', 'datetime', notnull=True, rname='data_start'),
    Field('fine', 'datetime', notnull=True, rname='data_end'),
    primarykey = ['squadra_id', 'matricola', 'inizio'],
    rname = 'users.t_componenti_squadre'
)

db.define_table('telefono',
    Field('codice', required=True, notnull=True, rname='cod'),
    Field('telefono', required=True, notnull=True),
    Field('matricola', required=True, notnull=True, rname='matricola_cf'),
    primarykey = ['codice', 'telefono', 'matricola'],
    rname = 'users.t_telefono_squadre'
)

db.define_table('email',
    Field('codice', required=True, notnull=True, rname='cod'),
    Field('address', required=True, notnull=True, rname='mail'),
    Field('matricola', required=True, notnull=True, rname='matricola_cf'),
    primarykey = ['codice', 'address', 'matricola'],
    rname = 'users.t_mail_squadre'
)

db.define_table('agenti',
    Field('matricola', required=True, notnull=True, unique=True,
        rname='matricola_cf'
    ),
    Field('cognome', required=True, notnull=True),
    Field('nome', required=True, notnull=True),
    Field('livello1'),
    Field('livello2'),
    Field('livello3'),
    migrate = settings.MIGRATE_INTERVENTO,
    rname = 'verbatel.agenti_pm'
)