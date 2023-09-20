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
from ..common import session, T, cache, auth, logger, authenticated, unauthenticated, flash, db, cors

from py4web.utils.form import Form
from pydal.validators import *
from pydal.validators import Validator

from mptools.frameworks.py4web import shampooform as sf
import functools, itertools

from .tools import iscrizione_options, LANGUAGES

def error_message(**errors):
    base_message = "Sono stati riscontrati i seguenti valori nella compilazione del form:\n"
    return base_message + '\n'.join(map(lambda kw: f'{kw[0]}: {kw[1]}', errors.items()))

def validation_error(**errors):

    status = 400

    body : dict = {
        "detail": error_message(**errors),
        "instance": "string",
        "status": status,
        "title": "Errore di convalida",
        "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.1"
    }

    raise HTTP(status, body=dumps(body), headers={'Content-Type': 'application/json'})

def file_not_found():

    status = 404

    body = {
        "detail": "I dati forniti non hanno restituito nessun risultato",
        "instance": "string",
        "status": 404,
        "title": "Nessun risultato",
        "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.4"
    }

    raise HTTP(status, body=dumps(body), headers={'Content-Type': 'application/json'})

def no_content():
    """ https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.5 """

    status = 204

    raise HTTP(status, body='')

def generic_message(detail='Successo', title='Ok'):

    body = {
        "detail": f'{detail}',
        "instance": "string",
        "status": 200,
        "title": f'{title}',
        "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.1"
    }

    return body

not_accepted = {
    "detail": "Il servizio è stato invocato senza i dati necessari.",
    "instance": "string",
    "status": 200,
    "title": "Richiesta vuota",
    "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.10"
}

not_yet_implemented = {
    "detail": "Servizio non conforme",
    "instance": "string",
    "status": 200,
    "title": "Servizio dummy",
    "type": "https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.1"
}


@action("lingue", method=['GET'])
@action.uses(cors)
def lingue():
    """ Restituisce le lingue accettate """
    raise HTTP(200,
        body=dumps([
            {
                "idLingua": f"{cc[0]}",
                "descrizione": f"{cc[1]}"
            }
        for cc in LANGUAGES]),
        headers={'Content-Type': 'application/json'}
    )

@action("utente/<codice_fiscale>", method=['GET'])
@action("allerte/utente/<codice_fiscale>", method=['GET'])
@action.uses(cors, db)
def info(codice_fiscale):
    """ Recap informazioni utente """

    contatti = f"json_agg(DISTINCT {db.contatto})"
    recapiti = f"json_agg(DISTINCT {db.recapito})"
    ruolo = f"json_agg({db.nucleo._rname.split('.')[1]})"

    join = db(
        (db.utente.id==db.nucleo.idUtente) & \
        (db.nucleo.idCivico==db.recapito.id) # & \
        # (db.utente.id==db.contatto.idUtente)
    )

    dbset = join((db.utente.codiceFiscale==codice_fiscale))

    res_ = dbset.select(
        db.utente.ALL,
        contatti,
        recapiti,
        ruolo,
        left = db.contatto.on(db.utente.id==db.contatto.idUtente),
        distinct = db.utente.id,
        groupby = db.utente.id,
        limitby = (0,1,)
    ).first()

    if res_ is None: no_content()

    # WARNING! Mantenere questo controllo, la proprietà readable non sembra sempre
    #          affidabile, talvolta è nulla
    field_is_readable = lambda field: field.readable or not field.name in ('modified_on', 'created_on',)

    res = {ff.name: res_.utente[ff.name] for ff in db.utente if field_is_readable(ff)}

    ruoli = {vv['idcivico']: vv['tipo'] for vv in res_[ruolo]}

    res['listaCiviciRegistrati'] = [
        {ff.name: recapito[ff._rname.strip('"')] for ff in db.recapito if field_is_readable(ff)}
    for recapito in res_[recapiti]]
    # if ruoli[recapito['id']]=="CAPO FAMIGLIA"

    res['listaContattiTelefonici'] = [
        {ff.name: contatto[ff._rname.strip('"')] for ff in db.contatto if field_is_readable(ff)}
    for contatto in res_[contatti] if not contatto is None]

    componenti = f"json_agg({db.utente} ORDER BY {db.utente}.id)"

    join1 = db(
        (db.utente.id==db.nucleo.idUtente) & \
        (db.nucleo.idCivico==db.recapito.id) #& \
        # (db.utente.id==db.contatto.idUtente)
    )

    res1_ = join1(
        # (db.utente.codiceFiscale!=codice_fiscale) & \
        db.recapito.id.belongs([recapito['id'] for recapito in res_[recapiti]])
    ).select(
        db.recapito.id,
        db.nucleo.tipo.with_alias('tipo'),
        contatti,
        componenti,
        left = db.contatto.on(db.utente.id==db.contatto.idUtente),
        groupby = [db.nucleo.tipo]+[ff for ff in db.recapito],
        orderby = db.recapito.id
    )

    for el in res['listaCiviciRegistrati']:
        nucleocf = set()
        el['listaComponentiNucleo'] = []
        if ruoli[el['id']] == "CAPO FAMIGLIA":
            for componentiByCivico in res1_.find(lambda row: row.recapito.id==el['id']):
                for comp in itertools.chain(componentiByCivico[componenti]):

                    info = dict(
                        {ff.name: comp[ff._rname.strip('"')] for ff in db.utente if field_is_readable(ff)},
                        tipo = componentiByCivico['tipo'],
                        listaContattiTelefonici = [{ff.name: dd[ff._rname.strip('"')] for ff in db.contatto if field_is_readable(ff)}
                            for dd in componentiByCivico[contatti] if dd and dd['idutente']==comp['id']]
                    )
                    # TODO: gli utenti doppi sovrebbero poter essere evitati direttamente
                    # nella definizione dei loop invece che rimossi a posteriori
                    # come qui di seguito (soluzione quick&dirty)
                    if not info['codiceFiscale'] in nucleocf:
                        nucleocf.add(info['codiceFiscale'])
                        el['listaComponentiNucleo'].append(info)


    return res

