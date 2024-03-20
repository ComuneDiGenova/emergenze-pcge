# -*- coding: utf-8 -*-

import json
from ..common import settings, db, logger
from ..verbatel import Intervento
import datetime

DEFAULT_TIPO_STATO = 1

WARNING = '( Al momento NON è richiesto alcun intervento da parte di PM )'

uo_value = "concat('com_','PO' || codice_mun::text)" # 'PO'::text || m.codice_mun::text AS cod,

get_uo_id = lambda id: db(db.municipio)(
    (db.profilo_utilizatore.id==6) & \
    f"{db.municipio._rname}.id='{id}'"
).select(
    uo_value,
    limitby = (0,1,)
).first()[uo_value]


def create(segnalazione_id, lavorazione_id, profilo_id, descrizione, municipio_id,
    preview=None, note=None, stato_id=DEFAULT_TIPO_STATO, parziale=False
):

    uo_id = get_uo_id(municipio_id)

    incarico_id = db.incarico.insert(
        profilo_id = profilo_id,
        descrizione = descrizione,
        uo_id = uo_id,
        preview = preview if stato_id==1 else datetime.datetime.now(),
        start = datetime.datetime.now() if stato_id==2 else None,
        note = note
    )

    stato_id = db.stato_incarico.insert(
        incarico_id = incarico_id,
        stato_id = stato_id,
        parziale = parziale
    )

    join_id = db.join_segnalazione_incarico.insert(
        incarico_id = incarico_id,
        lavorazione_id = lavorazione_id
    )

    return incarico_id


def update(id, descrizione, municipio_id,
    profilo_id=6, preview=None, start=None, stop=None, note=None, rifiuto=None,
    **_
):
    """ Funzione dedicata all'aggiornamento dei dati di Incarico

    id          @string : Id incarico
    descrizione @string : Descrizione
    uo_id       @string :
    profilo_id @integer : Id del profilo mittente (Default: 6 - Emergenza Distretto PM (Tutti i distretti di Polizia Municipale))
    stop      @datetime :
    note      @datetime :
    rifiuto     @string :

    """

    uo_id = get_uo_id(municipio_id)

    return db(db.incarico.id==id).update(
        profilo_id = profilo_id,
        descrizione = descrizione,
        uo_id = uo_id,
        preview = preview,
        start = start,
        stop = stop,
        note = note,
        rifiuto = rifiuto
    )


def upgrade(id, stato_id, uo_id, parziale=False, note=None, **kwargs):
    """ Funzione dedicata all'aggiornamento dell'Incarico

    """

    lavorazione_id = db.join_segnalazione_incarico(incarico_id=id).lavorazione_id

    db.stato_incarico.insert(
        incarico_id = id,
        stato_id = stato_id,
        parziale = parziale
    )

    if not note is None:
        db.comunicazione_incarico.insert(
            incarico_id = id,
            testo = note
        )

    parzialmente = '(parzialmente) ' if parziale else ''
    messaggio = f'Incarico "{id}" preso in carico{parzialmente} dalla seguente U.O.: "{uo_id}" - <a class="btn btn-info" href="dettagli_incarico.php?id="{id}"> Visualizza dettagli </a>'

    db.storico_segnalazione_lavorazione.insert(
        lavorazione_id = lavorazione_id,
        aggiornamento = messaggio
    )

    # if (stato_id==3 and not 'stop' in kwargs) and not db.stato_incarico(incarico_id=id, stato_id=stato_id):
    #     kwargs['stop'] = datetime.datetime.now()

    if stato_id==2:
        kwargs['start'] = datetime.datetime.now()
        # if not kwargs.get(preview):
        #     kwargs['preview'] = kwargs['start']
    elif stato_id==3:
        kwargs['stop'] = datetime.datetime.now()

    return update(id, uo_id=uo_id, **kwargs)

check = f"({db.incarico._rname}.id_uo ilike 'com_PO%')"
# check += " or {db.intervento._rname}.incarico_id is not null)::bool"

# check = db.incarico.uo_id.startswith('com_PO') or f'{db.intervento._rname}.incarico_id is not null'

