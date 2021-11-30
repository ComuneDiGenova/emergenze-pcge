# -*- coding: utf-8 -*-

from ..common import db, Field
from pydal.validators import *

from geomet import wkt

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

geometry_ = 'ST_AsText(ST_Transform(geom, 4326))'

db.civico.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('profilo_utilizatore',
    Field('descrizione'),
    Field('valido', 'boolean', notnull=True, default=True),
    rname = 'users.profili_utilizzatore'
)

db.define_table('fiume',
    Field('gid', 'id'),
    Field('id', 'integer'),
    Field('nome', rname='nome_rivo'),
    Field('regione', rname='nome_rivo_regione'),
    Field('catasto', rname='nome_rivo_catasto'),
    Field('toponomastica', rname='nome_toponomastica'),
    Field('tipo', rname='tipo_rivo'),
    Field('codice', rname='cod_rivo'),
    Field('note'),
    Field('geom', 'geometry()'),
    rname = f'{SCHEMA}.fiumi'
)

db.fiume.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('area_verde',
    Field('id', 'id', rname='gid'),
    Field('cod_avd'),
    Field('geom', 'geometry()'),
    # ...
    rname=f'{SCHEMA}.aree_verdi'
)

db.area_verde.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('elemento_stradale',
    Field('id', 'id', rname='gid'),
    Field('id_oggetto', 'integer'),
    Field('geom', 'geometry()'),
    # ...
    rname=f'{SCHEMA}.elemento_stradale'
)

db.elemento_stradale.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('edificio',
    Field('id', 'id', rname='gid'),
    Field('id_oggetto', 'integer'),
    Field('geom', 'geometry()'),
    # ...
    rname = f'{SCHEMA}.edifici'
)

db.edificio.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('impianti',
    Field('id', 'id', rname='gid'),
    Field('pk_id', 'integer'),
    Field('geom', 'geometry()'),
    # ...
    rname = f'{SCHEMA}.rir_impianti'
)

db.impianti.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('sottopasso',
    Field('id', 'id', rname='gid'),
    Field('id_crit', 'integer'),
    Field('geom', 'geometry()'),
    # ...
    rname = f'{SCHEMA}.sottopassi'
)

db.sottopasso.geometry = Field.Virtual('geometry',
    lambda row: wkt.loads(row[geometry_])
)

db.define_table('v_utenti_sistema',
    Field('nome'),
    Field('cognome'),
    Field('descrizione'),
    Field('matricola', rname='matricola_cf'),
    rname = 'users.v_utenti_sistema'
)

db.define_table('log',
    Field('timeref', 'datetime', rname='date'),
    Field('schema'),
    Field('operatore'),
    Field('operazione'),
    rname = 'varie.t_log'
)
