# -*- coding: utf-8 -*-

from .common import db
from pydal import geoPoint

DEFAULT_TIPO_SEGNALANTE = 1 # Presidio territoriale (Volontariato e PM)
DEFAULT_DESCRIZIONE_UTILIZZATORE = db(db.profilo_utilizatore.id==6).select(db.profilo_utilizatore.descrizione).first().descrizione

GEOM_SRID = 3003

# DEFAULT_OGGETTO_CIVICO_A_RISCHIO = db(db.tipo_oggetto_rischio.descrizione=='Civici').select(db.tipo_oggetto_rischio.id).first().id

TIPO_OGGETTI_A_RISCHIO = {row.nome_tabella: row
    for row in db(db.tipo_oggetto_rischio.descrizione).select(
        db.tipo_oggetto_rischio.id,
        db.tipo_oggetto_rischio.descrizione,
        db.tipo_oggetto_rischio.nome_tabella,
        db.tipo_oggetto_rischio.campo_identificativo
    )
}

new_id = lambda table: db(table).select(table.id, orderby=~table.id, limitby=(0,1,)).first().id+1

def create(evento_id, nome, descrizione, lon_lat, criticita, operatore, # municipio_id=None,
    telefono=None, note=None, nverde=False, note_geo=None,
    civico_id=None, persone_a_rischio=None, tabella_oggetto=None, oggetto_id=None,
    note_riservate=None
):

    # Insert SEGNALANTE

    segnalante_id = db.segnalante.insert(
        id = new_id(db.segnalante),
        tipo_segnalante_id = DEFAULT_TIPO_SEGNALANTE,
        nome_cognome = nome,
        telefono = telefono,
        note = note
    )

    POINT3003 = f"ST_Transform(ST_SetSRID(ST_GeomFromText('POINT({lon_lat[0]} {lon_lat[1]})'), 4326), {GEOM_SRID})"

    municipio_id = db(db.municipio.geom.st_transform(4326).st_intersects(geoPoint(*lon_lat))).select(db.municipio.codice).first().codice

    # Insert DEGNALAZIONE

    segnalazione_id = db.segnalazione.insert(
        id = new_id(db.segnalazione),
        uo_ins = DEFAULT_DESCRIZIONE_UTILIZZATORE,
        segnalante_id = segnalante_id,
        descrizione = descrizione,
        id_criticita = db(
            (db.tipo_criticita.descrizione.lower()==criticita.lower()) & \
            (db.tipo_criticita.valido==True)
        ).select(db.tipo_criticita.id).first().id,
        evento_id = evento_id,
        geom = geoPoint(*lon_lat),
        operatore = operatore,
        municipio_id = int(municipio_id),
        rischio = persone_a_rischio,
        nverde = nverde,
        civico_id = civico_id,
        note_geo = note_geo
    )

    # Insert OGGETTO A RISCHIO

    if tabella_oggetto in TIPO_OGGETTI_A_RISCHIO.values():
        if TIPO_OGGETTI_A_RISCHIO[tabella_oggetto].descrizione == 'Civici':
            db.join_oggetto_richio.insert(
                segnalazione_id = segnalazione_id,
                tipo_oggetto_id = TIPO_OGGETTI_A_RISCHIO[tabella_oggetto].id,
                oggetto_id = civico_id
            )
        else:
            oggetto_tabella = next(filter(lambda tt: tt._rname==tabella_oggetto, db))
            risko = db(oggetto_tabella).select(
                oggetto_tabella[TIPO_OGGETTI_A_RISCHIO[tabella_oggetto].campo_identificativo].with_alias('myid'),
                orderby = f"ST_Distance({POINT3003}, geom)",
                limitby = (0,1,)
            )
            db.join_oggetto_richio.insert(
                segnalazione_id = segnalazione_id,
                tipo_oggetto_id = TIPO_OGGETTI_A_RISCHIO[tabella_oggetto].id,
                oggetto_id = risko.myid
            )
    elif tabella_oggetto is None:
        pass
    else:
        raise ValueError(tabella_oggetto)

    # Insert NOTE RISERVATE

    if not note_riservate is None:
        operatore_ = db(db.v_utenti_sistema.matricola==operatore).select(
            db.v_utenti_sistema.nome,
            db.v_utenti_sistema.cognome,
            db.v_utenti_sistema.descrizione,
            limitby = (0,1,)
        ).first()
        if not operatore_ is None:
            db.segnalazione_riservata.insert(
                segnalazione_id = segnalazione_id,
                mittente = f"{operatore_.nome} {operatore_.cognome} ({operatore_.descrizione})",
                testo = note_riservate
            )

    db.log.insert(
        schema = 'segnalazioni',
        operatore = operatore,
        operazione = f'Creazione segnalazione {segnalazione_id}'
    )

    return segnalazione_id