def render(row):

    # Per caso gli identificativi coincidono
    if row.stato_id==1:
        stato = 1 # Da prendere in carico
    elif row.stato_id==2:
        stato = 2 # In lavorazione
    elif row.stato_id==3:
        stato = 3 # Chiusa
    elif row.stato_id==4:
        stato = 4 # Rifiutato

    localizzazione = {}

    civico = f'{row.civico_numero}{(row.civico_lettera and row.civico_lettera.upper()) or ""}{row.civico_colore or ""}'
    indirizzo = f'{row.desvia}, {civico}' #.encode()

    if row.civico_id is None:
        localizzazione['tipoLocalizzazione'] = 3
        localizzazione['daSpecificare'] = civico
    else:
        localizzazione['tipoLocalizzazione'] = 1
        localizzazione['civico'] = civico

    # tipoRichiesta TODO
    #   1: Intervento da gestire dalla PL
    #   2: Intervento gestito dalla PC su cui PL ha visibilità. In questo caso è
    #       necessario notificare le modifiche della segnalazione a Verbatel.
    #   3: Richiesta di ausilio su intervento gestito dalla PC

    if (row.segnalazione_lavorazione.profilo_id==settings.PM_PROFILO_ID) and (row.incarico.profilo_id==settings.PM_PROFILO_ID):
        tipoRichiesta = 1
    elif row.incarico.profilo_id==settings.PM_PROFILO_ID and (WARNING in row.note):
        tipoRichiesta = 1
    elif row.incarico.uo_id.startswith('com_PO'):
    # elif row.incarico.profilo_id==settings.PM_PROFILO_ID:
        tipoRichiesta = 3
    else:
        # logger.error(f'Situazione non prevista: {row}')
        tipoRichiesta = 2

    geom = json.loads(row.geom)
    lon, lat = geom['coordinates']

    # datiPattuglia DA DEFINIRE
    # dataRifiuto
    # dataRiapertura

    return dict(
        tipoRichiesta = tipoRichiesta,
        stato = stato,
        idSegnalazione = row.id,
        eventoId = row.evento_id,
        operatore = row.operatore,
        motivoRifiuto = row.motivo_rifiuto,
        nomeStrada = row.desvia,
        codiceStrada = row.codvia,
        dataInLavorazione = row.inizio and row.inizio.isoformat(),
        dataChiusura =  row.fine and row.fine.isoformat(),
        tipoIntervento = row.criticita_id,
        noteOperative = row.note,
        reclamante = row.reclamante,
        telefonoReclamante = row.telefono,
        dataInserimento =  row.inizio and row.inizio.isoformat(),
        longitudine = lon,
        latitudine = lat,
        check = row[check],
        **localizzazione
    )

def fetch(id):
    """
    id @integer : Id incarico
    """

    result = db(
        (db.stato_incarico.stato_id==db.tipo_stato_incarico.id) & \
        (db.stato_incarico.incarico_id==db.incarico.id) & \
        (db.join_segnalazione_incarico.incarico_id==db.incarico.id) & \
        (db.join_segnalazione_incarico.lavorazione_id==db.segnalazione_lavorazione.id) & \
        (db.segnalante.id == db.segnalazione.segnalante_id) & \
        (db.segnalazione.evento_id == db.evento.id) & \
        (db.incarico.id==id) & \
        # "verbatel.segnalazioni_da_verbatel.intervento_id is null" & \
        # "verbatel.interventi.intervento_id is null" & \
        "eventi.t_eventi.valido is not false"
    ).select(
        db.incarico.id.with_alias('id'),
        db.incarico.start.with_alias('inizio'),
        db.incarico.stop.with_alias('fine'),
        db.incarico.profilo_id,
        db.stato_incarico.stato_id.with_alias('stato_id'),
        db.segnalazione.evento_id.with_alias('evento_id'),
        db.segnalazione.operatore.with_alias('operatore'),
        db.segnalazione.criticita_id.with_alias('criticita_id'),
        db.segnalazione.civico_id.with_alias('civico_id'),
        db.incarico.descrizione.with_alias('note'),
        db.incarico.rifiuto.with_alias('motivo_rifiuto'),
        db.incarico.uo_id,
        db.segnalazione_lavorazione.profilo_id,
        db.segnalazione_lavorazione.in_lavorazione.with_alias('in_lavorazione'),
        db.civico.geom.st_distance(
            db.segnalazione.geom.st_transform(3003)
        ).with_alias('distanza'),
        db.civico.codvia.with_alias('codvia'),
        db.civico.desvia.with_alias('desvia'),
        db.civico.testo.with_alias('civico_numero'),
        db.civico.lettera.with_alias('civico_lettera'),
        db.civico.colore.with_alias('civico_colore'),
        db.segnalazione.geom.st_asgeojson().with_alias('geom'),
        db.segnalante.nome.with_alias('reclamante'),
        db.segnalante.telefono.with_alias('telefono'),
        # db.profilo_utilizatore.id,
        check,
        distinct = 'segnalazioni.t_segnalazioni."id"',
        orderby = (
            db.segnalazione.id,
            db.civico.geom.st_distance(db.segnalazione.geom.st_transform(3003)),
            ~db.segnalazione_lavorazione.id,
            ~db.segnalazione_lavorazione.in_lavorazione,
        ),
        left = (
            # db.intervento.on(db.intervento.id==db.segnalazione_da_vt.intervento_id),
            # db.intervento.on(db.incarico.id==db.intervento.incarico_id),
            db.segnalazione.on(db.join_segnalazione_lavorazione.segnalazione_id==db.segnalazione.id),
            db.segnalazione_lavorazione.on(db.join_segnalazione_lavorazione.lavorazione_id==db.segnalazione_lavorazione.id),
            db.civico.on(
                (db.segnalazione.civico_id == db.civico.id) | \
                db.civico.geom.st_dwithin(db.segnalazione.geom.st_transform(3003), 250)
            ),
            db.profilo_utilizatore.on(db.profilo_utilizatore.id==db.segnalazione_lavorazione.profilo_id),
        ),
        limitby = (0,1,)
    ).first()
    # invia = (result and result.incarico.uo_id.startswith('com_PO'))
    invia = (result and result[check])
    return invia, result and render(result)
    # return result.incarico.profilo_id==settings.PM_PROFILO_ID, render(result)
    # return result.segnalazione_lavorazione.profilo_id!=settings.PC_PROFILO_ID, render(result)