@action("utente", method=['POST', 'GET'])
# @action("allerte/utente", method=['POST', 'GET'])
@action.uses(cors, db)
def utente():
    """ Registrazione utente """

    db.utente.iscrizione.default = iscrizione_options[0]
    if not 'iscrizione' in request.POST:
        request.POST['iscrizione'] = db.utente.iscrizione.default

    # db.utente.vulnerabilitaPersonale.required = False
    # if not 'vulnerabilitaPersonale' in request.POST:
    #     request.POST['vulnerabilitaPersonale'] = db.utente.vulnerabilitaPersonale.default

    db.utente.dataRegistrazione.required = False
    if not 'dataRegistrazione' in request.POST:
        request.POST['dataRegistrazione'] = db.utente.dataRegistrazione.default()

    record = db.utente(codiceFiscale=request.POST.get('codiceFiscale'))
    form = Form(db.utente,
        record = record,
        deletable = False, # dbio=False,
        form_name = 'utente',
        csrf_protection = False
    )

    # result = {}
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

    # return not_accepted


@action("telefono", method=['POST'])
# @action("allerte/telefono", method=['POST'])
@action.uses(cors, db)
def telefono():
    """ Registrazione contatto telefonico """

    dbio = not 'rollback' in request.POST

    if not dbio:
        db.contatto.idUtente.requires = IS_EMPTY_OR(db.contatto.idUtente.requires)

    form = Form(db.contatto,
        deletable = False, dbio=dbio,
        form_name = 'telefono',
        csrf_protection = False
    )

    if form.accepted:
        if not form.errors:
            # 200
            # TODO: Trovare una soluzione trasversale a tutti i campi (tipo render)
            # in base a quanto definito nei validatori
            return form.vars
    elif form.errors:
        # 400
        return validation_error(**form.errors)
    else:
        # 200
        return not_accepted

    # return not_yet_implemented


# @action("telefono/<contatto_id>/<utente_id>/<telefono>", method=['DELETE'])
# @action("cancellaTelefono/<contatto_id>/<utente_id>/<telefono>", method=['DELETE'])
# @action("allerte/telefono/<contatto_id>/<utente_id>/<telefono>", method=['DELETE'])
@action("telefono/<contatto_id>", method=['DELETE'])
@action("cancellaTelefono/<contatto_id>", method=['DELETE'])
@action("allerte/telefono/<contatto_id>", method=['DELETE'])
@action.uses(cors, db)
def telefono2(contatto_id, utente_id=None, telefono=None):
    """ Rimozione contatto telefonico """
    # TODO: utente_id, contatto_id e telefono campi obbligatori

    dbset = db(db.contatto.id==contatto_id)
    # if not utente_id is None:
    #     dbset = dbset(db.contatto.idUtente==utente_id)
    # if not telefono is None:
    #     dbset = dbset(db.contatto.numero==telefono)

    count = dbset.delete()
    if count==0:
        return no_content()
        # return file_not_found()
    elif count==1:
        return generic_message(detail='Contatto rimosso correttamente')
    else:
        # Questo non deve mai succedere
        return validation_error(
            telefono = 'Le chiavi corrispondono a troppi valori'
        )

    return not_yet_implemented


