# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

SCHEMA = 'segnalazioni'

db.define_table('tipo_criticita',
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    rname = 'segnalazioni.tipo_criticita'
)

db.define_table('tipo_oggetto_rischio',
    Field('nome_tabella', required=True, notnull=True),
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    Field('campo_identificativo'),
    Field('elenco_elementi_segnalazione', 'boolean', default=True, required=True, notnull=True),
    rname = 'segnalazioni.tipo_oggetti_rischio'
)

db.define_table('tipo_segnalante',
    Field('descrizione'),
    Field('valido', 'boolean', notnull=True, default=True),
    rname = 'segnalazioni.tipo_segnalanti'
)
