# -*- coding: utf-8 -*-

import json
from ..common import db, logger
from ..verbatel import Intervento

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
    preview=None, start=None, stop=None, note=None, stato=2
):

    uo_id = get_uo_id(municipio_id)

    incarico_id = db.incarico.insert(
        profilo_id = profilo_id,
        descrizione = descrizione,
        uo_id = uo_id,
        preview = preview,
        start = start,
        stop = stop,
        note = note
    )

    stato_id = db.stato_incarico.insert(
        incarico_id = incarico_id,
        stato_id = stato
    )

    join_id = db.join_segnalazione_incarico.insert(
        incarico_id = incarico_id,
        lavorazione_id = lavorazione_id
    )

    return incarico_id


def update(id, stato=None, **values):
    """ """

    if not municipio_id is None:
        values['uo_id'] = get_uo_id(municipio_id)

    db.incarico.update(**db.incarico._filter_fields(values))

    # TODO: 
        


    


def upgrade(id):
    """ """


def render(row):

    # Pr caso gli identificativi coincidono
    if row.stato_id==1:
        stato = 1 # Da prendere in carico
    elif row.stato_id==2:
        stato = 2 # In lavorazione
    elif row.stato_id==3:
        stato = 3 # Chiusa
    elif row.stato_id==4:
        stato = 4 # Rifiutato

    localizzazione = {}
    indirizzo = f'{row.desvia}, {row.civico_numero}{(row.civico_lettera and row.civico_lettera.upper()) or ""}{row.civico_colore or ""}' #.encode()
    if row.civico_id is None:
        localizzazione['tipoLocalizzazione'] = 3
        localizzazione['daSpecificare'] = indirizzo
    else:
        localizzazione['tipoLocalizzazione'] = 1
        localizzazione['civico'] = indirizzo

    # tipoRichiesta TODO
    #   1: Intervento da gestire dalla PL
    #   2: Intervento gestito dalla PC su cui PL ha visibilità. In questo caso è
    #       necessario notificare le modifiche della segnalazione a Verbatel.
    #   3: Richiesta di ausilio su intervento gestito dalla PC

    if row.profilo_utilizatore.id==6 and row.incarico.profilo_id==6:
        tipoRichiesta = 1
    elif row.incarico.profilo_id==6 and row.note.contains(WARNING):
        tipoRichiesta = 2
    elif row.incarico.profilo_id==6:
        tipoRichiesta = 3
    else:
        logger.error(f'Situazione non prevista: {row}')
        tipoRichiesta = None

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
        dataInLavorazione = row.inizio.isoformat(),
        dataChiusura = row.fine.isoformat(),
        tipoIntervento = row.criticita_id,
        noteOperative = row.note,
        reclamante = row.reclamante,
        telefonoReclamante = row.telefono,
        # tipoRichiesta =
        dataInserimento = row.inizio.isoformat(),
        longitudine = lon,
        latitudine = lat,
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
        db.profilo_utilizatore.id,
        distinct = 'segnalazioni.t_segnalazioni."id"',
        orderby = (
            db.segnalazione.id,
            db.civico.geom.st_distance(db.segnalazione.geom.st_transform(3003)),
            ~db.segnalazione_lavorazione.id,
            ~db.segnalazione_lavorazione.in_lavorazione,
        ),
        left = (
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
    return result.segnalazione_lavorazione.profilo_id!=6, render(result)
    

def after_insert_incarico(id):
    if db.intervento(incarico_id=id) is None:
        # Chiamata servizio Verbatel
        invia, mio_incarico = fetch(id)
        if invia:
            # Invio info a PL
            response = Intervento.create(**mio_incarico)
            # Registro 
            db.intervento.insert(
                intervento_id = response['idIntervento'],
                incarico_id = id
            )

def after_update_incarico(id):
    if db.intervento(incarico_id=id) is None:
        # Chiamata servizio Verbatel
        invia, mio_incarico = fetch(id)
        if invia:
            # Invio info a PL
            incarico_id = mio_incarico.pop('idSegnalazione')
            response = Intervento.update(incarico_id, **mio_incarico)
            # Registro 
            db.intervento.insert(
                intervento_id = response['idIntervento'],
                incarico_id = id
            )


