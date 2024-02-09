# -*- coding: utf-8 -*-

from .. import settings

from ..common import db, Field, logger

from pydal.validators import *
from pydal.validators import Validator, ValidationError

from codicefiscale import codicefiscale
import phonenumbers
import datetime
import string

from ..chiamate.tools import iscrizione_options, LANGUAGES

SCHEMA = "chiamate"

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
            pn = phonenumbers.parse(value, None)
        except phonenumbers.phonenumberutil.NumberParseException:
            pn = phonenumbers.parse(value, "IT")

        try:
            assert phonenumbers.is_valid_number(pn)
        except AssertionError:
            raise ValidationError(
                "Formato numero telefonico non valido"
            )
        else:
            return phonenumbers.format_number(
                pn, phonenumbers.PhoneNumberFormat.E164
            )


db.define_table(
    "utente",
    # TODO: CF validator
    Field(
        "codiceFiscale",
        length=16,
        required=True,
        notnull=True,
        unique=True,
        rname="cf",
    ),
    Field(
        "nome",
        required=True,
        notnull=True,
        requires=[
            IS_NOT_EMPTY(error_message="Valore richiesto"),
            IS_MATCH(
                "^[\D]*$", error_message="Caratteri non validi"
            ),
        ],
    ),
    Field(
        "cognome",
        required=True,
        notnull=True,
        requires=[
            IS_NOT_EMPTY(error_message="Valore richiesto"),
            IS_MATCH(
                "^[\D]*$", error_message="Caratteri non validi"
            ),
        ],
    ),
    Field(
        "dataRegistrazione",
        "date",
        required=True,
        notnull=True,
        default=today,
        requires=IS_DATE(
            format="%Y-%m-%d",
            error_message="Inserire un formato data del tipo: %(format)s",
        ),
        rname="dataregistrazione",
    ),
    Field('iscrizione', required=True, notnull=True, requires=IS_IN_SET(iscrizione_options)),
    Field('vulnerabilitaPersonale', length=2, default='NO', notnull=False,
        label = 'Vulnerabilità personale',
        comment = 'Indica se la persona possiede una vulnerabilità personale',
        requires=IS_EMPTY_OR(IS_IN_SET(['SI', 'NO'])),
        rname = 'vulnerabilitapersonale'
    ),
    # Field('disabilita', 'boolean', default=False, required=True, notnull=True, requires=IS_IN_SET()),
    Field(
        "eMail",
        label="Indirizzo email",
        comment="Email di contatto alternativo ai numeri telefonici",
        requires=IS_EMPTY_OR(IS_EMAIL()),
        rname="email",
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
    Field('is_active', 'boolean',
        writable=False, readable=False, default=True),
    rname=f"{SCHEMA}.utente",
    migrate = db._migrate
)

db.utente._enable_record_versioning()

db.utente.codiceFiscale.requires = requires = [
    IS_NOT_EMPTY(error_message="Valore richiesto"),
    isValidCf(),
    # IS_NOT_IN_DB(db(db.utente), db.utente.codiceFiscale, error_message='Valore nullo o già registrato')
]

db.define_table(
    "contatto",
    Field(
        "numero",
        required=True,
        notnull=True,
        requires=[IS_NOT_EMPTY(error_message="Valore richiesto"), isValidPhoneNumber()],
        rname="telefono",
    ),
    Field(
        "idUtente",
        "reference utente",
        required=True,
        notnull=True,
        requires=IS_IN_DB(db(db.utente), db.utente.id),
        rname="idutente",
    ),
    Field(
        "tipo",
        requires=IS_IN_SET(
            ["FISSO", "CELLULARE"],
            error_message="Valore non permesso, scegliere tra: FISSO, CELLULARE",
        ),
    ),
    Field(
        "lingua",
        requires=IS_IN_SET(
            [
                "BUONA SE IN LINGUA ITALIANA",
                "BUONA SOLO SE IN LINGUA STRANIERA",
                "AUDIOLESO o NON UDENTE",
            ],
            error_message='Valore non permesso, scegliere tra: "BUONA SE IN LINGUA ITALIANA", "BUONA SOLO SE IN LINGUA STRANIERA", "AUDIOLESO o NON UDENTE"',
        ),
    ),
    Field(
        "linguaNoItalia",
        comment="Lingua preferita a quella italiana per il messaggio vocale",
        requires=IS_EMPTY_OR(IS_IN_SET(LANGUAGES)),
        rname="linguanoitalia",
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
    Field('is_active', 'boolean',
        writable=False, readable=False, default=True),
    rname=f"{SCHEMA}.contatto",
    migrate = db._migrate
)

db.contatto._enable_record_versioning()

db.define_table(
    "recapito",
    Field(
        "indirizzoCompleto",
        required=True,
        notnull=True,
        label="Indirizzo completo",
        comment="Indirizzo completo",
        requires=IS_NOT_EMPTY(error_message="Valore richiesto"),
        rname="indirizzocompleto",
    ),
    Field(
        "idVia",
        required=True,
        notnull=True,
        requires=IS_NOT_EMPTY(error_message="Valore richiesto"),
        rname="idvia",
    ),
    Field(
        "numeroCivico",
        length=6,
        required=True,
        notnull=True,
        label="Civico",
        comment="Numero civico",
        requires=IS_NOT_EMPTY(error_message="Valore richiesto"),
        rname="numerocivico",
    ),
    Field(
        "esponente",
        length=1,
        label="Esponente",
        comment="Esponente del civico",
        requires=IS_EMPTY_OR(IS_IN_SET(string.ascii_uppercase)),
    ),
    Field(
        "colore",
        length=1,
        label="Colore",
        comment="Colore del civico",
        requires=IS_EMPTY_OR(IS_IN_SET("NR")),
    ),
    Field(
        "interno",
        length=3,
        label="Interno",
        comment="Interno",
    ),
    Field(
        "internoLettera",
        length=1,
        label="Lettera",
        comment="Lettera dell'interno",
        equires=IS_IN_SET(string.ascii_uppercase),
        rname="internolettera",
    ),
    Field(
        "scala",
        label="Scala",
        comment="Scala",
    ),
    Field(
        "posizione",
        label="posizione",
        comment="Posizione della abitazione rispetto al piano stradale di riferimento",
        requires=IS_EMPTY_OR(IS_IN_SET(["STRADA", "SOTTOSTRADA"])),
    ),
    Field(
        "vulnerabilita",
        label="Vulnerabilità",
        comment="Grado di vulnerabilità",
        requires=IS_EMPTY_OR(
            IS_IN_SET(["SOSTENIBILE", "MATERIALE", "PERSONALE"])
        ),
    ),
    Field(
        "amministratore",
        label="Amministratore",
        comment="Recapito dell'amministratore condominiale",
    ),
    Field(
        "proprietario",
        label="Proprietario",
        comment="Recapito del proprietario",
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
    Field('is_active', 'boolean',
        writable=False, readable=False, default=True),
    rname=f"{SCHEMA}.recapito",
    migrate = db._migrate
)

db.recapito._enable_record_versioning()

db.define_table(
    "nucleo",
    Field(
        "idUtente",
        "reference utente",
        required=True,
        notnull=True,
        requires=IS_IN_DB(db(db.utente), db.utente.id),
        rname="idutente",
    ),
    Field(
        "idCivico",
        "reference recapito",
        required=True,
        notnull=True,
        requires=IS_IN_DB(db(db.recapito), db.recapito.id),
        rname="idcivico",
    ),
    Field(
        "tipo",
        required=True,
        notnull=True,
        label="Ruolo",
        comment="Indica che ruolo ha la persona all'interno del nucleo abitativo",
        requires=[
            IS_NOT_EMPTY(error_message="Valore richiesto"),
            IS_IN_SET(
                ["CAPO FAMIGLIA", "RESIDENTE", "NON RESIDENTE"]
            ),
        ],
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
    Field('is_active', 'boolean',
        writable=False, readable=False, default=True),
    rname=f"{SCHEMA}.componente",
    migrate = db._migrate
)

db.nucleo._enable_record_versioning()

db.define_table(
    "recupero",
    Field(
        "nome",
        required=True,
        notnull=True,
        requires=[
            IS_NOT_EMPTY(error_message="Valore richiesto"),
            IS_MATCH(
                "^[\D]*$", error_message="Caratteri non validi"
            ),
        ],
    ),
    Field(
        "cognome",
        required=True,
        notnull=True,
        requires=[
            IS_NOT_EMPTY(error_message="Valore richiesto"),
            IS_MATCH(
                "^[\D]*$", error_message="Caratteri non validi"
            ),
        ],
    ),
    Field(
        "numero",
        required=True,
        notnull=True,
        requires=isValidPhoneNumber(),
        rname="telefono",
    ),
    Field("indirizzoCompleto", rname="indirizzocompleto"),
    Field(
        "numeroCivico",
        length=20,
        required=True,
        notnull=True,
        label="Civico",
        comment="Numero civico",
        requires=IS_NOT_EMPTY(error_message="Valore richiesto"),
        rname="numerocivico",
    ),
    Field(
        "gruppo",
        length=1,
        required=True,
        notnull=True,
        requires=IS_IN_SET(["1", "2", "3"]),
    ),
    Field('residenza', 'boolean'),
    Field('codice_fiscale'),
    Field(
        "is_active",
        "boolean",
        writable=False,
        readable=False,
        default=True,
    ),
    migrate = db._migrate,
    rname=f"{SCHEMA}.recupero",
)

# Vista soggetti vulnerabili validi

db.define_table(
    "soggetti_vulnerabili",
    Field("id"),
    Field("nome"),
    Field("cognome"),
    Field("telefono"),
    Field("indirizzo", rname="indirizzocompleto"),
    Field("numero_civico", rname="numerocivico"),
    Field("gruppo"),
    Field("sorgente"),
    Field("validita"),
    primarykey=["id"],
    migrate = db._migrate,
    rname=f"{SCHEMA}.soggetti_vulnerabili_validi",
)

db.recupero._enable_record_versioning()

# DONE: callback su inserimento numero telefonico che verifichi l'esistenza del
#       numero inserito nella tabella recupero, in caso affermativo il record
#       corrispondente della tabella recupero va rimosso
def foo(f, i):
    """
    Nel caso in cui attraverso le API venga registrato un telefono della tabella
    recupero questo viene disattivato dalla stessa tabella e gestito normalmente
    """

    def bar(numero, **__):
        tel, err = db.recupero.numero.requires(numero)
        nn = db(db.recupero.numero == tel).delete()
        logger.debug(f"Removed records: {nn}")

    return bar(**f)


db.contatto._after_insert.append(foo)


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
