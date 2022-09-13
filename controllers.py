"""
This file defines actions, i.e. functions the URLs are mapped into
The @action(path) decorator exposed the function at URL:

    http://127.0.0.1:8000/{app_name}/{path}

If app_name == '_default' then simply

    http://127.0.0.1:8000/{path}

If path == 'index' it can be omitted:

    http://127.0.0.1:8000/

The path follows the bottlepy syntax.

@action.uses('generic.html')  indicates that the action uses the generic.html template
@action.uses(session)         indicates that the action uses the session
@action.uses(db)              indicates that the action uses the db
@action.uses(T)               indicates that the action uses the i18n & pluralization
@action.uses(auth.user)       indicates that the action requires a logged in user
@action.uses(auth)            indicates that the action requires the auth object

session, db, T, auth, and tempates are examples of Fixtures.
Warning: Fixtures MUST be declared with @action.uses({fixtures}) else your app will result in undefined behavior
"""

from datetime import datetime
from pprint import pp
from py4web import action, request, abort, redirect, URL, Field
from yatl.helpers import A
from .common import (
    session,
    T,
    cache,
    auth,
    logger,
    authenticated,
    unauthenticated,
    flash,
    db,
    cors,
    alertsystem_config,
)

from py4web.utils.form import Form
from pydal.validators import *
from pydal.validators import Validator

from . import evento as _evento
from . import civico as _civico
from . import segnalazione as _segnalazione

from mptools.frameworks.py4web import shampooform as sf

import geojson, json

# from .segnalazione import comunicazione as _comunicazione
from . import segnalazione
from . import presidio_mobile as squadra

from .incarico import incarico
from . import mire
from . import settings

from alertsystem import model
from alertsystem.azioni import do as alert_do
from pprint import pformat, pprint
from py4web.core import HTTP


class NoDBIO(object):
    """TEST/DEBUG HELPER"""

    def __init__(self, form):
        super(NoDBIO, self).__init__()
        self.form = form
        self.rollback = False

    def __enter__(self):
        if "rollback" in self.form.vars:
            self.rollback = True
            self.form.vars.pop("rollback")
        # return self

    def __exit__(self, exc_type, exc_value, traceback):
        if self.rollback:
            # Modalità test del form. In questo modo il DB non viene aggiornato
            db.rollback()


# ? variable to pass ID
id_message_global: int = -1
# from py4web.core import Fixture
#
# class query2forms(Fixture):
#     def on_request(self):
#         if request.query:
#             for k in dict(request.query):
#                 request.forms[k] = request.query.pop(k)


# @unauthenticated("index", "index.html")
# def index():
#     user = auth.get_user()
#     message = T("Hello {first_name}".format(**user) if user else "Hello")
#     return dict(message=message)
def general_error_message(
    form: Form,
    error_message: str = "Bad Request",
    error_code: int = 400,
):
    if form == None:
        return {
            "error_code": error_code,
            "error_message": error_message,
        }
    error_body = sf.form2dict(form)
    error_body["error_status"] = f"{error_code} {error_message}"
    raise HTTP(
        status=error_code,
        body=json.dumps(error_body),
        headers={"Content-Type": "application/json"},
    )


@action("evento")
@action.uses(cors)
def evento():
    return {"result": _evento.fetch()}


@action("indirizzo", method=["GET", "POST"])
@action("indirizzo.<format>", method=["GET", "POST"])
@action("ricerca_indirizzo", method=["GET", "POST"])
@action("ricerca_indirizzo.<format>", method=["GET", "POST"])
@action("RicercaCivico", method=["GET", "POST"])
@action("RicercaCivico.<format>", method=["GET", "POST"])
@action.uses(cors)
def civico(format=None):

    db.civico.desvia.comment = "Cerca per toponimo"
    res = (
        db(db.civico)
        .select(
            db.civico.numero.min().with_alias("nummin"),
            db.civico.numero.max().with_alias("nummax"),
            db.civico.cap.min().with_alias("capmin"),
            db.civico.cap.max().with_alias("capmax"),
            db.civico.codvia.min().with_alias("codmin"),
            db.civico.codvia.max().with_alias("codmax"),
        )
        .first()
    )

    db.civico.codvia.requires = IS_EMPTY_OR(
        IS_INT_IN_RANGE(int(res.codmin), int(res.codmax))
    )

    # db.civico.colore.requires = IS_IN_DB(db(db.civico), db.civico.colore, distinct=True)
    db.civico.colore.requires = IS_EMPTY_OR(
        IS_IN_SET([("", "Nero"), ("R", "Rosso"), ("r", "Rosso")])
    )
    db.civico.lettera.requires = IS_LENGTH(1)

    form = Form(
        [
            db.civico.codvia,
            db.civico.desvia,
            Field(
                db.civico.numero.name,
                "integer",
                label=db.civico.numero.label,
                comment=f"Numero compreso tra {int(res.nummin):d} e {int(res.nummax):d}",
                length=db.civico.numero.length,
                requires=IS_EMPTY_OR(
                    IS_INT_IN_RANGE(int(res.nummin), int(res.nummax))
                ),
            ),
            db.civico.lettera,
            db.civico.colore,
            Field(
                db.civico.cap.name,
                "integer",
                label=db.civico.cap.label,
                comment=f"Numero compreso tra {int(res.capmin):d} e {int(res.capmax):d}",
                length=db.civico.cap.length,
                requires=IS_EMPTY_OR(
                    IS_INT_IN_RANGE(int(res.capmin), int(res.capmax))
                ),
            ),
            Field(
                "lon",
                "double",
                label="Longitude",
                requires=IS_EMPTY_OR(
                    IS_FLOAT_IN_RANGE(-180.0, 180.0)
                ),
            ),
            Field(
                "lat",
                "double",
                label="Latitude",
                requires=IS_EMPTY_OR(IS_FLOAT_IN_RANGE(-90.0, 90.0)),
            ),
            # Field('epsg', )
            Field(
                "page",
                "integer",
                default=0,
                requires=IS_EMPTY_OR(IS_INT_IN_RANGE()),
            ),
            Field(
                "paginate",
                "integer",
                default=10,
                requires=IS_EMPTY_OR(IS_INT_IN_RANGE()),
            ),
        ],
        deletable=False,
        dbio=False,
        form_name="civico",
        csrf_protection=False,
    )
    result = None
    if form.accepted:

        for field in filter(
            lambda ff: not ff.name in form.errors, form.table
        ):
            if form.vars[field.name] is None:
                if not field.default is None:
                    form.vars[field.name] = field.default

        lon = form.vars.pop("lon")
        lat = form.vars.pop("lat")
        if not None in (
            lon,
            lat,
        ):
            form.vars["near_by"] = geojson.Point([lon, lat])
        result = _civico.fetch(
            **form.vars, as_geojson=(format == "geojson")
        )

    if format == "geojson":
        return {
            "result": geojson.FeatureCollection(result),
            "form": sf.form2dict(form),
        }
    else:
        return {"result": result, "form": sf.form2dict(form)}


