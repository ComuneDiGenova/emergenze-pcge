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

from py4web.core import Fixture, HTTP, dumps
from py4web import action, request, abort, redirect, URL, Field
from yatl.helpers import A
from ..common import session, T, cache, auth, logger, authenticated, unauthenticated, flash, db

from py4web.utils.form import Form
from py4web.utils.cors import CORS
from pydal.validators import *
from pydal.validators import Validator

from mptools.frameworks.py4web import shampooform as sf

from .tools import iscrizione_optons

# TODO: Limitare l'abilitazione cross origin all'indirizzo effettivo di chiamata da parte di WSO2
cors = CORS()

def error_message(**errors):
    base_message = "Sono stati riscontrati i seguenti valori nella compilazione del form:\n"
    return base_message + '\n'.join(map(lambda kw: f'{kw[0]}: {kw[1]}', errors.items()))

def validation_error(**errors):

    status = 400

    body = {
        "detail": error_message(**errors),
        "instance": "string",
        "status": status,
        "title": "Errore di convalida",
        "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.1"
    }

    raise HTTP(status, body=dumps(body), headers={'Content-Type': 'application/json'})

not_accepted = {
    "detail": "Il servizio è stato invocato senza i dati necessari.",
    "instance": "string",
    "status": 200,
    "title": "Richiesta vuota",
    "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.10"
}

not_yet_implemented = {
    "detail": "Il servizio non conforme",
    "instance": "string",
    "status": 200,
    "title": "Servizio dummy",
    "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.1"
}

@action("utente/<codice_fiscale>", method=['GET'])
@action("allerte/utente/<codice_fiscale>", method=['GET'])
@action.uses(cors)
def info(codice_fiscale):
    """ Recap informazioni utente """
    # TODO: Codice fiscale obbligatorio
    return db.utente(codiceFiscale=codice_fiscale)

@action("utente", method=['POST', 'GET'])
# @action("allerte/utente", method=['POST', 'GET'])
@action.uses(cors, db)
def utente():
    """ Registrazione utente """

    db.utente.iscrizione.default = iscrizione_optons[0]
    if not 'iscrizione' in request.POST:
        request.POST['iscrizione'] = db.utente.iscrizione.default

    db.utente.vulnerabilitaPersonale.required = False
    if not 'vulnerabilitaPersonale' in request.POST:
        request.POST['vulnerabilitaPersonale'] = db.utente.vulnerabilitaPersonale.default

    db.utente.dataRegistrazione.required = False
    if not 'dataRegistrazione' in request.POST:
        request.POST['dataRegistrazione'] = db.utente.dataRegistrazione.default()

    form = Form(db.utente,
        deletable = False, # dbio=False,
        form_name = 'utente',
        csrf_protection = False
    )

    result = {}
    if form.accepted:
        # result['idUtente'] = form.vars['id']

        if not form.errors:
            # 200
            
            # TODO: Trovare una soluzione trasversale a tutti i campi (tipo render)
            # in base a quanto definito nei validatori
            return form.vars

    elif form.errors:
        # 400
        return validation_error(**form.errors)
    else:
        return not_accepted

    return not_accepted


@action("telefono", method=['POST'])
@action("allerte/telefono", method=['POST'])
@action.uses(cors)
def telefono():
    """ Registrazione contatto telefonico """
    return not_yet_implemented

@action("telefono/<utente_id>/<contatto_id>/<telefono>", method=['DELETE'])
@action("cancellaTelefono/<utente_id>/<contatto_id>/<telefono>", method=['DELETE'])
@action("allerte/telefono/<utente_id>/<contatto_id>/<telefono>", method=['DELETE'])
@action.uses(cors)
def telefono2(utente_id=None, contatto_id=None, telefono=None):
    """ Rimozione contatto telefonico """
    # TODO: utente_id, contatto_id e telefono campi obbligatori
    return not_yet_implemented


@action("componente", method=['POST'])
@action("allerte/componente", method=['POST'])
@action.uses(cors)
def componente():
    """ Registrazione nuovo componente nucleo famigliare """
    return not_yet_implemented

@action("componente/<utente_id>/<civico_id>/<motivo>", method=['DELETE'])
@action("cancellaComponente/<utente_id>/<civico_id>/<motivo>", method=['DELETE'])
@action("allerte/componente/<utente_id>/<civico_id>/<motivo>", method=['DELETE'])
@action.uses(cors)
def componente(utente_id=None, civico_id=None, motivo=None):
    """ Rimozione componente nucleo famigliare """
    # TODO: utente_id e civico_id campi obbligatori
    return not_yet_implemented


@action("civico", method=['POST'])
@action("allerte/civico", method=['POST'])
@action.uses(cors)
def civico():
    """ Registrazione nuovo civico """
    return not_yet_implemented

@action("civico", method=['PUT'])
@action("allerte/civico", method=['PUT'])
@action.uses(cors)
def civico():
    """ Aggiornamento civico """
    return not_yet_implemented
