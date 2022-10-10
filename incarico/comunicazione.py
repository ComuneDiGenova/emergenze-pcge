# -*- coding: utf-8 -*-

import os
from ..common import db, logger
from ..verbatel import Intervento

from .. import settings
from py4web import Field

import base64

def render(row):
    """ """

    out = {
        # 'idIntervento': row.idIntervento,
        'operatore': 'operatore di PC',
        'testo': row.testo,
        # 'files': [allegato]
    }

    if not row.comunicazione_incarico_inviata.allegato is None:

        with open(os.path.join(settings.EMERGENZE_UPLOAD, *(row.comunicazione_incarico_inviata.allegato.split(os.path.sep)[1:])), 'rb') as ff:
            encoded_string = base64.b64encode(ff.read()).decode()

        allegato = {
            'fileName': os.path.basename(row.comunicazione_incarico_inviata.allegato),
            'file': encoded_string
        }



        out['files'] = [allegato]

    return out


def fetch(incarico_id, timeref=None):
    """ """

    # dbset = db(
    #     (db.comunicazione.lavorazione_id==lavorazione_id) & \
    #     (db.join_segnalazione_lavorazione.lavorazione_id==db.comunicazione.lavorazione_id) & \
    #     (db.join_segnalazione_incarico.lavorazione_id==db.join_segnalazione_lavorazione.lavorazione_id) & \
    #     (db.join_segnalazione_incarico.incarico_id==db.intervento.incarico_id)
    # )

    dbset = db(db.presidio)(
        (db.comunicazione_incarico_inviata.incarico_id==incarico_id) & \
        (db.comunicazione_incarico_inviata.incarico_id==db.intervento.incarico_id) & \
        # (db.incarico.id==db.comunicazione_incarico_inviata.incarico_id) #& \
        f"segnalazioni.t_sopralluoghi_mobili.id_profilo='{settings.PM_PROFILO_ID}'"
        # (db.presidio.profilo_id=='6')
    )

    if not timeref is None:
        dbset = dbset(db.comunicazione_incarico_inviata.timeref==timeref)

    rec = dbset.select(
        db.intervento.intervento_id.with_alias('idIntervento'),
        # .with_alias('operatore'),
        db.comunicazione_incarico_inviata.testo.with_alias('testo'),
        db.comunicazione_incarico_inviata.allegato,
        orderby = ~db.comunicazione_incarico_inviata.timeref
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
        Intervento.message(idIntervento, **payload)

# def after_insert_comunicazione(lavorazione_id, timeref=None):
#     """ """
#     return
