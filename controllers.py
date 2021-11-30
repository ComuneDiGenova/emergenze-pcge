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
# @action.uses(query2forms())
def civico(format=None):
    db.civico.desvia.comment = 'Cerca per toponimo'
    res = db(db.civico).select(
        db.civico.numero.min().with_alias('nummin'),
        db.civico.numero.max().with_alias('nummax'),
        db.civico.cap.min().with_alias('capmin'),
        db.civico.cap.max().with_alias('capmax'),
    ).first()
    # db.civico.colore.requires = IS_IN_DB(db(db.civico), db.civico.colore, distinct=True)
    db.civico.colore.requires = IS_EMPTY_OR(IS_IN_SET([
        ('','Nero'),
        ('R','Rosso'),
        ('r', 'Rosso')
    ]))
    db.civico.lettera.requires = IS_LENGTH(1)

    form = Form([
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
