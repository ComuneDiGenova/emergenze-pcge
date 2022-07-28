# -*- coding: utf-8 -*-

import os
from ..common import db, logger
from ..verbatel import Presidio

from .. import settings
from py4web import Field
import base64

def render(row):
    """ """

    out = {
        # 'idSquadra': row.idSquadra,
        'operatore': 'anonimo',
        'testo': row.testo,
    }

    if not row.comunicazione_presidio.allegato is None:
        with open(os.path.join(settings.EMERGENZE_UPLOAD, *(row.comunicazione_presidio.allegato.split(os.path.sep)[1:])), 'rb') as ff:
            encoded_string = base64.b64encode(ff.read()).decode()

            allegato = {
                'fileName': os.path.basename(row.comunicazione_presidio.allegato),
                'file': encoded_string
            }

        out['files'] = [allegato]

    return out


def fetch(presidio_id, timeref=None):
    """ """
    dbset = db(db.presidio)(
        (db.comunicazione_presidio.presidio_id==presidio_id) & \
        (db.pattuglia_pm.presidio_id==db.comunicazione_presidio.presidio_id)
        # (db.comunicazione_presidio.presidio_id==db.presidio.id) & \
        # (db.squadra.id==db.componente.squadra_id) & \
        # (db.squadra.id==db.pattuglia_pm.squadra_id) # & \
        # "segnalazioni.t_sopralluoghi_mobili.id_profilo='6'"
        # (db.componente.matricola==db.agente.matricola)
    )

    #if not timeref is None:
    #    dbset = dbset(db.comunicazione_presidio.timeref==timeref)

    rec = dbset.select(
        # db.intervento.id.with_alias('idIntervento'),
        # db.comunicazione_presidio.presidio_id.with_alias('idSquadra'),
        db.pattuglia_pm.pattuglia_id.with_alias('idSquadra'), # <- Id Verbatel
        db.comunicazione_presidio.testo.with_alias('testo'),
        db.comunicazione_presidio.allegato,
        orderby = ~db.comunicazione_presidio.timeref
    ).first()

    if rec is None:
        logger.debug('Comunicazione non diretta a PL.')
        logger.debug(f'{timeref}, {presidio_id}')

    return rec and (rec.idSquadra, render(rec),)

def after_insert_comunicazione(*args, **kwargs):

    result = fetch(*args, **kwargs)
    if not result is None:
        idSquadra, payload = result
        Presidio.message(idSquadra, **payload)