@action("civico", method=['POST'])
@action("allerte/civico", method=['POST'])
@action.uses(cors, db)
def civico():
    """ Registrazione nuovo civico """

    dbio = not 'rollback' in request.POST

    if not dbio:
        db.recapito.indirizzoCompleto.requires = None
        db.recapito.idVia.requires = None
        db.recapito.numeroCivico.requires = None

    form = Form(db.recapito,
        record = request.POST.get('id'),
        deletable = False, dbio=dbio,
        form_name = 'civico',
        csrf_protection = False
    )

    if form.accepted:
        if not form.errors:
            # 200
            # TODO: Trovare una soluzione trasversale a tutti i campi (tipo render)
            # in base a quanto definito nei validatori
            return {k: v for k,v in form.vars.items()}
    elif form.errors:
        # 400
        return validation_error(**form.errors)
    else:
        # 200
        return not_accepted

    return not_yet_implemented

# @action("civico", method=['PUT'])
# @action("allerte/civico", method=['PUT'])
# @action.uses(cors)
# def civico():
#     """ Aggiornamento civico """
#     import pdb; pdb.set_trace()
#     return not_yet_implemented

@action("componente", method=['POST'])
@action("allerte/componente", method=['POST'])
@action.uses(cors, db)
def componente():
    """ Registrazione nuovo componente nucleo famigliare """

    dbio = not 'rollback' in request.POST

    if not dbio:
        db.nucleo.idUtente.requires = IS_EMPTY_OR(db.nucleo.idUtente.requires)
        db.nucleo.idCivico.requires = IS_EMPTY_OR(db.nucleo.idCivico.requires)

    form = Form(db.nucleo,
        deletable = False, dbio=dbio,
        form_name = 'componente',
        csrf_protection = False
    )

    if form.accepted:
        if not form.errors:
            # 200
            # TODO: Trovare una soluzione trasversale a tutti i campi (tipo render)
            # in base a quanto definito nei validatori
            return form.vars
    elif form.errors:
        # 400
        return validation_error(**form.errors)
    else:
        # 200
        return not_accepted

# @action("pippo", method=['DELETE'])
# def pippo():
#     import pdb; pdb.set_trace()

# @action("componente/<utente_id>/<civico_id>/<motivo>", method=['DELETE'])
# @action("allerte/componente/<utente_id>/<civico_id>/<motivo>", method=['DELETE'])
@action("cancellaComponente/<utente_id>/<civico_id>", method=['DELETE'])
@action("cancellaComponente/<utente_id>/<civico_id>/<motivo>", method=['DELETE'])
@action.uses(cors, db)
def cancellaComponente(utente_id=None, civico_id=None, motivo=None):
    """ Rimozione componente nucleo famigliare """

    dbset = db(db.nucleo.idUtente==utente_id)
    dbset = dbset(db.nucleo.idCivico==civico_id)

    count = dbset.delete()
    if count==0:
        return no_content()
        # return file_not_found()
    elif count>0:
        return generic_message(detail='Componente rimosso correttamente')
    else:
        # Questo non deve maisuccedere
        return validation_error(
            telefono = 'Le chiavi corrispondono a troppi valori'
        )


@action("soggettiVulnerabili/<page:int>/<paginate:int>/", method=['GET', 'OPTIONS'])
@action("soggettiVulnerabili/<page:int>/", method=['GET', 'OPTIONS'])
@action("soggettiVulnerabili/", method=['GET', 'OPTIONS'])
@action.uses(cors, db)
def soggetti_vulnerabili(page=None, paginate=10):

    if page is None:
        limitby = None
    else:
        limitby = tuple(map(lambda ee: ee*int(paginate), (page, page+1)))

    result = db(db.soggetti_vulnerabili).select(limitby=limitby)

    return {
        'result': result.as_list(),
        'results': len(result)
    }

@action("soggettiVulnerabili/db-<id:int>/", method=['DELETE', 'OPTIONS'])
@action("soggettiVulnerabili/fc-<id:int>/", method=['DELETE', 'OPTIONS'])
@action.uses(cors, db)
def disattiva_soggetti_vulnerabili2(id):
    
    rec = db.utente[id]
    if rec is None: no_content()
    
    res = db(db.utente.id==id).delete()
    
    {'result': res}
    

@action("soggettiVulnerabili/xls-<id:int>/", method=['DELETE', 'OPTIONS'])
@action("soggettiVulnerabili/pc-<id:int>/", method=['DELETE', 'OPTIONS'])
@action.uses(cors, db)
def disattiva_soggetti_vulnerabili(id):
    """ """

    rec = db.recupero[id]

    if rec is None: no_content()

    res = rec.update_record(is_active=False)
    return {'result': res}