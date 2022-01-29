# -*- coding: utf-8 -*-

from .common import db, logger
from pydal import geoPoint
from pydal.validators import *

DEFAULT_TIPO_SEGNALANTE = 1 # Presidio territoriale (Volontariato e PM)
DEFAULT_DESCRIZIONE_UTILIZZATORE = db(db.profilo_utilizatore.id==6).select(db.profilo_utilizatore.descrizione).first().descrizione

GEOM_SRID = 3003

TABELLA_CIVICI = db.tipo_oggetto_rischio(descrizione='Civici')


def valida_nuova_segnalazione(form):
    """ """
    _, msg = IS_IN_DB(db(db.evento), db.evento.id)(form.vars['evento_id'])
    if msg:
        form.errors['evento_id'] = msg

    _, msg = IS_EMPTY_OR(IS_IN_DB(db(db.civico), db.civico.id))(form.vars['civico_id'])
    if msg:
        form.errors['civico_id'] = msg

def valida_segnalazione(form):
    """ """
    _, msg = IS_IN_DB(
        db(db.segnalazione),
        db.segnalazione.id
    )(form.vars['segnalazione_id'])

    if msg:
        form.errors['segnalazione_id'] = msg

def valida_intervento(form):
    _, msg = IS_IN_DB(
        db(db.intervento),
        db.intervento.intervento_id
    )(form.vars['intervento_id'])

    if msg:
        form.errors['intervento_id'] = msg


def create(evento_id, nome, descrizione, lon_lat, criticita_id, operatore,
    tipo_segnalante_id=DEFAULT_TIPO_SEGNALANTE, municipio_id=None,
    telefono=None, note=None, nverde=False, note_geo=None,
    civico_id=None, persone_a_rischio=None, tabella_oggetto_id=None,
    note_riservate=None, assegna=True
):
    """
    Funzione dedicata alla creazione di una nuova Segnalazione.

    evento_id          @integer : Id evento,
    nome                @string : Nome segnalante,
    descrizione         @string : Descrizione segnalazione,
    lon_lat               @list : Coordinate,
    criticita_id        @string : Id tipo criticità,
    operatore           @string : Identificativo operatore (matricola o CF)
    telefono            @string : Numero di telefono segnalante,
    note                @string : Note del segnalante,
    nverde                @bool : Attivazione num verde,
    note_geo            @string : Note di geolocalizzazione,
    civico_id          @integer : Id civico,
    persone_a_rischio     @bool : Segnalazione presenza persone a rischio
    tabella_oggetto_id @integer : Id tabella oggetto a rischio (eg.: 'geodb.fiumi')
    note_riservate      @string : Note riservate
    assegna            @boolean : Se vero esprime che l'operatore segnalante prende
                                  in carico la stessa segnalazione

    Restituisce: Id nuova segnalazione
    """

    # Insert SEGNALANTE

    segnalante_id = db.segnalante.insert(
        # id = new_id(db.segnalante),
        tipo_segnalante_id = tipo_segnalante_id,
        nome = nome,
        telefono = telefono,
        note = note
    )
    logger.debug(f'Nuovo segnalante: {db.segnalante[segnalante_id]}')

    if municipio_id is None:
        municipio_id = db(
            db.municipio.geom.st_transform(4326).st_intersects(geoPoint(*lon_lat))
        ).select(db.municipio.codice).first().codice

    # Insert SEGNALAZIONE

    segnalazione_id = db.segnalazione.insert(
        # id = new_id(db.segnalazione),
        uo_ins = DEFAULT_DESCRIZIONE_UTILIZZATORE,
        segnalante_id = segnalante_id,
        descrizione = descrizione,
        criticita_id = criticita_id,
        evento_id = evento_id,
        geom = geoPoint(*lon_lat),
        operatore = operatore,
        municipio_id = municipio_id,
        rischio = persone_a_rischio,
        nverde = nverde,
        civico_id = civico_id,
        note = note_geo
    )

    # Insert OGGETTO A RISCHIO

    if tabella_oggetto_id==TABELLA_CIVICI.id and not civico_id is None:
        # L'oggetto a rischio è un civico
        _ = db.join_oggetto_rischio.insert(
            segnalazione_id = segnalazione_id,
            tipo_oggetto_id = tabella_oggetto_id,
            oggetto_id = civico_id
        )
        logger.debug(f'Aggiunto civico a rischio: {_}')
    elif not tabella_oggetto_id is None:
        # L'oggetto a rischio è qualcosaltro
        # Tipo oggetto a rischio dalla tabella segnalazioni.tipo_oggetti_rischio
        tabella_oggetto = db.tipo_oggetto_rischio[tabella_oggetto_id]
        # Oggetto tabella definito in db e identificato da tabella_oggetto
        oggetto_tabella = next(filter(lambda tt: tt._rname==tabella_oggetto.nome_tabella, db))

        POINT3003 = f"ST_Transform(ST_SetSRID(ST_GeomFromText('POINT({lon_lat[0]} {lon_lat[1]})'), 4326), {GEOM_SRID})"
        risko = db(oggetto_tabella).select(
            oggetto_tabella[tabella_oggetto.campo_identificativo].with_alias('myid'),
            orderby = f"ST_Distance({POINT3003}, geom)",
            limitby = (0,1,)
        ).first()
        logger.debug(f'Trovato oggetto a rischio: {risko}')

        _ = db.join_oggetto_rischio.insert(
            segnalazione_id = segnalazione_id,
            tipo_oggetto_id = tabella_oggetto.id,
            oggetto_id = risko.myid
        )
        logger.debug(f'Aggiunto oggetto a rischio: {_}')

    # Insert NOTE RISERVATE

    if not note_riservate is None:
        operatore_ = db(db.v_utenti_sistema.matricola==operatore).select(
            db.v_utenti_sistema.nome,
            db.v_utenti_sistema.cognome,
            db.v_utenti_sistema.descrizione,
            limitby = (0,1,)
        ).first()
        if not operatore_ is None:
            _ = db.segnalazione_riservata.insert(
                segnalazione_id = segnalazione_id,
                mittente = f"{operatore_.nome} {operatore_.cognome} ({operatore_.descrizione})",
                testo = note_riservate
            )
            logger.debug(f"Nuova nota riservata: {db.segnalazione_riservata[_]}")

    # Insert LOG

    db.log.insert(
        schema = 'segnalazioni',
        operatore = operatore,
        operazione = f'Creazione segnalazione {segnalazione_id}'
    )

    if assegna:
        upgrade(segnalazione_id, operatore)

    return segnalazione_id


