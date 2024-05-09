# -*- coding: utf-8 -*-

from .. import settings
from ..common import db, logger
import shutil, os
from py4web import Field
from pathlib import Path
from pydal.validators import *

fake_upload = Field('allegato', 'upload',
    uploadfolder = settings.UPLOAD_FOLDER, uploadseparate=True
)

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
