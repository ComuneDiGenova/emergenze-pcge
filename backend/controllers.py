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

from datetime import datetime, timedelta
# from pprint import pp
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

logger.debug(request.url)
logger.debug(request.query)

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
# id_message_global: int = -1
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

@action("evento")
@action.uses(cors, db)
def evento():
    return {"result": _evento.fetch()}

@action('indirizzo', method=['GET', 'POST'])
@action('indirizzo.<format>', method=['GET', 'POST'])
@action('ricerca_indirizzo', method=['GET', 'POST'])
@action('ricerca_indirizzo.<format>', method=['GET', 'POST'])
@action('RicercaCivico', method=['GET', 'POST'])
@action('RicercaCivico.<format>', method=['GET', 'POST'])
@action.uses(cors, db)
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
@action.uses(cors, db)
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
        IS_NOT_IN_DB(db(db.segnalazione_da_vt), db.segnalazione_da_vt.intervento_id),
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
        (intervento_info.idmin or 0), (intervento_info.idmax or 0) + 1
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
        [
            db.intervento.intervento_id,
            Field('ceduta',
                label = "Se presente indica la segnalazione come NON in carico a PM",
                required = False,
                requires = IS_EMPTY_OR(IS_IN_SET(['True', 'False'], zero=None))
            ),
        ] + segnalazione_form() + incarico_form(),
        deletable = False, dbio=False,
        validation = _segnalazione.valida_intervento,
        hidden = {'rollback': False},
        form_name = 'modifica_intervento',
        csrf_protection = False
    )

    result = None
    if form.accepted:
        with NoDBIO(form):
            # Rimuovo le variabili non espresamente passate nella request
            for ff in form.table:
                if not ff.required and form.vars[ff.name] is None:
                    form.vars.pop(ff.name)
            if form.vars['stato_id'] is None:
                form.vars['stato_id'] = db(
                    (db.stato_incarico.incarico_id==db.intervento.incarico_id) \
                    & (db.intervento.intervento_id==form.vars['intervento_id'])
                ).select(db.stato_incarico.stato_id, limitby=(0,1,)).first().stato_id
            lon = form.vars.pop('lon')
            lat = form.vars.pop('lat')

            if 'ceduta' in form.vars:
                form.vars['ceduta'] = form.vars.pop('ceduta')=='True'

            form.vars['lon_lat'] = (lon, lat,)
            form.vars['persone_a_rischio'] = form.vars.pop('persone_a_rischio')=='True'
            form.vars['parziale'] = form.vars.pop('parziale')=='True'
            result = _segnalazione.verbatel_update(**form.vars)

    return {"result": result and "Ok", "form": sf.form2dict(form)}


@action("comunicazione", method=["GET", "POST"])
@action("crea_comunicazione", method=["GET", "POST"])
@action("crea/comunicazione", method=["GET", "POST"])
@action("CreaComunicazione", method=["GET", "POST"])
@action("segnalazione/incarico/comunicazione", method=["GET", "POST"])
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
@action("ModificaPresidio/<pattuglia_id:int>", method=["GET", "POST"])
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

@action('lista/segnalazioni', method=['GET', 'POST'])
@action.uses(db)
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


@action('lista/mire', method=['GET', 'POST'])
@action.uses(db)
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
