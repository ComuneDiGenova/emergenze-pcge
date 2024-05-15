# -*- coding: utf-8 -*-

from .. import settings
from ..common import db, logger
import shutil, os
from py4web import Field
from pathlib import Path
from pydal.validators import *
from ..verbatel import Intervento

import base64

def get_fake_upload(table):
    """ """
    
    fake_upload = Field('allegato', 'upload',
        uploadfolder = settings.UPLOAD_FOLDER, uploadseparate=True
    )
    fake_upload.bind(table)
    return fake_upload

fake_upload = get_fake_upload(db.comunicazione)

comunicazione_fields = [
    db.comunicazione.mittente,
    db.log.operatore,
    db.comunicazione.testo,
    fake_upload
]

UPLOAD_CONFIGURED = (fake_upload.uploadfolder and settings.EMERGENZE_UPLOAD)

def _upload(allegato):
    """
    allegato @string : Path to file on fs
    """
    rdest = None
    if UPLOAD_CONFIGURED and not allegato is None:
        filename, stream = fake_upload.retrieve(allegato)
        filepath = stream.name
        stream.close()

        dest = os.path.join(
            settings.EMERGENZE_UPLOAD,
            os.path.relpath(
                filepath,
                fake_upload.uploadfolder
            ),
            filename
        )
        Path(os.path.dirname(dest)).mkdir(parents=True, exist_ok=True)

        shutil.move(
            filepath,
            dest
        )

        rdest = os.path.join(
            os.path.basename(settings.EMERGENZE_UPLOAD),
            os.path.relpath(dest, settings.EMERGENZE_UPLOAD)
        )
    return rdest

def valida_nuova_comunicazione(form):
    """ """
    fieldname = 'incarico_id'

    _, msg = IS_IN_DB(db(db.incarico), db.incarico.id)(form.vars[fieldname])

    if msg:
        form.errors[fieldname] = msg

def valida_nuova_comunicazione_da_intervento(form):
    """ """
    fieldname = 'intervento_id'

    _, msg = IS_IN_DB(db(db.intervento), db.intervento.intervento_id, error_message=f"Value not in database: {form.vars[fieldname]}")(form.vars[fieldname])
    
    _, msg1 = IS_IN_DB(db(db.segnalazione_da_vt), db.segnalazione_da_vt.intervento_id, error_message=f"Value not in database: {form.vars[fieldname]}")(form.vars[fieldname])

    if None in [msg, msg1]:
        form.vars['is_intervento'] = (msg is None)
    else:
        form.errors[fieldname] = msg

def create(lavorazione_id, mittente, operatore=None, testo=None, allegato=None):
    """
    lavorazione_id @integer :
    mittente        @string :
    operatore       @string :
    testo           @string :
    allegato        @string :
    """

    assert not lavorazione_id is None

    # segnalazione_utile = db.segnalazioni_utili(id=segnalazione_id)

    rdest = _upload(allegato)

    row = db.comunicazione.insert(
        lavorazione_id = lavorazione_id,
        mittente = mittente,
        testo = testo,
        allegato = rdest
    )

    rec = db(db.comunicazione.lavorazione_id==row["lavorazione_id"]).select(
        db.comunicazione.lavorazione_id,
        db.comunicazione.timeref,
        limitby = (0,1,),
        orderby = ~db.comunicazione.timeref
    ).first()

    db.log.insert(
        schema = 'segnalazioni',
        operatore = operatore,
        operazione = f'Inviata comunicazione a PC (relativa a Lavorazione "{lavorazione_id}")'
    )

    return rec

def create_by_incarico(incarico_id, *args, **kwargs):

    lavorazione_id = db(
        # (db.segnalazioni_utili.lavorazione_id==db.join_segnalazione_incarico.lavorazione_id) & \
        (db.join_segnalazione_incarico.incarico_id==incarico_id)
    ).select(
        db.join_segnalazione_incarico.lavorazione_id,
        limitby = (0,1,)
    ).first().lavorazione_id

    return create(lavorazione_id, *args, **kwargs)


def create_by_intervento(intervento_id, *args, is_intervento=None, **kwargs):

    logger.debug(f"Intervento: {intervento_id}")
    logger.debug(f"is intervento: {is_intervento}")
    
    if is_intervento is True:
    
        lavorazione_id = db(
            (db.segnalazioni_utili.lavorazione_id==db.join_segnalazione_incarico.lavorazione_id) & \
            (db.join_segnalazione_incarico.incarico_id==db.intervento.incarico_id) & \
            (db.intervento.intervento_id==intervento_id)
        ).select(
            db.join_segnalazione_incarico.lavorazione_id,
            limitby = (0,1,)
        ).first().lavorazione_id
    
    elif is_intervento is False:
        
        lavorazione_id = db(
            (db.segnalazioni_utili.lavorazione_id==db.join_segnalazione_lavorazione.lavorazione_id) & \
            (db.join_segnalazione_lavorazione.segnalazione_id==db.segnalazione_da_vt.segnalazione_id) & \
            (db.segnalazione_da_vt.intervento_id==intervento_id)
        ).select(
            db.segnalazioni_utili.lavorazione_id,
            limitby = (0,1,)
        ).first().lavorazione_id
        
    else:
        raise ValueError()

    return create(lavorazione_id, *args, **kwargs)


