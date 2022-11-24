# -*- coding: utf-8 -*-

from ..common import settings, db, logger
from .. import incarico
from pydal import geoPoint
from pydal.validators import *
import json
import datetime

DEFAULT_TIPO_SEGNALANTE = 1  # Presidio territoriale (Volontariato e PM)
DEFAULT_DESCRIZIONE_UTILIZZATORE = (
    db(db.profilo_utilizatore.id == 6)
    .select(db.profilo_utilizatore.descrizione)
    .first()
    .descrizione
)

GEOM_SRID = 3003

TABELLA_CIVICI = db.tipo_oggetto_rischio(descrizione="Civici")


def valida_nuova_segnalazione(form):
    """ """
    _, msg = IS_IN_DB(db(db.evento), db.evento.id)(form.vars["evento_id"])
    if msg:
        form.errors["evento_id"] = msg

    _, msg = IS_EMPTY_OR(IS_IN_DB(db(db.civico), db.civico.id))(form.vars["civico_id"])
    if msg:
        form.errors["civico_id"] = msg


def valida_segnalazione(form):
    """ """
    _, msg = IS_IN_DB(db(db.segnalazione), db.segnalazione.id)(
        form.vars["segnalazione_id"]
    )

    if msg:
        form.errors["segnalazione_id"] = msg


def valida_intervento(form):
    _, msg = IS_IN_DB(db(db.intervento), db.intervento.intervento_id)(
        form.vars["intervento_id"]
    )

    if msg:
        form.errors["intervento_id"] = msg


