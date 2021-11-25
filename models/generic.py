# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

# from geomet import wkt

SCHEMA = 'geodb'

db.define_table('municipio',
    Field('geom', 'geometry()'),
    Field('codice', rname='codice_mun'),
    Field('nome', rname='nome_munic'),
    rname = f'{SCHEMA}.municipi'
)

db.define_table('civico',
    Field('gid', 'integer', required=True, notnull=True),
    Field('id_asta', length=10),
    Field('coord_nord', 'double'),
    Field('coord_est', 'double'),
    Field('codvia', length=5),
    Field('desvia', length=150, label='Toponimo'),
    Field('numero', length=4),
    Field('lettera', length=1),
    Field('colore', length=1),
    Field('cap', length=5),
    Field('uso', length=1, required=True, notnull=True),
    Field('tipo_accesso', 'decimal(2,0)'),
    Field('sottotipo_accesso', 'decimal(2,0)'),
    Field('angolo', 'double'),
    Field('provenienza', 'decimal(2,0)'),
    Field('scala', 'decimal(2,0)'),
    # ...
    Field('codmunicipio'),
    Field('desmunicipio'),
    # ...
    Field('geom', 'geometry()'),
    rname = f'{SCHEMA}.civici'
)

# db.municipio.geometry = Field.Virtual('geometry',
#     lambda row: wkt.loads(row['municipio'].geom.replace(" Z ", " ").replace(" M ", " ").replace(" ZM ", " "))
# )