@action("fetch/segnalazione/<id:int>")
@action.uses(cors)
def fetch_segnalazione(id):
    return {"result": _segnalazione.fetch(id)}


@action("segnalazione", method=["GET", "POST"])
@action("crea_segnalazione", method=["GET", "POST"])
@action("crea/segnalazione", method=["GET", "POST"])
@action("CreaSegnalazione", method=["GET", "POST"])
@action("intervento", method=["GET", "POST"])
@action.uses(db)
def ws_segnalazione():
    """ """

    res = (
        db(db.evento)
        .select(
            db.evento.id.min().with_alias("idmin"),
            db.evento.id.max().with_alias("idmax"),
        )
        .first()
    )

    civ_stats = (
        db(db.civico)
        .select(
            db.civico.id.min().with_alias("idmin"),
            db.civico.id.max().with_alias("idmax"),
        )
        .first()
    )

    db.segnalante.tipo_segnalante_id.comment = f"Inserire un corretto id per tipo segnalante se diverso da: {db.tipo_segnalante[_segnalazione.DEFAULT_TIPO_SEGNALANTE].descrizione}"
    db.segnalante.tipo_segnalante_id.default = (
        _segnalazione.DEFAULT_TIPO_SEGNALANTE
    )
    db.segnalante.tipo_segnalante_id.requires = IS_EMPTY_OR(
        db.segnalante.tipo_segnalante_id.requires,
        null=db.segnalante.tipo_segnalante_id.default,
    )

    db.segnalazione.criticita_id.requires = IS_IN_DB(
        db(
            (db.tipo_criticita.valido == True)
            & ~db.tipo_criticita.id.belongs([7, 12])
        ),
        db.tipo_criticita.id,
        label=db.tipo_criticita.descrizione,
        orderby=db.tipo_criticita.descrizione,
        zero=None,
    )

    db.intervento.intervento_id.requires = [
        IS_NOT_EMPTY(),
        IS_NOT_IN_DB(db(db.intervento), db.intervento.intervento_id),
    ]
    db.intervento.intervento_id.comment = (
        "Inserire un nuovo identificativo di intervento Verbatel"
    )

    db.stato_incarico.stato_id.default = 2  # Preso in carico

    db.stato_incarico.stato_id.requires = IS_EMPTY_OR(
        IS_IN_DB(
            db(
                (db.tipo_stato_incarico.valido != False)
                & db.tipo_stato_incarico.id.belongs([1, 2])
            ),
            db.tipo_stato_incarico.id,
            db.tipo_stato_incarico.descrizione,
            zero=None,
        )
    )

    if not "stato_id" in request.POST:
        request.POST["stato_id"] = db.stato_incarico.stato_id.default

    form = Form(
        [
            db.intervento.intervento_id,
            Field(
                "evento_id",
                "integer",
                label="Id Evento",
                required=True,
                comment=f"Inserisci un id Evento valido compreso tra {res.idmin} e {res.idmax}",
                requires=IS_INT_IN_RANGE(res.idmin, res.idmax + 1),
            ),
            Field(
                "nome",
                label="Nome segnalante",
                comment="Inserire nome e cognome",
                required=True,
                requires=IS_NOT_EMPTY(),
            ),
            Field(
                "descrizione", required=True, requires=IS_NOT_EMPTY()
            ),
            Field(
                "lon",
                "double",
                label="Longitudine",
                requires=IS_FLOAT_IN_RANGE(-180.0, 180.0),
            ),
            Field(
                "lat",
                "double",
                label="Latitudine",
                requires=IS_FLOAT_IN_RANGE(-90.0, 90.0),
            ),
            db.segnalazione.criticita_id,
            # Field('criticita_id', label='Id Criticità',
            #     comment='Scegli il tipo di criticità da segnalare',
            #     requires = IS_IN_DB(
            #         db((db.tipo_criticita.valido==True) & ~db.tipo_criticita.id.belongs([7,12])),
            #         db.tipo_criticita.id, label=db.tipo_criticita.descrizione,
            #         orderby=db.tipo_criticita.descrizione
            #     )
            # ),
            db.segnalazione.operatore,  # TODO: Introdurre validazione (CF valido o matricola in db)
            db.segnalante.tipo_segnalante_id,
            db.segnalante.telefono,
            db.segnalante.note,
            # db.segnalazione.nverde,
            Field(
                "nverde",
                label=db.segnalazione.nverde.label,
                comment=db.segnalazione.nverde.comment,
                required=True,
                requres=IS_IN_SET(["True", "False"], zero=None),
            ),
            Field(
                "note_geo",
                label=db.segnalazione.note.label,
                comment=db.segnalazione.note.comment,
            ),
            Field(
                "civico_id",
                "integer",
                label="Id civico",
                comment=f"Inserisci un Id civico valido compreso tra {civ_stats.idmin:d} e {civ_stats.idmax:d}",
                length=db.civico.id.length,
                requires=IS_EMPTY_OR(
                    IS_INT_IN_RANGE(
                        int(civ_stats.idmin),
                        int(civ_stats.idmax) + 1,
                    )
                ),
            ),
            # Field('persone_a_rischio', 'boolean',
            #     label = db.segnalazione.rischio.label,
            #     comment = db.segnalazione.rischio.comment
            # ),
            Field(
                "persone_a_rischio",
                label=db.segnalazione.rischio.label,
                comment=db.segnalazione.rischio.comment,
                required=True,
                requires=IS_IN_SET(["True", "False"], zero=None),
            ),
            Field(
                "tabella_oggetto_id",
                label="Seleziona la tabella degli oggetti a rischio",
                requires=IS_EMPTY_OR(
                    IS_IN_DB(
                        db(db.tipo_oggetto_rischio),
                        db.tipo_oggetto_rischio.id,
                        label=db.tipo_oggetto_rischio.descrizione,
                        orderby=db.tipo_oggetto_rischio.descrizione,
                    )
                ),
            ),
            Field(
                "note_riservate",
                label=db.segnalazione_riservata.testo.label,
                comment=db.segnalazione_riservata.testo.comment,
            ),
            db.segnalante.telefono,
            db.stato_incarico.stato_id,
            db.incarico.preview,
            Field(
                "ceduta",
                label="Se presente indica la segnalazione come NON in carico a PM",
                required=True,
                requires=IS_IN_SET(["True", "False"], zero=None),
            ),
            Field(
                "parziale",
                label=db.stato_incarico.parziale.label,
                comment=db.stato_incarico.parziale.comment,
                required=True,
                requires=IS_IN_SET(["True", "False"], zero=None),
            )
            # Field('ceduta', 'boolean',
            #     label = """Se presente indica la segnalazione come NON in carico a PM,
            #     in questo caso non viene associato nessun incarico/intervento,
            #     quindi nessun id incarico verrà restituito.
            #     Sarà quindi cura dell'ente che prenderà la lavorazione assegnare gli incarichi.
            #     """
            # )
        ],
        hidden={"rollback": False},
        validation=_segnalazione.valida_nuova_segnalazione,
        deletable=False,
        dbio=False,
        form_name="civico",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            lon = form.vars.pop("lon")
            lat = form.vars.pop("lat")
            form.vars["lon_lat"] = (
                lon,
                lat,
            )
            form.vars["assegna"] = form.vars.pop("ceduta") == "False"
            form.vars["persone_a_rischio"] = (
                form.vars.pop("persone_a_rischio") == "True"
            )
            form.vars["nverde"] = form.vars.pop("nverde") == "True"
            form.vars["parziale"] = (
                form.vars.pop("parziale") == "True"
            )
            result = _segnalazione.verbatel_create(**form.vars)

    return {"result": result, "form": sf.form2dict(form)}


def segnalazione_form():

    db.segnalante.tipo_segnalante_id.comment = f"Inserire un corretto id per tipo segnalante se diverso da: {db.tipo_segnalante[_segnalazione.DEFAULT_TIPO_SEGNALANTE].descrizione}"
    db.segnalante.tipo_segnalante_id.default = (
        _segnalazione.DEFAULT_TIPO_SEGNALANTE
    )
    db.segnalante.tipo_segnalante_id.requires = IS_EMPTY_OR(
        db.segnalante.tipo_segnalante_id.requires,
        null=db.segnalante.tipo_segnalante_id.default,
    )

    db.segnalazione.criticita_id.required = False
    db.segnalazione.criticita_id.requires = IS_EMPTY_OR(
        IS_IN_DB(
            db(
                (db.tipo_criticita.valido == True)
                & ~db.tipo_criticita.id.belongs([7, 12])
            ),
            db.tipo_criticita.id,
            label=db.tipo_criticita.descrizione,
            orderby=db.tipo_criticita.descrizione,
        )
    )

    return [
        Field(
            "nome",
            label="Nome segnalante",
            comment="Inserire nome e cognome",
            required=True,
        ),
        db.segnalante.telefono,
        db.segnalazione.operatore,  # TODO: Introdurre validazione (CF valido o matricola in db ??? )
        db.segnalante.note,
        Field(
            "lon",
            "double",
            label="Longitudine",
            requires=IS_FLOAT_IN_RANGE(-180.0, 180.0),
        ),
        Field(
            "lat",
            "double",
            label="Latitudine",
            requires=IS_FLOAT_IN_RANGE(-90.0, 90.0),
        ),
        # db.segnalante.tipo_segnalante_id,
        Field("descrizione", required=True, requires=IS_NOT_EMPTY()),
        Field(
            "note_geo",
            label=db.segnalazione.note.label,
            comment=db.segnalazione.note.comment,
        ),
        db.segnalazione.criticita_id,
        Field(
            "persone_a_rischio",
            label=db.segnalazione.rischio.label,
            comment=db.segnalazione.rischio.comment,
            required=True,
            requires=IS_IN_SET(["True", "False"], zero=None),
        ),
        # Field('persone_a_rischio', 'boolean',
        #     label = db.segnalazione.rischio.label,
        #     comment = db.segnalazione.rischio.comment
        # ),
    ]


def incarico_form():

    return [
        # db.incarico.uo_id,
        db.incarico.preview,
        db.incarico.start,
        db.incarico.stop,
        # db.incarico.note,
        db.incarico.rifiuto,
        db.stato_incarico.stato_id,
        # db.stato_incarico.parziale
        Field(
            "parziale",
            label=db.stato_incarico.parziale.label,
            comment=db.stato_incarico.parziale.comment,
            required=True,
            requires=IS_IN_SET(["True", "False"], zero=None),
        ),
    ]


@action("modifica/segnalazione", method=["GET", "POST"])
@action("ModificaSegnalazione", method=["GET", "POST"])
@action("modifica_segnalazione", method=["GET", "POST"])
@action("segnalazione/<segnalazione_id:int>", method=["GET", "POST"])
@action.uses(db)
def modifica_segnalazione(segnalazione_id=None):
    """DEPRECATO ?!?"""

    if not segnalazione_id is None:
        request.POST["segnalazione_id"] = segnalazione_id

    # TODO: Limitare la modifica alle segnalazioni di PM
    # (in validazione di segnalazione_id??!!)

    res = (
        db(db.segnalazione)
        .select(
            db.segnalazione.id.min().with_alias("idmin"),
            db.segnalazione.id.max().with_alias("idmax"),
        )
        .first()
    )

    form = Form(
        [
            Field(
                "segnalazione_id",
                "integer",
                label="Id Segnalazione",
                required=True,
                comment=f"Inserisci un id Segnalazione valido compreso tra {res.idmin} e {res.idmax}",
                requires=IS_INT_IN_RANGE(res.idmin, res.idmax + 1),
            )
        ]
        + segnalazione_form(),
        deletable=False,
        dbio=False,
        validation=_segnalazione.valida_segnalazione,
        hidden={"rollback": False},
        form_name="modifica_segnalazione",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            # Rimuovo le variabili non espresamente passate nella request
            for ff in form.table:
                if not ff.required and form.vars[ff.name] is None:
                    form.vars.pop(ff.name)
            lon = form.vars.pop("lon")
            lat = form.vars.pop("lat")
            form.vars["lon_lat"] = (
                lon,
                lat,
            )
            form.vars["persone_a_rischio"] = (
                form.vars.pop("persone_a_rischio") == "True"
            )
            result = _segnalazione.update(**form.vars)

    return {"result": result, "form": sf.form2dict(form)}


@action("modifica/intervento", method=["GET", "POST"])
@action("ModificaIntervento", method=["GET", "POST"])
@action("modifica_intervento", method=["GET", "POST"])
@action("intervento/<intervento_id:int>", method=["GET", "POST"])
@action.uses(db)
def modifica_intervento(intervento_id=None):

    if not intervento_id is None:
        request.POST["intervento_id"] = intervento_id

    intervento_info = (
        db(db.intervento)
        .select(
            db.intervento.intervento_id.min().with_alias("idmin"),
            db.intervento.intervento_id.max().with_alias("idmax"),
        )
        .first()
    )

    db.intervento.intervento_id.requires = IS_INT_IN_RANGE(
        intervento_info.idmin, intervento_info.idmax + 1
    )

    db.stato_incarico.stato_id.requires = IS_EMPTY_OR(
        IS_IN_DB(
            db(
                (db.tipo_stato_incarico.valido != False)
                & db.tipo_stato_incarico.id.belongs([1, 2, 3, 4])
            ),
            db.tipo_stato_incarico.id,
            db.tipo_stato_incarico.descrizione,
            zero=None,
        )
    )

    form = Form(
        [db.intervento.intervento_id]
        + segnalazione_form()
        + incarico_form(),
        deletable=False,
        dbio=False,
        validation=_segnalazione.valida_intervento,
        hidden={"rollback": False},
        form_name="modifica_intervento",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            # Rimuovo le variabili non espresamente passate nella request
            for ff in form.table:
                if not ff.required and form.vars[ff.name] is None:
                    form.vars.pop(ff.name)
            lon = form.vars.pop("lon")
            lat = form.vars.pop("lat")
            form.vars["lon_lat"] = (
                lon,
                lat,
            )
            form.vars["persone_a_rischio"] = (
                form.vars.pop("persone_a_rischio") == "True"
            )
            form.vars["parziale"] = (
                form.vars.pop("parziale") == "True"
            )
            result = _segnalazione.verbatel_update(**form.vars)

    return {"result": result and "Ok", "form": sf.form2dict(form)}


@action("comunicazione", method=["GET", "POST"])
@action("crea_comunicazione", method=["GET", "POST"])
@action("crea/comunicazione", method=["GET", "POST"])
@action("CreaComunicazione", method=["GET", "POST"])
@action(
    "segnalazione/incarico/comunicazione", method=["GET", "POST"]
)
@action("incarico/comunicazione", method=["GET", "POST"])
@action(
    "comunicazione/incarico/<incarico_id:int>",
    method=["GET", "POST"],
)
@action.uses(db)
def segnalazione_comunicazione_da_incarico(incarico_id=None):
    """ """

    if not incarico_id is None:
        request.POST["incarico_id"] = incarico_id

    # res = db(db.segnalazioni_utili).select(
    #     db.segnalazioni_utili.id.min().with_alias('idmin'),
    #     db.segnalazioni_utili.id.max().with_alias('idmax')
    # ).first()

    stat_incarico = (
        db(db.incarico)
        .select(
            db.incarico.id.min().with_alias("idmin"),
            db.incarico.id.max().with_alias("idmax"),
        )
        .first()
    )
    db.comunicazione.mittente.requires = IS_NOT_EMPTY()

    form = Form(
        [
            Field(
                "incarico_id",
                "integer",
                label="Id Incarico",
                required=True,
                comment=f"Inserisci un id Incarico valido compreso tra {stat_incarico.idmin} e {stat_incarico.idmax}",
            ),
            *segnalazione.comunicazione.comunicazione_fields,
        ],
        deletable=False,
        dbio=False,
        hidden={"rollback": False},
        validation=segnalazione.comunicazione.valida_nuova_comunicazione,
        form_name="crea_comunicazione",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            result = segnalazione.comunicazione.create_by_incarico(
                **form.vars
            )

    output = {"result": result, "form": sf.form2dict(form)}
    if (
        not segnalazione.comunicazione.UPLOAD_CONFIGURED
        and not form.errors
        and "allegato" in form.vars
    ):
        output[
            "message"
        ] = "ATTENZIONE! L'allegato non è stato salvato perché non è ancora configurato il percorso per l'upload."

    return output


@action(
    "segnalazione/intervento/comunicazione", method=["GET", "POST"]
)
@action("intervento/comunicazione", method=["GET", "POST"])
@action(
    "comunicazione/intervento/<intervento_id:int>",
    method=["GET", "POST"],
)
@action.uses(db)
def segnalazione_comunicazione_da_intervento(intervento_id=None):
    """ """

    if not intervento_id is None:
        request.POST["intervento_id"] = intervento_id

    stat_intervento = (
        db(db.intervento)
        .select(
            db.intervento.id.min().with_alias("idmin"),
            db.intervento.id.max().with_alias("idmax"),
        )
        .first()
    )
    db.comunicazione.mittente.requires = IS_NOT_EMPTY()

    form = Form(
        [
            # Field('segnalazione_id', 'integer', label='Id Segnalazione', required=True,
            #     comment = f'Inserisci un id Segnalazione valido compreso tra {res.idmin} e {res.idmax}',
            #     requires = IS_INT_IN_RANGE(res.idmin, res.idmax+1)
            # ),
            Field(
                "intervento_id",
                "integer",
                label="Id Incarico",
                required=True,
                comment=f"Inserisci un id Incarico valido compreso tra {stat_intervento.idmin} e {stat_intervento.idmax}",
            ),
            *segnalazione.comunicazione.comunicazione_fields,
        ],
        deletable=False,
        dbio=False,
        hidden={"rollback": False},
        validation=segnalazione.comunicazione.valida_nuova_comunicazione_da_intervento,
        form_name="crea_comunicazione",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            result = segnalazione.comunicazione.create_by_intervento(
                **form.vars
            )

    output = {"result": result, "form": sf.form2dict(form)}
    if (
        not segnalazione.comunicazione.UPLOAD_CONFIGURED
        and not form.errors
        and "allegato" in form.vars
    ):
        output[
            "message"
        ] = "ATTENZIONE! L'allegato non è stato salvato perché non è ancora configurato il percorso per l'upload."

    return output


# TODO: Parte work in progress

# PREFIX = 'c'
#
# componente_form = lambda num=0: [
#     # Field('{}')
# ]

uo_value = "concat('com_','PO' || codice_mun::text)"  # 'PO'::text || m.codice_mun::text AS cod,
uo_label = "'Distretto ' || codice_mun::text"


@action("presidio", method=["GET", "POST"])
@action("pattuglia", method=["GET", "POST"])
@action.uses(db)
def ws_presidio():
    """ """

    res = (
        db(db.evento)
        .select(
            db.evento.id.min().with_alias("idmin"),
            db.evento.id.max().with_alias("idmax"),
        )
        .first()
    )

    db.squadra.evento_id.comment = f"Inserisci un id Evento valido compreso tra {res.idmin} e {res.idmax}"
    db.squadra.evento_id.requires = IS_INT_IN_RANGE(
        res.idmin, res.idmax + 1
    )

    unita_operative = map(
        lambda row: (
            row[uo_value],
            row[uo_label],
        ),
        db(db.municipio)(db.profilo_utilizatore.id == 6).iterselect(
            uo_value, uo_label
        ),
    )
    db.squadra.afferenza.requires = IS_IN_SET(
        list(unita_operative), zero=None
    )

    db.squadra.stato_id.required = False
    db.squadra.stato_id.requires = IS_EMPTY_OR(
        db.squadra.stato_id.requires
    )

    form = Form(
        [
            db.pattuglia_pm.pattuglia_id,
            Field(
                "componenti",
                "json",
                label="Componenti squadra",
                default="[]",
                comment='Es.: [{"matricola": "MRARSS80A01H501T", "nome": "Mario", "cognome": "Rossi", "telefono": "1234", "email": "mario.rossi@foo.it"}]',
                requires=squadra.IS_JSON_LIST_OF_COMPONENTI(),
            ),
            Field(
                "percorso",
                label="Percorso",
                comment="Scegliere un percorso",
                required=False,
                requires=IS_EMPTY_OR(
                    IS_IN_DB(
                        db(db.presidi_mobili),
                        db.presidi_mobili.percorso,
                        zero=None,
                    )
                ),
            ),
            db.squadra.nome,
            db.squadra.evento_id,
            db.squadra.stato_id,
            db.squadra.afferenza,
            db.presidio.preview,
            db.presidio.start,
            # db.presidio.stop
        ],
        deletable=False,
        dbio=False,
        validation=squadra.squadra.valida_nuova_pattuglia,
        hidden={"rollback": False},
        form_name="crea_presidio",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            if form.vars["percorso"] is None:
                form.vars["percorso"] = "A1"
            else:
                form.vars["descrizione"] = form.vars["percorso"]
            # if form.vars['stato_id'] is None:
            #     form.vars['stato_id'] = db.squadra.stato_id.default
            result = squadra.squadra.create(**form.vars)

    output = {"result": result, "form": sf.form2dict(form)}

    return output


@action("pattuglia/<pattuglia_id:int>", method=["GET", "POST"])
@action("modifica/pattuglia/", method=["GET", "POST"])
@action(
    "modifica/pattuglia/<pattuglia_id:int>", method=["GET", "POST"]
)
@action("ModificaPattuglia", method=["GET", "POST"])
@action(
    "ModificaPattuglia/<pattuglia_id:int>", method=["GET", "POST"]
)
@action("modifica_pattuglia", method=["GET", "POST"])
@action(
    "modifica_pattuglia/<pattuglia_id:int>", method=["GET", "POST"]
)
@action("modifica/presidio/", method=["GET", "POST"])
@action(
    "modifica/presidio/<pattuglia_id:int>", method=["GET", "POST"]
)
@action("ModificaPresidio", method=["GET", "POST"])
@action(
    "ModificaPresidio/<pattuglia_id:int>", method=["GET", "POST"]
)
@action("modifica_presidio", method=["GET", "POST"])
@action(
    "modifica_presidio/<pattuglia_id:int>", method=["GET", "POST"]
)
@action.uses(db)
def ws_presidio_update(pattuglia_id=None):

    db.pattuglia_pm.pattuglia_id.requires = None

    stat_pattuglia = (
        db(db.pattuglia_pm)
        .select(
            db.pattuglia_pm.pattuglia_id.min().with_alias("idmin"),
            db.pattuglia_pm.pattuglia_id.max().with_alias("idmax"),
        )
        .first()
    )

    db.pattuglia_pm.pattuglia_id.comment = f"Inserisci un id pattuglia valido compreso tra {stat_pattuglia.idmin or 0} e {stat_pattuglia.idmax or 0}"

    if not pattuglia_id is None:
        request.POST["pattuglia_id"] = pattuglia_id

    form = Form(
        [
            db.pattuglia_pm.pattuglia_id,
            db.presidio.preview,
            db.presidio.start,
            db.presidio.stop,
        ],
        deletable=False,
        dbio=False,
        hidden={"rollback": False},
        form_name="aggiorna_presidio",
        validation=squadra.squadra.valida_pattuglia,
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            result = squadra.squadra.update_by_pattuglia_pm_id(
                **form.vars
            )

    output = {"result": result, "form": sf.form2dict(form)}

    return output


@action("comunicazione/presidio", method=["GET", "POST"])
@action(
    "comunicazione/presidio/<presidio_id:int>",
    method=["GET", "POST"],
)
@action.uses(db)
def segnalazione_comunicazione_a_presidio(presidio_id=None):
    """ """

    if not presidio_id is None:
        request.POST["presidio_id"] = presidio_id

    # stat_presidio = db(db.intervento).select(
    #     db.intervento.id.min().with_alias('idmin'),
    #     db.intervento.id.max().with_alias('idmax')
    # ).first()

    form = Form(
        [
            Field(
                "pattuglia_id",
                "integer",
                label="Id Presidio",
                required=True,
                comment="Inserisci un id Presidio valido"
                # comment = f'Inserisci un id Incarico valido compreso tra {stat_presidio.idmin} e {stat_presidio.idmax}',
            ),
            *squadra.comunicazione.comunicazione_fields,
        ],
        deletable=False,
        dbio=False,
        hidden={"rollback": False},
        validation=squadra.comunicazione.valida_nuova_comunicazione,
        form_name="crea_comunicazione_a_presidio",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            result = squadra.comunicazione.create(**form.vars)
            pass

    output = {"result": result, "form": sf.form2dict(form)}
    if (
        not segnalazione.comunicazione.UPLOAD_CONFIGURED
        and not form.errors
        and "allegato" in form.vars
    ):
        output[
            "message"
        ] = "ATTENZIONE! L'allegato non è stato salvato perché non è ancora configurato il percorso per l'upload."

    return output


@action("lista/segnalazioni", method=["GET", "POST"])
def segnalazioni():

    form = Form(
        [
            Field(
                "status",
                "integer",
                requires=IS_EMPTY_OR(segnalazione.state_validation),
            ),
            Field(
                "start",
                "datetime",
                requires=IS_EMPTY_OR(
                    IS_DATETIME(format="%Y-%m-%d %H:%M")
                ),
            ),
            Field(
                "end",
                "datetime",
                requires=IS_EMPTY_OR(
                    IS_DATETIME(format="%Y-%m-%d %H:%M")
                ),
            ),
            Field(
                "page",
                "integer",
                requires=IS_EMPTY_OR(IS_INT_IN_RANGE(1, None)),
            ),
            Field(
                "paginate",
                "integer",
                requires=IS_EMPTY_OR(
                    IS_IN_SET([5, 10, 20, 50], zero=None)
                ),
            ),
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="segnalazioni",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            result = segnalazione.fetch(**form.vars)

    return {
        "result": result,
        "results": result and len(result),
        "form": sf.form2dict(form),
    }


@action("lista/mire", method=["GET", "POST"])
def lista_mire():

    form = Form(
        [
            # Field('start', 'datetime', requires=IS_EMPTY_OR(IS_DATETIME(format="%Y-%m-%d %H:%M"))),
            # Field('end', 'datetime', requires=IS_EMPTY_OR(IS_DATETIME(format="%Y-%m-%d %H:%M"))),
            Field(
                "page",
                "integer",
                requires=IS_EMPTY_OR(IS_INT_IN_RANGE(0, None)),
            ),
            Field(
                "paginate",
                "integer",
                requires=IS_EMPTY_OR(
                    IS_IN_SET([5, 10, 20, 50, 100], zero=None)
                ),
            ),
        ],
        deletable=False,
        dbio=False,
        form_name="mire",
        csrf_protection=False,
    )

    result = None
    if form.accepted:
        if not form.vars.get("paginate"):
            form.vars["paginate"] = mire.DEFAULT_PAGINATION
        result = mire.fetch(**form.vars)
        with NoDBIO(form):
            if not form.vars.get("paginate"):
                form.vars["paginate"] = mire.DEFAULT_PAGINATION
            result = mire.fetch(**form.vars)
    return {
        "result": result,
        "results": result and len(result),
        "form": sf.form2dict(form),
    }


# ?------------------------------------------------------------
# ?------------------------------------------------------------
# ?------------------------------------------------------------

##// TODO Message url for putting message
##// TODO POST via postman message on url
##// TODO get data from url
##// TODO make a query to DB
##// TODO recieve DB data
##// TODO generate campaign

# TODO managment of HTTP raise errors
# // TODO check validator for the datetime
@action("user_campaign/_get_campaign_from_to", method=["POST"])
def user_campaign_get_campaign_from_to():
    """user_campaign_get_campaign_from_to _summary_

    Returns
    -------
    _type_
        _description_
    """
    form = Form(
        [
            Field(
                "date_start",
                "datetime",
                requires=IS_EMPTY_OR(IS_DATETIME("%Y-%m-%d %H:%M")),
            ),
            Field(
                "date_end",
                "datetime",
                requires=IS_EMPTY_OR(IS_DATETIME("%Y-%m-%d %H:%M")),
            ),
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="_get_campaign_from_to",
        csrf_protection=False,
    )
    logger.debug(
        f"tuple_of_campaigns: {pformat(form, indent=4, width=1)}"
    )
    if form.accepted:
        date_start: datetime = form.vars.get("date_start")
        date_end: datetime = form.vars.get("date_end")
        logger.debug(
            f"date_start: {date_start} and date_end: {date_end}"
        )
        # ? alert_do.ricerca_campagne is using strftime to convert str to datetime so str mmust be passed
        (
            tuple_of_campaigns,
            alertsystem_response_status,
        ) = alert_do.ricerca_campagne(
            cfg=alertsystem_config,
            start_date=date_start,
            end_date=date_end,
        )
        tuple_of_campaigns = dict(
            (x.id_campagna, x) for x in tuple_of_campaigns
        )
        return {
            "result": tuple_of_campaigns,
            "alertsystem_response_status": alertsystem_response_status,
        }
    else:
        general_error_message(form=form)


# TODO retrieve reposne status as well
@action("user_campaign/_retrive_message_list", method=["GET"])
def user_campaign_retrive_message_list():
    """user_campaign_retrive_message_list _summary_

    Returns
    -------
    _type_
        _description_
    """
    (
        message_list,
        alertsystem_response_status,
    ) = alert_do.visualizza_messaggi(cfg=alertsystem_config)
    message_list = dict((x.id_messaggio, x) for x in message_list)
    logger.debug(
        f"\talertsystem_config: {pformat(alertsystem_config, indent=4, width=1)}"
    )
    logger.debug(
        f"\tstatus: {pformat(alertsystem_response_status, indent=4, width=1)}"
    )
    logger.debug(f"\n{pformat(message_list, indent=4, width=1)}")
    alertsystem_response_status_kk = (
        alertsystem_response_status.__dict__
    )
    logger.debug(
        f"\n{pformat(alertsystem_response_status, indent=4, width=1)}"
    )
    return {
        "result": message_list,
        "alertsystem_response_status": alertsystem_response_status,
    }


@action("user_campaign/_create_message", method=["POST"])
def user_campaign_create_message():
    """user_campaign_create_message _summary_

    Returns
    -------
    _type_
        _description_
    """
    form = Form(
        [
            Field(
                "message_text",
                requires=IS_NOT_EMPTY(),
            ),
            Field(
                "voice_gender",
                requires=IS_EMPTY_OR(IS_IN_SET(["M", "F"])),
            ),
            Field("message_note"),
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="_create_capmaign",
        csrf_protection=False,
    )
    if form.accepted:
        message_text: str = form.vars.get("message_text")
        voice_gender: str = form.vars.get("voice_gender")
        message_type: str = form.vars.get("message_note")
        voice_for_character: model.Carattere = getattr(
            model.Carattere, voice_gender
        )

        (
            message_tuple,
            alertsystem_response_status,
        ) = alert_do.crea_messaggio(
            cfg=alertsystem_config,
            testo_messaggio=message_text,
            carattere_voce=voice_for_character,
            note_messaggio=message_type,
        )
        logger.debug(f"\talertsystem_config: {alertsystem_config}")
        logger.debug(f"\tstatus: {alertsystem_response_status}")
        logger.debug(
            f"\n{pformat(message_tuple, indent=4, width=1)}"
        )
        alertsystem_response_status_kk = (
            alertsystem_response_status.__dict__
        )
        logger.debug(
            f"\n{pformat(alertsystem_response_status, indent=4, width=1)}"
        )
        return {
            "result": {
                "message_id": message_tuple[0],
                "message_credits": message_tuple[1],
            },
            "alertsystem_response_status": alertsystem_response_status,
        }
    else:
        general_error_message(form=form)


@action("user_campaign/<campaign_id>", method=["GET"])
def user_campaign_get_campaign(campaign_id: str):
    """This is a test function to test the campaign creation"""
    (
        vis_campaign,
        alertsystem_response_status,
    ) = alert_do.visualizza_campagna(
        id_campagna=campaign_id,
        cfg=alertsystem_config,
    )
    print(vis_campaign, alertsystem_response_status)
    if vis_campaign is None:
        return {
            "alertsystem_response_status": alertsystem_response_status,
            "result": vis_campaign,
        }
    vis_campaign = dict(zip(vis_campaign[0], vis_campaign[1]))
    return {
        "result": vis_campaign,
        "alertsystem_response_status": alertsystem_response_status,
    }


# TODO get message ID, delete message
@action(
    "user_campaign/_delete_older_message",
    method=["DELETE"],
)
def user_campaign_delete_older_message():
    """user_campaign_delete_older_message _summary_

    Returns
    -------
    _type_
        _description_
    """
    (
        message_list,
        alertsystem_response_status,
    ) = alert_do.visualizza_messaggi(cfg=alertsystem_config)
    message_id_delete = int(request.params["message_id_delete"])
    # ?checking if message_id_delete is in message_list
    if (
        len(
            [
                b.id_messaggio
                for b in message_list
                if b.id_messaggio == message_id_delete
            ]
        )
        < 1
    ):
        return {
            "result": "No message with this ID, list with this ID is empty",
            "alertsystem_response_status": alertsystem_response_status,
        }
    logger.debug(
        f"\n message_list: {pformat(message_list, indent=4, width=1)}"
    )
    logger.debug(
        f"\n status: {pformat(alertsystem_response_status, indent=4, width=1)}"
    )
    logger.debug(
        f"\n message_id_delete: {pformat(message_id_delete, indent=4, width=1)}"
    )
    (
        message_to_be_deleted,
        alertsystem_response_status,
    ) = alert_do.cancella_messaggio(
        cfg=alertsystem_config, id_messaggio=message_id_delete
    )
    if message_to_be_deleted is None:
        # general_error_message(
        #     error_code=410, error_message="ID mismatch", form=None
        # )
        return {
            "alertsystem_response_status": alertsystem_response_status,
            "result": "No message with this ID, list with this ID is empty",
        }
    elif message_to_be_deleted == message_id_delete:
        logger.debug(
            f"\n Deleted: {message_id_delete} from database"
        )
        return {
            "alertsystem_response_status": alertsystem_response_status,
            "result": f"Message {message_id_delete} deleted from database",
        }
    else:
        general_error_message(
            form=None,
            error_code=500,
            error_message="Internal Server Error. Message deleted is not the message specified",
        )


@action("user_campaign/_create_capmaign", method=["POST"])
def user_campaign_create():
    """user_campaign_create _summary_

    Returns
    -------
    _type_
        _description_
    """
    form = Form(
        [
            Field(
                "group",
                "integer",
                requires=IS_EMPTY_OR(IS_INT_IN_RANGE(1, 3)),
            ),
            Field(
                "message_text",
                requires=IS_NOT_EMPTY(),
            ),
            Field(
                "voice_gender",
                requires=IS_EMPTY_OR(IS_IN_SET(["M", "F"])),
            ),
            Field("message_note"),
            Field("message_ID"),
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="_create_capmaign",
        csrf_protection=False,
    )

    if form.accepted:
        if form.vars["voice_gender"] is None:
            voice_gender: str = "F"
        else:
            voice_gender: str = form.vars.get("voice_gender")

        group_numer: int = form.vars["group"]
        message_text: str = form.vars["message_text"]
        message_type: str = form.vars.get("message_note")
        voice_for_character: model.Carattere = getattr(
            model.Carattere, voice_gender
        )
        # TODO HTTP response status
        result_from_database = db(
            (db.soggetti_vulnerabili.gruppo == group_numer)
        ).select(
            db.soggetti_vulnerabili.telefono,
        )
        if result_from_database is None:
            general_error_message(
                form=form,
                error_message=".Bad Request. Empty result_from_database is None",
            )
        telephone_numbers = result_from_database.column(0)
        # telephone_numbers = ["3494351325"]
        # logger.debug(
        #     f"\n This is telephone_numbers : {pformat(telephone_numbers, indent=4, width=1)}"
        # )
        # * To be deleted once we use whole phone numbers list
        return telephone_numbers

        # * if there is no message ID given create a new message
        if form.vars["message_ID"] is None:
            if form.vars["message_note"] is None:
                message_type: str = "default"
            (
                message_tuple,
                alertsystem_response_status,
            ) = alert_do.crea_messaggio(
                cfg=alertsystem_config,
                testo_messaggio=message_text,
                carattere_voce=voice_for_character,
                note_messaggio=message_type,
            )
            message_id = int(message_tuple[0])
            logger.debug(
                f"\n This is message_tuple : {pformat(message_tuple, indent=4, width=1)}"
            )
        # * if there is a message ID given, create campaign with this message ID
        else:
            message_id = int(form.vars("message_ID"))
        return f"\n{pformat(alertsystem_config, indent=4, width=1)}"
        (
            campagin_tuple,
            alertsystem_response_status,
        ) = alert_do.genera_campagna(
            cfg=alertsystem_config,
            id_prescelto_campagna="TESTGTERpresceltocampagna",
            id_messaggio=message_id,
            lista_numeri_telefonici=telephone_numbers,
        )
        return {
            "result": campagin_tuple,
            "alertsystem_response_status": alertsystem_response_status,
        }
    else:
        general_error_message(form=form)