# def test_lavorazione_da_intervento(intervento_id):
#     """ """
#     lavorazione_id = db(
#         (db.segnalazioni_utili.lavorazione_id==db.join_segnalazione_incarico.lavorazione_id)
#     ).select(
#         left = db.
#     ).first().lavorazione_id
    

def create_by_segnalazione(segnalazione_id, *args, **kwargs):
    """ NON IN USO """

    lavorazione_id = db(db.segnalazioni_utili.id==segnalazione_id).select(
        db.segnalazioni_utili.lavorazione_id,
        limitby = (0,1,)
    ).first().lavorazione_id

    return create(lavorazione_id, *args, **kwargs)

def render(row):
    """ """

    out = {
        # 'idIntervento': row.idIntervento,
        'operatore': 'operatore di PC',
        'testo': row.testo,
        # 'files': [allegato]
    }

    if not row.allegato is None:

        with open(os.path.join(settings.EMERGENZE_UPLOAD, *(row.allegato.split(os.path.sep)[1:])), 'rb') as ff:
            encoded_string = base64.b64encode(ff.read()).decode()

        allegato = {
            'fileName': os.path.basename(row.allegato),
            'file': encoded_string
        }

        out['files'] = [allegato]

    return out

check = f"({db.incarico._rname}.id_uo ilike 'com_PO%')"
check_da_pm = f"({db.comunicazione._rname}.mittente ilike '%Polizia Locale')"

def fetch2(lavorazione_id:int, timeref):
    """ """

    rec = db(
        (db.comunicazione.lavorazione_id == db.segnalazione_lavorazione.id) & \
        (db.comunicazione.lavorazione_id==lavorazione_id) & \
        (db.comunicazione.timeref==timeref)
    ).select(
        db.segnalazione_lavorazione.id, # .with_alias('lavorazione_id'),
        db.join_segnalazione_lavorazione.segnalazione_id, # .with_alias('segnalazione_id'),
        db.join_segnalazione_incarico.incarico_id, # .with_alias('incarico_id'),
        db.segnalazione_da_vt.intervento_id.with_alias('segnalazione_intervento_id'),
        db.intervento.intervento_id.with_alias('incarico_intervento_id'),
        db.comunicazione.timeref.with_alias('comunicazione_id'),
        db.comunicazione.testo.with_alias('testo'),
        db.comunicazione.allegato.with_alias('allegato'),
        db.comunicazione.mittente,
        check_da_pm,
        left = (
            db.join_segnalazione_lavorazione.on(
                (
                    db.join_segnalazione_lavorazione.lavorazione_id==db.segnalazione_lavorazione.id) & \
                    (db.join_segnalazione_lavorazione.segnalazione_id==db.segnalazione_da_vt.segnalazione_id)
                ),
            db.join_segnalazione_incarico.on(
                (db.join_segnalazione_incarico.lavorazione_id==db.segnalazione_lavorazione.id) & \
                (db.join_segnalazione_incarico.incarico_id==db.intervento.incarico_id)
            ),
        ),
        distinct = "segnalazioni.t_comunicazioni_segnalazioni.data_ora_stato",
        orderby = (~db.comunicazione.timeref, ~db.segnalazione_da_vt.id, ~db.intervento.id),
        limitby = (0,1,)
    ).first()
    
    logger.debug(rec)

    return rec and (rec.segnalazione_intervento_id or rec.incarico_intervento_id, [True, rec(check_da_pm)], render(rec),)

def fetch(lavorazione_id:int, timeref):
    """ """

    dbset = db(
        (db.comunicazione.lavorazione_id == db.segnalazione_lavorazione.id) & \
        (db.join_segnalazione_incarico.lavorazione_id==db.segnalazione_lavorazione.id) & \
        (db.join_segnalazione_incarico.incarico_id==db.incarico.id) & \
        (db.incarico.id==db.intervento.incarico_id) & \
        (db.comunicazione.lavorazione_id==lavorazione_id) & \
        (db.comunicazione.timeref==timeref) & \
        check
    )

    rec = dbset.select(
        db.intervento.intervento_id.with_alias('idIntervento'),
        db.comunicazione.testo.with_alias('testo'),
        db.comunicazione.allegato.with_alias('allegato'),
        db.comunicazione.mittente,
        check,
        check_da_pm
    ).first()

    logger.debug(rec)

    return rec and (rec.idIntervento, [rec[check], rec[check_da_pm]], render(rec),)


def after_insert_comunicazione_segnalazione(*args, **kwargs):
    """ """
    logger.debug(args)
    logger.debug(kwargs)
    
    result = fetch2(*args, **kwargs)
    logger.debug(result)
    if not result is None:
        idIntervento, checks, payload = result
        _, da_pm = checks
        if not da_pm:
            Intervento.message(idIntervento, **payload)
