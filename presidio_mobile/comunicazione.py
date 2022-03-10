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
        'idSquadra': row.idSquadra,
        # 'operatore': row.operatore,
        'testo': row.testo,
        'files': []
    }
    

def fetch(presidio_id, timeref=None):
    """ """
    dbset = db(db.comunicazione_presidio.presidio_id==presidio_id)
    
    if not timeref is None:
        dbset = dbset(db.comunicazione_presidio.timeref==timeref)
        
    rec = dbset.select(
        # db.intervento.id.with_alias('idIntervento'),
        db.comunicazione_presidio.presidio_id.with_alias('idSquadra'),
        db.comunicazione_presidio.testo.with_alias('testo'),
        db.comunicazione_presidio.allegato,
        orderby = ~db.comunicazione_presidio.timeref
    ).first()
    
    return render(rec)

after_insert_comunicazione = fetch

