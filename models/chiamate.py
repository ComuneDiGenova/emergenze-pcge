# -*- coding: utf-8 -*-

from .. import settings

from ..common import db, Field

from pydal.validators import *
from pydal.validators import Validator, ValidationError

from codicefiscale import codicefiscale
import phonenumbers
import datetime
import string

from ..chiamate.tools import iscrizione_options, LANGUAGES

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
    Field('nome', required=True, notnull=True,
        requires = [
            IS_NOT_EMPTY(),
            IS_MATCH('^[\D]*$', error_message='Caratteri non validi')
        ]
    ),
    Field('cognome', required=True, notnull=True,
        requires = [
            IS_NOT_EMPTY(),
            IS_MATCH('^[\D]*$', error_message='Caratteri non validi')
        ]
    ),
    Field('dataRegistrazione', 'date', required=True, notnull=True,
        default = today,
        requires=IS_DATE(format="%Y-%m-%d", error_message="Inserire un formato data del tipo: %(format)s"),
        rname='dataregistrazione'
    ),
    Field('iscrizione', required=True, notnull=True, requires=IS_IN_SET(iscrizione_options)),
    Field('vulnerabilitaPersonale', length=2, default='NO', notnull=True,
        label = 'Vulnerabilità personale',
        comment = 'Indica se la persona possiede una vulnerabilità personale',
        requires=IS_IN_SET(['SI', 'NO']),
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
    # IS_NOT_IN_DB(db(db.utente), db.utente.codiceFiscale, error_message='Valore nullo o già registrato')
]

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
        requires = IS_EMPTY_OR(IS_IN_SET(LANGUAGES)),
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
    migrate = False,
    rname=f'{SCHEMA}.contatto'
)

db.define_table('recapito',
    Field('indirizzoCompleto', required=True, notnull=True,
        label = 'Indirizzo completo', comment = 'Indirizzo completo',
        requires = IS_NOT_EMPTY(error_message='Valore richiesto'),
        rname = 'indirizzocompleto'
    ),
    Field('idVia', required=True, notnull=True,
        requires = IS_NOT_EMPTY(error_message='Valore richiesto'),
        rname = 'idvia'
    ),
    Field('numeroCivico', length = 6, required=True, notnull=True,
        label='Civico', comment='Numero civico',
        requires = IS_NOT_EMPTY(error_message='Valore richiesto'),
        rname = 'numerocivico'
    ),
    Field('esponente', length=1,
        label='Esponente', comment='Esponente del civico',
        requires = IS_EMPTY_OR(IS_IN_SET(string.ascii_uppercase))
    ),
    Field('colore', length=1,
        label='Colore', comment='Colore del civico',
        requires = IS_EMPTY_OR(IS_IN_SET('NR'))
    ),
    Field('interno', length=3,
        label='Interno', comment='Interno',
    ),
    Field('internoLettera', length=1,
        label='Lettera', comment="Lettera dell'interno",
        equires = IS_IN_SET(string.ascii_uppercase),
        rname = 'internolettera'
    ),
    Field('scala', label='Scala', comment="Scala",),
    Field('posizione',
        label='posizione', comment='Posizione della abitazione rispetto al piano stradale di riferimento',
        requires = IS_EMPTY_OR(IS_IN_SET(['STRADA', 'SOTTOSTRADA']))
    ),
    Field('vulnerabilita',
        label='Vulnerabilità', comment='Grado di vulnerabilità',
        requires = IS_EMPTY_OR(IS_IN_SET(['SOSTENIBILE', 'MATERIALE', 'PERSONALE']))
    ),
    Field('amministratore', label='Amministratore', comment="Recapito dell'amministratore condominiale"),
    Field('proprietario', label='Proprietario', comment='Recapito del proprietario'),
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
    rname=f'{SCHEMA}.recapito'
)

db.define_table('nucleo',
    Field('idUtente', 'reference utente', required=True, notnull=True,
        requires=IS_IN_DB(db(db.utente), db.utente.id),
        rname = 'idutente'
    ),
    Field('idCivico', 'reference recapito', required=True, notnull=True,
        requires=IS_IN_DB(db(db.recapito), db.recapito.id),
        rname = 'idcivico'
    ),
    Field('tipo', required=True, notnull=True,
        label = 'Ruolo',
        comment = "Indica che ruolo ha la persona all'interno del nucleo abitativo",
        requires = [
            IS_NOT_EMPTY(),
            IS_IN_SET(["CAPO FAMIGLIA", "RESIDENTE", "NON RESIDENTE"])
        ]
    ),
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
    rname=f'{SCHEMA}.componente'
)

db.define_table('recupero',
    Field('nome', required=True, notnull=True,
        requires = [
            IS_NOT_EMPTY(),
            IS_MATCH('^[\D]*$', error_message='Caratteri non validi')
        ]
    ),
    Field('cognome', required=True, notnull=True,
        requires = [
            IS_NOT_EMPTY(),
            IS_MATCH('^[\D]*$', error_message='Caratteri non validi')
        ]
    ),
    Field('numero', required=True, notnull=True, requires=isValidPhoneNumber(), rname='telefono'),
    Field('indirizzoCompleto'),
    Field('numeroCivico', length = 20, required=True, notnull=True,
        label='Civico', comment='Numero civico',
        requires = IS_NOT_EMPTY(error_message='Valore richiesto'),
        rname = 'numerocivico'
    ),
    Field('gruppo', length=1, required=True, notnull=True,
        requires = IS_IN_SET(['1', '2', '3'])
    ),
    migrate = False,
    rname=f'{SCHEMA}.recupero'
)

# TODO: Attivare il versionamento della tabella recupero

# TODO: callback su inserimento numero telefonico che verifichi l'esistenza del
#       numero inserito nella tabella recupero, in caso affermativo il record
#       corrispondente della tabella recuper va rimosso

# db.define_table('componente_log',
#     Field('utente', 'json'),
#     Field('recapito', 'json'),
#     Field('azione'),
#     Field('messagio'),
#     Field(
#         "created_on",
#         "datetime",
#         default=now,
#         writable=False,
#         readable=False,
#         # label=self.param.messages["labels"].get("created_on"),
#     ),
#     Field(
#         "modified_on",
#         "datetime",
#         update=now,
#         default=now,
#         writable=False,
#         readable=False,
#         # label=self.param.messages["labels"].get("modified_on"),
#     ),
#     migrate = False,
#     rname=f'{SCHEMA}.log'
# )
