# -*- coding: utf-8 -*-

from .. import settings

from .evento_decodifica import SCHEMA, Field, db

from pydal.validators import *

db.define_table('evento',
    Field('inizio', 'datetime', rname='data_ora_inizio_evento'),
    Field('fine', 'datetime', rname='data_ora_fine_evento'),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    Field('fine_sospensione', 'datetime'),
    Field('chiusura', 'datetime', rname='data_ora_chiusura'),
    rname=f'{SCHEMA}.t_eventi'
)

db.define_table('nota_evento',
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('nota'),
    Field('timeref', 'datetime', rname='data_ora_nota'),
    rname=f'{SCHEMA}.t_note_eventi'
)

db.define_table('join_tipo_evento',
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('tipo_evento_id', 'reference tipo_evento', rname='id_tipo_evento'),
    primarykey=['evento_id', 'tipo_evento_id'],
    rname=f'{SCHEMA}.join_tipo_evento'
)

db.define_table('join_tipo_allerta',
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('tipo_allerta_id', 'reference tipo_allerta', rname='id_tipo_allerta'),
    Field('messaggio', required=True, notnull=True, rname='messaggio_rlg'),
    Field('inizio', 'datetime', required=True, notnull=True, rname='data_ora_inizio_allerta'),
    Field('fine', 'datetime', rname='data_ora_fine_allerta'),
    Field('valido', 'boolean'),
    # Field('allegato')
    rname=f'{SCHEMA}.join_tipo_allerta'
)

db.define_table('join_municipio',
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('municipio_id', 'reference municipio', required=True, notnull=True, rname='id_municipio'),
    Field('inizio', 'datetime', required=True, notnull=True, rname='data_ora_inizio'),
    Field('fine', 'datetime', rname='data_ora_fine'),
    rname = f'{SCHEMA}.join_municipi'
)

db.define_table('join_tipo_foc',
    Field('evento_id', 'reference evento', rname='id_evento'),
    Field('tipo_foc_id', 'reference tipo_foc', rname='id_tipo_foc'),
    Field('inizio', 'datetime', required=True, notnull=True, rname='data_ora_inizio_foc'),
    Field('fine', 'datetime', rname='data_ora_fine_foc'),
    primarykey=['evento_id', 'tipo_foc_id', 'inizio'],
    # Field('allegato')
    rname=f'{SCHEMA}.join_tipo_foc'
)

db.define_table('evento_inviato',
    Field('evento_id', 'reference evento',
        label = 'Identificativo intevento Verbatel',
        notnull=True, unique=True, required=True,
        requires = IS_IN_DB(db(db.evento), db.evento.id)
    ),
    Field('inviato', 'boolean',
        label = 'Identificativo intevento Verbatel',
        notnull=True, required=False, default=True
    ),
    migrate = settings.MIGRATE_EVENTO,
    rname = 'verbatel.eventi_inviati'
)