import time

def after_insert_incarico(id):
    logger.debug(f"after insert incarico")

    if db.intervento(incarico_id=id) is None:
        # Chiamata servizio Verbatel
        invia, mio_incarico = fetch(id)
        logger.debug(mio_incarico)
        if invia:
            # Invio info a PL
            response = Intervento.create(**mio_incarico)
            # Registro
            db.intervento.insert(
                intervento_id = response['idIntervento'],
                incarico_id = id
            )

def after_update_incarico(id):
    logger.debug(f"after update incarico")
    intervento = db.intervento(incarico_id=id)

    if intervento is None:
        # Chiamata servizio Verbatel
        invia, mio_incarico = fetch(id)
        if invia:
            # Invio info a PL
            incarico_id = mio_incarico.pop('idSegnalazione')
            response = Intervento.update(intervento.intervento_id, **mio_incarico)

    # TODO: Verificare se la segnalazione corrispondente è in capo a PM e non ha
    # altri incarichi aperti, in tal caso chiudere la Segnalazione

    nfo = db(
        (db.incarico.id==db.join_segnalazione_incarico.incarico_id) \
        & (db.join_segnalazione_incarico.lavorazione_id==db.join_segnalazione_lavorazione.lavorazione_id) \
        # & (db.join_segnalazione_lavorazione.)
        & (db.incarico.id==id)
    ).select(
        db.incarico.id.with_alias('incarico_id'),
        db.join_segnalazione_lavorazione.lavorazione_id.with_alias('lavorazione_id'),
        db.join_segnalazione_lavorazione.segnalazione_id.with_alias('segnalazione_id'),
        limitby = (0,1,)
    ).first()

    # TODO: by-passare la vista incarichi_utili e usare direttamente le tabelle di incarico, segnalazione, lavorazione, etc

    # nfo = db((db.incarichi_utili.id==id)).select(
    #     db.incarichi_utili.segnalazione_id,
    #     db.incarichi_utili.lavorazione_id,
    #     db.incarichi_utili.id,
    #     limitby=(0,1,)
    # ).first()

    segnalazione_id = nfo.segnalazione_id

    num_incarichi_aperti = len(db(
        (db.incarichi_utili.segnalazione_id==segnalazione_id)
        # & (db.incarichi_utili.stato_incarico_id < 3)
    ).select(
        db.incarichi_utili.stato_incarico_id,

        distinct = f'{db.incarichi_utili._rname}.id',
        orderby = db.incarichi_utili.id|~db.incarichi_utili.timeref
    ).find(lambda row: row.stato_incarico_id<3))

    logger.debug(f'Incarichi ancora aperti: {num_incarichi_aperti}')

    if num_incarichi_aperti == 0:

        # 1. Aggiornamento Lavorazione

        lavorazione = db.segnalazione_lavorazione(
            id = nfo.lavorazione_id,
            profilo_id = settings.PM_PROFILO_ID
        )


        if not lavorazione is None:
            logger.debug('Aggiornamento Lavorazione')
            descrizione_chiusura = "Chiusura segnalazione da parte di PL"
            lavorazione.update_record(
                in_lavorazione = False,
                descrizione_chiusura = descrizione_chiusura
            )
            logger.debug(descrizione_chiusura)

            db.storico_segnalazione_lavorazione.insert(
                lavorazione_id = lavorazione.id,
                aggiornamento = f'Chiusura delle segnalazioni. (id_lavorazione= "{lavorazione.id}")'
            )

            segnalazione = db.segnalazione(id=segnalazione_id)

            operazione = f'La segnalazione in lavorazione con "{segnalazione.id}" è stata chiusa'

            db.log.insert(
                schema = 'segnalazioni',
                operatore = segnalazione.operatore,
                operazione = operazione
            )
            logger.debug(operazione)
