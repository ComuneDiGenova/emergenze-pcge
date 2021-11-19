# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

SCHEMA = 'segnalazioni'

"""
id integer NOT NULL DEFAULT nextval('segnalazioni.tipo_criticita_id_seq'::regclass),
    descrizione character varying COLLATE pg_catalog."default" NOT NULL,
    valido boolean NOT NULL DEFAULT true,
    CONSTRAINT tipo_criticita_pkey PRIMARY KEY (id)
"""

db.define_table('tipo_criticita',
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    rname = 'segnalazioni.tipo_criticita'
)


db.define_table('tipo_oggetti_rischio',
    Field('nome_tabella', required=True, notnull=True),
    Field('descrizione', required=True, notnull=True),
    Field('valido', 'boolean', default=True, required=True, notnull=True),
    Field('campo_identificativo'),
    Field('elenco_elementi_segnalazione', 'boolean', default=True, required=True, notnull=True),
    rname = 'segnalazioni.tipo_oggetti_rischio'
)
