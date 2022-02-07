# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

SCHEMA = 'segnalazioni'

db.define_table('tipo_criticita',
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    rname = f'{SCHEMA}.tipo_criticita'
)

db.define_table('tipo_oggetto_rischio',
    Field('nome_tabella', required=True, notnull=True),
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    Field('campo_identificativo'),
    Field('elenco_elementi_segnalazione', 'boolean', default=True, required=True, notnull=True),
    rname = f'{SCHEMA}.tipo_oggetti_rischio'
)

db.define_table('tipo_segnalante',
    Field('descrizione'),
    Field('valido', 'boolean', notnull=True, default=True),
    rname = f'{SCHEMA}.tipo_segnalanti'
)

db.define_table('tipo_stato_incarico',
    Field('descrizione'),
    Field('valido', 'boolean')
    rname = f'{SCHEMA}.tipo_stato_incarichi'
)