def verbatel_create(intervento_id, *args, **kwargs):
    segnalazione_id = create(*args, **kwargs)
    # Registrazione intervento id di Verbatel

    db.intervento.insert(
        segnalazione_id = segnalazione_id,
        intervento_id = intervento_id
    )

    return segnalazione_id

def update_(segnalazione_id, segnalante_id, lon_lat=None, persone_a_rischio=None,
    **kwargs):
    """

    Funzione dedicata all'aggiornamento dei dati di Segnalazione

    """

    # if not criticita is None:
    #     kwargs['id_criticita'] = db(
    #         (db.tipo_criticita.descrizione.lower()==criticita.lower()) & \
    #         (db.tipo_criticita.valido==True)
    #     ).select(db.tipo_criticita.id).first().id

    if not lon_lat is None:
        kwargs['geom'] = geoPoint(*lon_lat)
        kwargs['municipio_id'] = db(db.municipio.geom.st_transform(4326).st_intersects(geoPoint(*lon_lat))).select(db.municipio.codice).first().codice

    if not persone_a_rischio is None:
        kwargs['rischio'] = persone_a_rischio

    db(db.segnalazione.id==segnalazione_id).update(
        segnalante_id = segnalante_id,
        **kwargs
    )


def update(segnalazione_id, nome, telefono, operatore, note=None,
    tipo_segnalante=DEFAULT_TIPO_SEGNALANTE, **kwargs
):
    """

    Funzione dedicata alla procedura di aggiornamento dei dati di Segnalazione

    """

    if kwargs:

        # Insert SEGNALANTE

        segnalante_id = db.segnalante.insert(
            # id = new_id(db.segnalante),
            tipo_segnalante_id = tipo_segnalante,
            nome = nome,
            telefono = telefono,
            note = note
        )
        logger.debug(f'Nuovo segnalante: {db.segnalante[segnalante_id]}')

        update_(segnalazione_id, segnalante_id, **kwargs)
        return 'Ok'


def verbatel_update(intervento_id, *args, **kwars):
    """"""
    segnalazione_id = db.intervento(intervento_id=intervento_id).segnalazione_id
    return update(segnalazione_id, *args, **kwars)


def upgrade(segnalazione_id, operatore, profilo_id=6, sospeso=False):
    """

    Funzione dedicata alla presa in carico della Segnalazione

    segnalazione_id @integer : Id segnalazione
    operatore        @string : Identificativo operatore (matricola o CF)
    profilo_id      @integer : Id del profilo utilizzatore assegnatario della
                               segnalazione (default: 'Emergenza Distretto PM')
    sospeso         @boolean : Sospendere la segnalazione?

    Cosa restiuisce:

    """

    segnalazione = db.segnalazione[segnalazione_id]
    profilo = db.profilo_utilizatore[profilo_id]

    lavorazione_id = db.segnalazione_lavorazione.insert(
        profilo_id = profilo.id,
        geom = segnalazione.geom
    )

    db.join_segnalazione_lavorazione.insert(
        lavorazione_id = lavorazione_id,
        segnalazione_id = segnalazione.id,
        sospeso = sospeso
    )

    if sospeso and profilo_id==3:
        raise NotImplementedError()

    message = f'La segnalazione n. {lavorazione_id} è stata presa in carico come profilo {profilo.descrizione}'
    _ = db.storico_segnalazione_lavorazione.insert(
        lavorazione_id = lavorazione_id,
        aggiornamento = message
    )
    logger.debug(f'{_}: {message}')

    # Insert LOG

    message =  f'Aggiornato segnalazione {segnalazione.id}'
    _ = db.log.insert(
        schema = 'segnalazioni',
        operatore = operatore,
        operazione = message
    )
    logger.debug(f'{_}: {message}')
