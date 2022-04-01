# -*- coding: utf-8 -*-

from .. import settings

from ..common import db, Field

from pydal.validators import *
from pydal.validators import Validator

from codicefiscale import codicefiscale
import phonenumbers

SCHEMA = 'chiamate'

iscrizione_optons = []

class isValidCf(Validator):
    """docstring for isValidCf"""

    def validate(self, value, record_id=None):
        """ """
        try:
            assert codicefiscale.is_valid(value)
        except AssertionError:
            raise ValidationError("Codice fiscale non valido")
        else:
            return value

class isValidPhoneNumber(Validator):
    """docstring for isValidCf"""

    def validate(self, value, record_id=None):
        """ """
        try:
            assert phonenumbers.is_valid_number(phonenumbers.parse(value, None))
        except AssertionError:
            raise ValidationError("Formato numero telefonico non valido")
        else:
            return value

db.define_table('utente',
    # TODO: CF validator
    Field('cf', length=16, required=True, notnull=True, unique=True, requires=isValidCf()),
    Field('nome', required=True, notnull=True),
    Field('cognome', required=True, notnull=True),
    Field('iscrizione', required=True, notnull=True, requires=IS_IN_SET(iscrizione_optons)),
    Field('disabilita', 'boolean', default=False, required=True, notnull=True),
    # ...
    migrate = True,
    rname=f'{SCHEMA}.utente'
)

db.define_table('contatto',
    Field('telefono', required=True, notnull=True, unique=True, requires=isValidPhoneNumber()),
    # ...
    migrate = True,
    rname=f'{SCHEMA}.contatto'
)

db.define_table('civico_fc',
    Field('codvia', length=5, label='Codice strada', required=True, notnull=True),
    Field('desvia', length=150, label='Nome strada/piazza', required=True, notnull=True),
    Field('numero', length=4, required=True, notnull=True),
    Field('lettera', length=1),
    Field('colore', length=1),
    Field('cap', length=5),
    Field('scala', 'decimal(2,0)'),
    # Field('codmunicipio'),
    # Field('municipio', rname='desmunicipio'),
    # Field('circoscrizione', rname='descircoscrizione'),
    # Field('unita_urbanistica', rname='desunitaurbanistica'),
    migrate = True,
    rname=f'{SCHEMA}.civico'
)
