# -*- coding: utf-8 -*-

import os
from ..common import db, logger

def render(row):
    """ """

    # TODO:
    # import pdb; pdb.set_trace()
    # allegato = {
    #     'fileName': os.path.basename(row.comunicazione.allegato),
    #     'file': '' 
    # }

    return {
        'idIntervento': row.idIntervento,
        'operatore': row.operatore,
        'testo': row.testo,
        'files': []
    }
    

def fetch(lavorazione_id, timeref=None):
    """ """

    dbset = db(
        (db.comunicazione.lavorazione_id==lavorazione_id) & \
        (db.join_segnalazione_lavorazione.lavorazione_id==db.comunicazione.lavorazione_id) & \
        (db.join_segnalazione_incarico.lavorazione_id==db.join_segnalazione_lavorazione.lavorazione_id) & \
        (db.join_segnalazione_incarico.incarico_id==db.intervento.incarico_id)
    )

    if not timeref is None:
        dbset = dbset(db.comunicazione.timeref==timeref)

    rec = dbset.select(
        db.intervento.id.with_alias('idIntervento'),
        db.comunicazione.mittente.with_alias('operatore'),
        db.comunicazione.testo.with_alias('testo'),
        db.comunicazione.allegato,
        orderby = ~db.comunicazione.timeref
    ).first()

    return render(rec)

after_insert_comunicazione = fetch

# def after_insert_comunicazione(lavorazione_id, timeref=None):
#     """ """
#     return 