def create(
    evento_id,
    nome,
    descrizione,
    lon_lat,
    criticita_id,
    operatore,
    tipo_segnalante_id=DEFAULT_TIPO_SEGNALANTE,
    municipio_id=None,
    telefono=None,
    note=None,
    nverde=False,
    note_geo=None,
    civico_id=None,
    persone_a_rischio=None,
    tabella_oggetto_id=None,
    note_riservate=None,
    assegna=True,
    intervento_id=None,
    **kwargs,
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

    Restituisce: Id nuovo incarico o None
    """

    # Insert SEGNALANTE

    segnalante_id = db.segnalante.insert(
        # id = new_id(db.segnalante),
        tipo_segnalante_id=tipo_segnalante_id,
        nome=nome,
        telefono=telefono,
        note=note,
    )
    logger.debug(f"Nuovo segnalante: {db.segnalante[segnalante_id]}")

    if municipio_id is None:
        municipio_id = (
            db(db.municipio.geom.st_transform(4326).st_intersects(geoPoint(*lon_lat)))
            .select(db.municipio.codice)
            .first()
            .codice
        )

    # Insert SEGNALAZIONE

    segnalazione_id = db.segnalazione.insert(
        # id = new_id(db.segnalazione),
        uo_ins=DEFAULT_DESCRIZIONE_UTILIZZATORE,
        segnalante_id=segnalante_id,
        descrizione=descrizione,
        criticita_id=criticita_id,
        evento_id=evento_id,
        geom=geoPoint(*lon_lat),
        operatore=operatore,
        municipio_id=municipio_id,
        rischio=persone_a_rischio,
        nverde=nverde,
        civico_id=civico_id,
        note=note_geo,
    )

    if not intervento_id is None:
        # Registrazione segnalazione da Verbatel
        db.segnalazione_da_vt.insert(
            segnalazione_id=segnalazione_id, intervento_id=intervento_id
        )

    # Insert OGGETTO A RISCHIO

    if tabella_oggetto_id == TABELLA_CIVICI.id and not civico_id is None:
        # L'oggetto a rischio è un civico
        _ = db.join_oggetto_rischio.insert(
            segnalazione_id=segnalazione_id,
            tipo_oggetto_id=tabella_oggetto_id,
            oggetto_id=civico_id,
        )
        logger.debug(f"Aggiunto civico a rischio: {_}")
    elif not tabella_oggetto_id is None:
        # L'oggetto a rischio è qualcosaltro
        # Tipo oggetto a rischio dalla tabella segnalazioni.tipo_oggetti_rischio
        tabella_oggetto = db.tipo_oggetto_rischio[tabella_oggetto_id]
        # Oggetto tabella definito in db e identificato da tabella_oggetto
        oggetto_tabella = next(
            filter(lambda tt: tt._rname == tabella_oggetto.nome_tabella, db)
        )

        POINT3003 = f"ST_Transform(ST_SetSRID(ST_GeomFromText('POINT({lon_lat[0]} {lon_lat[1]})'), 4326), {GEOM_SRID})"
        risko = (
            db(oggetto_tabella)
            .select(
                oggetto_tabella[tabella_oggetto.campo_identificativo].with_alias(
                    "myid"
                ),
                orderby=f"ST_Distance({POINT3003}, geom)",
                limitby=(
                    0,
                    1,
                ),
            )
            .first()
        )
        logger.debug(f"Trovato oggetto a rischio: {risko}")

        _ = db.join_oggetto_rischio.insert(
            segnalazione_id=segnalazione_id,
            tipo_oggetto_id=tabella_oggetto.id,
            oggetto_id=risko.myid,
        )
        logger.debug(f"Aggiunto oggetto a rischio: {_}")

    # Insert NOTE RISERVATE

    if not note_riservate is None:
        operatore_ = (
            db(db.v_utenti_sistema.matricola == operatore)
            .select(
                db.v_utenti_sistema.nome,
                db.v_utenti_sistema.cognome,
                db.v_utenti_sistema.descrizione,
                limitby=(
                    0,
                    1,
                ),
            )
            .first()
        )
        if not operatore_ is None:
            _ = db.segnalazione_riservata.insert(
                segnalazione_id=segnalazione_id,
                mittente=f"{operatore_.nome} {operatore_.cognome} ({operatore_.descrizione})",
                testo=note_riservate,
            )
            logger.debug(f"Nuova nota riservata: {db.segnalazione_riservata[_]}")

    # Insert LOG

    db.log.insert(
        schema="segnalazioni",
        operatore=operatore,
        operazione=f"Creazione segnalazione {segnalazione_id}",
    )

    #
    if assegna:
        lavorazione_id, incarico_id = upgrade(
            segnalazione_id, operatore, profilo_id=settings.PM_PROFILO_ID, **kwargs
        )
        return (
            segnalazione_id,
            lavorazione_id,
            incarico_id,
        )
    else:
        return (
            segnalazione_id,
            None,
            None,
        )


def verbatel_create(intervento_id, **kwargs):
    """ """

    segnalazione_id, lavorazione_id, incarico_id = create(
        intervento_id=intervento_id, **kwargs
    )

    # Registrazione intervento id di Verbatel assegnato all'incarico
    if not incarico_id is None:
        db.intervento.insert(incarico_id=incarico_id, intervento_id=intervento_id)

    return incarico_id


def update_(segnalazione_id, segnalante_id, persone_a_rischio=None, ceduta=None, **kwargs):
    """Funzione dedicata all'aggiornamento dei dati di Segnalazione"""

    # if not criticita is None:
    #     kwargs['id_criticita'] = db(
    #         (db.tipo_criticita.descrizione.lower()==criticita.lower()) & \
    #         (db.tipo_criticita.valido==True)
    #     ).select(db.tipo_criticita.id).first().id

    if not persone_a_rischio is None:
        kwargs["rischio"] = persone_a_rischio

    if ceduta:
        lavorazione = db.join_segnalazione_lavorazione(segnalazione_id=segnalazione_id)
        tt = db(
            db.segnalazione_lavorazione.id == lavorazione.lavorazione_id
        ).update(
            profilo_id = settings.PC_PROFILO_ID
        )

    db(db.segnalazione.id == segnalazione_id).update(
        segnalante_id=segnalante_id, **db.segnalazione._filter_fields(kwargs)
    )


def update(
    segnalazione_id,
    nome,
    telefono,
    operatore,
    note=None,
    tipo_segnalante=DEFAULT_TIPO_SEGNALANTE,
    **kwargs,
):
    """

    Funzione dedicata alla procedura di aggiornamento dei dati di Segnalazione

    """

    if kwargs:

        # Insert SEGNALANTE

        segnalante_id = db.segnalante.insert(
            # id = new_id(db.segnalante),
            tipo_segnalante_id=tipo_segnalante,
            nome=nome,
            telefono=telefono,
            note=note,
        )
        logger.debug(f"Nuovo segnalante: {db.segnalante[segnalante_id]}")

        update_(segnalazione_id, segnalante_id, **kwargs)
        return "Ok"


def verbatel_update(intervento_id, lon_lat=None, **kwargs):
    """ """

    segnalazione = (
        db(
            (db.intervento.intervento_id == intervento_id)
            & (db.intervento.incarico_id == db.incarichi_utili.id)
        )
        .select(
            db.intervento.incarico_id.with_alias("incarico_id"),
            db.incarichi_utili.segnalazione_id.with_alias("segnalazione_id"),
            distinct=f"{db.incarichi_utili._rname}.id",
            limitby=(
                0,
                1,
            ),
        )
        .first()
    )

    if not lon_lat is None:
        kwargs["geom"] = geoPoint(*lon_lat)
        kwargs["municipio_id"] = (
            db(db.municipio.geom.st_transform(4326).st_intersects(geoPoint(*lon_lat)))
            .select(db.municipio.codice)
            .first()
            .codice
        )
        kwargs["uo_id"] = incarico.get_uo_id(kwargs["municipio_id"])

    # Così supporto la chiamata anche con parametro segnalazione_id anche se non
    # servirebbe, a questo punto lo uso come check di robustezza
    # if 'segnalazione_id' in kwargs:
    # assert kwargs.pop('segnalazione_id') == segnalazione.segnalazione_id
    # Rimosso perché il valore passato come segnalazione_id era l'incarico_id
    # per incomprensione con Verbatel

    # Aggiornamento dati di Segnalazione
    update(segnalazione.segnalazione_id, **kwargs)

    # Aggiornamento dati di incarico
    return incarico.upgrade(segnalazione.incarico_id, **kwargs)


def upgrade(
    segnalazione_id,
    operatore,
    profilo_id,
    sospeso=False,
    preview=None,
    stato_id=incarico.DEFAULT_TIPO_STATO,
    parziale=False,
):
    """

    Funzione dedicata alla presa in carico della Segnalazione

    segnalazione_id @integer : Id segnalazione
    operatore        @string : Identificativo operatore (matricola o CF)
    profilo_id      @integer : Id del profilo utilizzatore assegnatario della
                               segnalazione
    sospeso         @boolean : Sospendere la segnalazione?
    preview        @datetime : Inizio previsto incarico
    stato_id        @integer : Id stato incarico
                               (Default: 'Inviato ma non ancora preso in carico')

    Cosa restiuisce:
        lavorazione_id, incarico_id
    """

    segnalazione = db.segnalazione[segnalazione_id]
    profilo = db.profilo_utilizatore[profilo_id]
    assert not profilo is None
    assert not segnalazione is None

    lavorazione_id = db.segnalazione_lavorazione.insert(
        profilo_id=profilo.id, geom=segnalazione.geom
    )

    db.join_segnalazione_lavorazione.insert(
        lavorazione_id=lavorazione_id, segnalazione_id=segnalazione.id, sospeso=sospeso
    )

    if sospeso and profilo_id == settings.PM_PROFILO_ID:
        raise NotImplementedError()

    message = f"La segnalazione n. {lavorazione_id} è stata presa in carico come profilo {profilo.descrizione}"
    _ = db.storico_segnalazione_lavorazione.insert(
        lavorazione_id=lavorazione_id, aggiornamento=message
    )
    logger.debug(f"{_}: {message}")

    # Insert LOG

    message = f"Aggiornato segnalazione {segnalazione.id}"
    _ = db.log.insert(schema="segnalazioni", operatore=operatore, operazione=message)
    logger.debug(f"{_}: {message}")

    # Incarico
    if not stato_id is None:

        descrizione_incarico = segnalazione.descrizione

        incarico_id = incarico.create(
            segnalazione_id=segnalazione.id,
            lavorazione_id=lavorazione_id,
            profilo_id=profilo.id,
            descrizione=descrizione_incarico,
            municipio_id=segnalazione.municipio_id,
            stato_id=stato_id,
            preview=preview,
            parziale=parziale,
        )
        logger.debug(f"Creato incarico: {incarico_id}")

        return lavorazione_id, incarico_id

    else:

        return lavorazione_id, None


def after_insert_lavorazione(id):
    """
    id @integer : Id della nuova lavorazione
    """

    rec = (
        db(
            (db.segnalazione_lavorazione.id == id)
            & (
                db.segnalazione_lavorazione.id
                == db.join_segnalazione_lavorazione.lavorazione_id
            )
            & (db.join_segnalazione_lavorazione.segnalazione_id == db.segnalazione.id)
            & ~db.tipo_criticita.id.belongs([7, 12])  # Segnalazione di intetresse di PL
            & (db.segnalazione.criticita_id == db.tipo_criticita.id)
            & (db.tipo_criticita.valido == True)
            & (db.segnalazione_lavorazione.in_lavorazione == True)
            # & (db.join_segnalazione_lavorazione.sospeso == False)
        )
        .select(
            db.segnalazione.ALL,
            db.segnalazione_lavorazione.with_alias("lavorazione").ALL,
            distinct=f"{db.segnalazione._rname}.id",
            orderby=(
                db.segnalazione.id,
                ~db.segnalazione_lavorazione.id,
            ),
        )
        .first()
    )

    if not rec is None and rec.lavorazione.profilo_id != settings.PM_PROFILO_ID:
        descrizione_incarico = f"""Richiesta sola presa visione della segnalazione:
{rec.segnalazione.descrizione}.
{incarico.WARNING}"""

        incarico_id = incarico.create(
            segnalazione_id=rec.segnalazione.id,
            lavorazione_id=id,
            profilo_id=settings.PM_PROFILO_ID,
            descrizione=descrizione_incarico,
            municipio_id=rec.segnalazione.municipio_id,
        )
        logger.debug(f"Creato incarico: {incarico_id}")

        # a quanto pare l'evento insert lanciato al passo precedente
        # all'interno dello stesso trigger non viene intercettato
        # per cui lancio a mano la callback seguente.
        incarico.after_insert_incarico(incarico_id)

        return incarico_id


# def render(row):
#
#     if row.in_lavorazione is None:
#         stato = 1 # Da prendere in carico
#     elif row.in_lavorazione is True:
#         stato = 2 # In lavorazione
#     else:
#         stato = 3 # Chiusa
#
#     localizzazione = {}
#     indirizzo = f'{row.desvia}, {row.civico_numero}{(row.civico_lettera and row.civico_lettera.upper()) or ""}{row.civico_colore or ""}' #.encode()
#     if row.civico_id is None:
#         localizzazione['tipoLocalizzazione'] = 3
#         localizzazione['daSpecificare'] = indirizzo
#     else:
#         localizzazione['tipoLocalizzazione'] = 1
#         localizzazione['civico'] = indirizzo
#
#     # tipoRichiesta TODO
#     #   1: Intervento da gestire dalla PL
#     #   2: Intervento gestito dalla PC su cui PL ha visibilità. In questo caso è
#     #       necessario notificare le modifiche della segnalazione a Verbatel.
#     #   3: Richiesta di ausilio su intervento gestito dalla PC
#
#
#     if row.profilo_utilizatore.id==6:
#         tipoRichiesta = 1
#     elif row.profilo_utilizatore.id==3:
#         tipoRichiesta = 2
#     else:
#         raise NotImplementedError()
#
#     geom = json.loads(row.geom)
#     lon, lat = geom['coordinates']
#
#     # datiPattuglia DA DEFINIRE
#     # motivoRifiuto
#
#     # dataInLavorazione
#     # dataChiusura
#
#     # dataRifiuto
#     # dataRiapertura
#
#     return dict(
#         tipoRichiesta = tipoRichiesta,
#         stato = stato,
#         idSegnalazione = row.id,
#         eventoId = row.evento_id,
#         operatore = row.operatore,
#
#         nomeStrada = row.desvia,
#         codiceStrada = row.codvia,
#
#         tipoIntervento = row.criticita_id,
#         noteOperative = row.note,
#         reclamante = row.reclamante,
#         telefonoReclamante = row.telefono,
#         # tipoRichiesta =
#         dataInserimento = row.inizio.isoformat(),
#         longitudine = lon,
#         latitudine = lat,
#         **localizzazione
#     )


# def fetch(id):
#
#     result = db(
#         (db.segnalazione.id==id) & \
#         (db.segnalante.id == db.segnalazione.segnalante_id) & \
#         (db.segnalazione.evento_id == db.evento.id) & \
#         "eventi.t_eventi.valido is not false"
#     ).select(
#         db.segnalazione.id.with_alias('id'),
#         db.segnalazione.inizio.with_alias('inizio'),
#         db.segnalazione.evento_id.with_alias('evento_id'),
#         db.segnalazione.operatore.with_alias('operatore'),
#         db.segnalazione.criticita_id.with_alias('criticita_id'),
#         db.segnalazione.civico_id.with_alias('civico_id'),
#         db.segnalazione.note.with_alias('note'),
#         db.segnalazione_lavorazione.in_lavorazione.with_alias('in_lavorazione'),
#         db.civico.geom.st_distance(
#             db.segnalazione.geom.st_transform(3003)
#         ).with_alias('distanza'),
#         db.civico.codvia.with_alias('codvia'),
#         db.civico.desvia.with_alias('desvia'),
#         db.civico.testo.with_alias('civico_numero'),
#         db.civico.lettera.with_alias('civico_lettera'),
#         db.civico.colore.with_alias('civico_colore'),
#         db.segnalazione.geom.st_asgeojson().with_alias('geom'),
#         db.segnalante.nome.with_alias('reclamante'),
#         db.segnalante.telefono.with_alias('telefono'),
#         db.profilo_utilizatore.id,
#         distinct = 'segnalazioni.t_segnalazioni."id"',
#         orderby = (
#             db.segnalazione.id,
#             db.civico.geom.st_distance(db.segnalazione.geom.st_transform(3003)),
#             ~db.segnalazione_lavorazione.id,
#             ~db.segnalazione_lavorazione.in_lavorazione,
#         ),
#         left = (
#             db.segnalazione.on(db.join_segnalazione_lavorazione.segnalazione_id==db.segnalazione.id),
#             db.segnalazione_lavorazione.on(db.join_segnalazione_lavorazione.lavorazione_id==db.segnalazione_lavorazione.id),
#             db.civico.on(
#                 (db.segnalazione.civico_id == db.civico.id) | \
#                 db.civico.geom.st_dwithin(db.segnalazione.geom.st_transform(3003), 250)
#             ),
#             db.profilo_utilizatore.on(db.profilo_utilizatore.id==db.segnalazione_lavorazione.profilo_id),
#         ),
#         limitby = (0,1,)
#     ).first()
#     return render(result)
