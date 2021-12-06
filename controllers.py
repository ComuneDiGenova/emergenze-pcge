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

from py4web import action, request, abort, redirect, URL, Field
from yatl.helpers import A
from .common import session, T, cache, auth, logger, authenticated, unauthenticated, flash, db

from py4web.utils.form import Form
from pydal.validators import *

from . import evento as _evento
from . import civico as _civico
from . import segnalazione as _segnalazione

from mptools.frameworks.py4web import shampooform as sf

import geojson

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
def evento():
    return {'result': _evento.fetch()}

@action('civico', method=['GET', 'POST'])
@action('civico.<format>', method=['GET', 'POST'])
@action('RicercaCivico', method=['GET', 'POST'])
@action('RicercaCivico.<format>', method=['GET', 'POST'])
# @action.uses(query2forms())
def civico(format=None):

    db.civico.desvia.comment = 'Cerca per toponimo'
    res = db(db.civico).select(
        db.civico.numero.min().with_alias('nummin'),
        db.civico.numero.max().with_alias('nummax'),
        db.civico.cap.min().with_alias('capmin'),
        db.civico.cap.max().with_alias('capmax'),
        db.civico.codvia.min().with_alias('codmin'),
        db.civico.codvia.max().with_alias('codmax'),
    ).first()

    db.civico.codvia.requires = IS_EMPTY_OR(IS_INT_IN_RANGE(int(res.codmin), int(res.codmax)))

    # db.civico.colore.requires = IS_IN_DB(db(db.civico), db.civico.colore, distinct=True)
    db.civico.colore.requires = IS_EMPTY_OR(IS_IN_SET([
        ('','Nero'),
        ('R','Rosso'),
        ('r', 'Rosso')
    ]))
    db.civico.lettera.requires = IS_LENGTH(1)

    form = Form([
        db.civico.codvia,
        db.civico.desvia,
        Field(db.civico.numero.name, 'integer',
            label = db.civico.numero.label,
            comment = f"Numero compreso tra {int(res.nummin):d} e {int(res.nummax):d}",
            length = db.civico.numero.length,
            requires = IS_EMPTY_OR(IS_INT_IN_RANGE(int(res.nummin), int(res.nummax)))
        ),
        db.civico.lettera,
        db.civico.colore,
        Field(db.civico.cap.name, 'integer',
            label = db.civico.cap.label,
            comment = f"Numero compreso tra {int(res.capmin):d} e {int(res.capmax):d}",
            length = db.civico.cap.length,
            requires = IS_EMPTY_OR(IS_INT_IN_RANGE(int(res.capmin), int(res.capmax)))
        ),
        Field('lon', 'double', label='Longitude', requires=IS_EMPTY_OR(IS_FLOAT_IN_RANGE(-180., 180.))),
        Field('lat', 'double', label='Latitude', requires=IS_EMPTY_OR(IS_FLOAT_IN_RANGE(-90., 90.))),
        # Field('epsg', )
        Field('page', 'integer', default=0, requires=IS_EMPTY_OR(IS_INT_IN_RANGE())),
        Field('paginate', 'integer', default=10, requires=IS_EMPTY_OR(IS_INT_IN_RANGE())),
    ], deletable = False, dbio=False,
        form_name = 'civico',
        csrf_protection = False
    )
    result = None
    if form.accepted:

        for field in filter(lambda ff: not ff.name in form.errors, form.table):
            if form.vars[field.name] is None:
                if not field.default is None:
                    form.vars[field.name] = field.default

        lon = form.vars.pop('lon')
        lat = form.vars.pop('lat')
        if not None in (lon, lat,):
            form.vars["near_by"] = geojson.Point([lon, lat])
        result = _civico.fetch(**form.vars, as_geojson=(format=='geojson'))

    if format=='geojson':
        return {'result': geojson.FeatureCollection(result), 'form': sf.form2dict(form)}
    else:
        return {'result': result, 'form': sf.form2dict(form)}

@action('segnalazione', method=['GET', 'POST'])
@action('crea/segnalazione', method=['GET', 'POST'])
@action('CreaSegnalazione', method=['GET', 'POST'])
def segnalazione():
    """ """

    res = db(db.evento).select(
        db.evento.id.min().with_alias('idmin'),
        db.evento.id.max().with_alias('idmax')
    ).first()

    civ_stats = db(db.civico).select(
        db.civico.id.min().with_alias('idmin'),
        db.civico.id.max().with_alias('idmax')
    ).first()

    form = Form([
        Field('evento_id', 'integer', label='Id Evento', required=True,
            comment = f'Inserisci un id Evento valido compreso tra {res.idmin} e {res.idmax}',
            requires = IS_INT_IN_RANGE(res.idmin, res.idmax)
        ),
        Field('nome', label='Nome segnalante', comment='Inserire nome e cognome', required=True),
        Field('descrizione', required=True),
        Field('lon', 'double', label='Longitudine', requires=IS_FLOAT_IN_RANGE(-180., 180.)),
        Field('lat', 'double', label='Latitudine', requires=IS_FLOAT_IN_RANGE(-90., 90.)),
        Field('criticita_id', label='Id Criticità',
            comment='Scegli il tipo di criticità da segnalare',
            requires = IS_IN_DB(
                db((db.tipo_criticita.valido==True) & ~db.tipo_criticita.id.belongs([7,12])),
                db.tipo_criticita.id, label=db.tipo_criticita.descrizione,
                orderby=db.tipo_criticita.descrizione
            )
        ),
        db.segnalazione.operatore, # TODO: Introdurre validazione (CF valido o matricola in db)
        db.segnalante.telefono,
        db.segnalante.note,
        db.segnalazione.nverde,
        Field('note_geo',
            label = db.segnalazione.note.label,
            comment = db.segnalazione.note.comment
        ),
        Field('civico_id', 'integer',
            label = 'Id civico',
            comment = f"Inserisci un Id civico valido compreso tra {civ_stats.idmin:d} e {civ_stats.idmax:d}",
            length = db.civico.id.length,
            requires = IS_EMPTY_OR(IS_INT_IN_RANGE(int(civ_stats.idmin), int(civ_stats.idmax)))
        ),
        Field('persone_a_rischio',
            label = db.segnalazione.rischio.label,
            comment = db.segnalazione.rischio.comment
        ),
        Field('tabella_oggetto_id',
            label = 'Seleziona la tabella degli oggetti a rischio',
            requires = IS_EMPTY_OR(IS_IN_DB(
                db(db.tipo_oggetto_rischio),
                db.tipo_oggetto_rischio.id,
                label = db.tipo_oggetto_rischio.descrizione,
                orderby = db.tipo_oggetto_rischio.descrizione
            ))
        ),
        Field('note_riservate',
            label=db.segnalazione_riservata.testo.label,
            comment=db.segnalazione_riservata.testo.comment,
        ),
    ],
        hidden = {'rollback': False},
        validation = _segnalazione.valida_segnalazione,
        deletable = False, dbio=False,
        form_name = 'civico',
        csrf_protection = False
    )

    result = None
    if form.accepted:
        lon = form.vars.pop('lon')
        lat = form.vars.pop('lat')
        form.vars['lon_lat'] = (lon, lat,)
        rollback = 'rollback' in form.vars
        if rollback:
            form.vars.pop('rollback')
        result =_segnalazione.create(**form.vars)
        if rollback:
            # Modalità test del form
            db.rollback()

    return {'result': result, 'form': sf.form2dict(form)}
