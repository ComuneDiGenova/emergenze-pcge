# -*- coding: utf-8 -*-

import os
from ..common import db, logger
from ..verbatel import Intervento

def render(row):
    """ """

    # TODO:
    # import pdb; pdb.set_trace()
    # allegato = {
    #     'fileName': os.path.basename(row.comunicazione.allegato),
    #     'file': '' 
    # }

    return {
        # 'idIntervento': row.idIntervento,
        'operatore': 'anonimo',
        'testo': row.testo,
        'files': []
    }
    

def fetch(incarico_id, timeref=None):
    """ """

    # dbset = db(
    #     (db.comunicazione.lavorazione_id==lavorazione_id) & \
    #     (db.join_segnalazione_lavorazione.lavorazione_id==db.comunicazione.lavorazione_id) & \
    #     (db.join_segnalazione_incarico.lavorazione_id==db.join_segnalazione_lavorazione.lavorazione_id) & \
    #     (db.join_segnalazione_incarico.incarico_id==db.intervento.incarico_id)
    # )

    dbset = db(
        (db.comunicazione_incarico_inviate.incarico_id==incarico_id) & \
        (db.comunicazione_incarico_inviate.incarico_id==db.intervento.incarico_id) & \
        (db.incarico.id==db.comunicazione_incarico_inviate.incarico_id) & \
        (db.presidio.profilo_id==6)
    )

    if not timeref is None:
        dbset = dbset(db.comunicazione_incarico_inviate.timeref==timeref)

    rec = dbset.select(
        db.intervento.id.with_alias('idIntervento'),
        # .with_alias('operatore'),
        db.intervento.testo.testo.with_alias('testo'),
        db.intervento.allegato,
        orderby = ~db.comunicazione_incarico_inviate.timeref
    ).first()

    # rec = dbset.select(
    #     db.intervento.id.with_alias('idIntervento'),
    #     db.comunicazione.mittente.with_alias('operatore'),
    #     db.comunicazione.testo.with_alias('testo'),
    #     db.comunicazione.allegato,
    #     orderby = ~db.comunicazione.timeref
    # ).first()

    return rec and (rec.idIntervento, render(rec),)

def after_insert_comunicazione(*args, **kwargs):
    """ """
    result = fetch(*args, **kwargs)
    if not result is None:
        idIntervento, payload = result
        Intervento.message(idIntervento, payload)

# def after_insert_comunicazione(lavorazione_id, timeref=None):
#     """ """
#     return 
