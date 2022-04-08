# -*- coding: utf-8 -*-

from .. import settings

from ..common import db, Field

from pydal.validators import *
from pydal.validators import Validator, ValidationError

from codicefiscale import codicefiscale
import phonenumbers
import datetime

from ..chiamate.tools import iscrizione_optons

SCHEMA = 'chiamate'

now = lambda: datetime.datetime.utcnow()
today = lambda: datetime.date.today()

# def now():
#     import pdb; pdb.set_trace()
#     return datetime.datetime.utcnow()

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
    Field('codiceFiscale', length=16, required=True, notnull=True, unique=True, rname='cf'),
    Field('nome', required=True, notnull=True, requires=IS_NOT_EMPTY()),
    Field('cognome', required=True, notnull=True, requires=IS_NOT_EMPTY()),
    Field('dataRegistrazione', 'date', required=True, notnull=True,
        default = today,
        requires=IS_DATE(format="%Y-%m-%d", error_message="Inserire un formato data del tipo: %(format)s"),
        rname='dataregistrazione'
    ),
    Field('iscrizione', required=True, notnull=True, requires=IS_IN_SET(iscrizione_optons)),
    Field('vulnerabilitaPersonale', length=2, default='NO', notnull=True,
        label = 'Vulnerabilità personale',
        comment = 'Indica se la persona possiede una vulnerabilità personale',
        requires=IS_IN_SET(['SI', 'NO', None], zero='NO'),
        rname = 'vulnerabilitapersonale'
    ),
    # Field('disabilita', 'boolean', default=False, required=True, notnull=True, requires=IS_IN_SET()),
    Field('eMail',
        label = 'Indirizzo email',
        comment = 'Email di contatto alternativo ai numeri telefonici',
        requires = IS_EMAIL(),
        rname = 'email'
    ),
    # ...
    Field(
        "created_on",
        "datetime",
        default=now,
        writable=False,
        readable=False,
        # label=self.param.messages["labels"].get("created_on"),
    ),
    Field(
        "modified_on",
        "datetime",
        update=now,
        default=now,
        writable=False,
        readable=False,
        # label=self.param.messages["labels"].get("modified_on"),
    ),
    migrate = False,
    rname=f'{SCHEMA}.utente'
)

db.utente.codiceFiscale.requires = requires=[
    IS_NOT_EMPTY(),
    isValidCf(),
    IS_NOT_IN_DB(db(db.utente), db.utente.codiceFiscale, error_message='Valore nullo o già registrato')
]

# {
#   "idUtente": 100,
#   "idContatto": 1234,
#   "numero": "010123456",
#   "tipo": "FISSO",
#   "lingua": "BUONA",
#   "linguaNoItalia": "francese"
# }

db.define_table('contatto',
    Field('numero', required=True, notnull=True, requires=isValidPhoneNumber(), rname='telefono'),
    Field('idUtente', 'reference utente', required=True, notnull=True,
        requires=IS_IN_DB(db(db.utente), db.utente.id),
        rname = 'idutente'
    ),
    Field('tipo',
        requires=IS_IN_SET(
            ['FISSO', 'CELLULARE'],
            error_message='Valore non permesso, scegliere tra: FISSO, CELLULARE'
        )
    ),
    Field('lingua',
        requires = IS_IN_SET(
            ['BUONA', 'BUONA SOLO SE', 'AUDIOLESO o NON UDENTE'],
            error_message = 'Valore non permesso, scegliere tra: BUONA, BUONA SOLO SE, AUDIOLESO o NON UDENTE'
        )
    ),
    Field('linguaNoItalia',
        comment = 'Lingua preferita a quella italiana per il messaggio vocale',
        rname = 'linguanoitalia'
    ),
    # ...
    Field(
        "created_on",
        "datetime",
        default=now,
        writable=False,
        readable=False,
        # label=self.param.messages["labels"].get("created_on"),
    ),
    Field(
        "modified_on",
        "datetime",
        update=now,
        default=now,
        writable=False,
        readable=False,
        # label=self.param.messages["labels"].get("modified_on"),
    ),
    migrate = True,
    rname=f'{SCHEMA}.contatto'
)

db.define_table('civico_fc',
    Field('topon_id'),
    Field('desvia', length=150, label='Nome strada/piazza', required=True, notnull=True),
    Field('cod_strada', length=5, label='Codice strada', required=True, notnull=True, rname='codvia'),
    Field('numero', length=4, required=True, notnull=True),
    Field('lettera', length=1),
    Field('colore', length=1),
    Field('testo'),
    Field('codmunicipio', 'integer', length=2),
    Field('codcircoscrizione', 'integer', length=2),
    migrate = False,
    rname=f'{SCHEMA}.civico'
)

# db.define_table('componente',
#     Field('civico_id', 'reference civico_fc'),
#     Field('')
# )
