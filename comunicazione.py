# -*- coding: utf-8 -*-

from . import settings
from .common import db, logger
import shutil, os
from py4web import Field
from pathlib import Path

fake_upload = Field('allegato', 'upload',
    uploadfolder = settings.UPLOAD_FOLDER, uploadseparate=True
)

UPLOAD_CONFIGURED = (fake_upload.uploadfolder and settings.EMERGENZE_UPLOAD)

def create(segnalazione_id, mittente, testo=None, allegato=None):
    """ """

    row = db.segnalazioni_utili(id=segnalazione_id)

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
        rdest = os.path.relpath(dest, settings.EMERGENZE_UPLOAD)

    row = db.comunicazione.insert(
        lavorazione_id = row.lavorazione_id,
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

    return rec