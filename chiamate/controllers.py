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
from ..common import session, T, cache, auth, logger, authenticated, unauthenticated, flash, db

from py4web.utils.form import Form
from py4web.utils.cors import CORS
from pydal.validators import *
from pydal.validators import Validator

# TODO: Limitare all'indirizzo effettivo di chiamata da parte di WSO2
cors = CORS()

@action("utente", method=['POST'])
@action("allerte/utente", method=['POST'])
@action.uses(cors)
def utente():
    return {'result': 'ok'}

@action("utente", method=['GET'])
@action("allerte/utente", method=['GET'])
@action.uses(cors)
def info():
    return {'result': 'ok'}


@action("telefono", method=['POST'])
@action("allerte/telefono", method=['POST'])
@action.uses(cors)
def telefono():
    return {'result': 'ok'}

@action("telefono", method=['DELETE'])
@action("allerte/telefono", method=['DELETE'])
@action.uses(cors)
def telefono2():
    return {'result': 'ok'}


@action("componente", method=['POST'])
@action("allerte/componente", method=['POST'])
@action.uses(cors)
def componente():
    return {'result': 'ok'}

@action("componente", method=['DELETE'])
@action("allerte/componente", method=['DELETE'])
@action.uses(cors)
def componente():
    return {'result': 'ok'}


@action("civico", method=['POST'])
@action("allerte/civico", method=['POST'])
@action.uses(cors)
def civico():
    return {'result': 'ok'}

@action("civico", method=['PUT'])
@action("allerte/civico", method=['PUT'])
@action.uses(cors)
def civico():
    return {'result': 'ok'}
