# -*- coding: utf-8 -*-

import json
from ..common import settings, db, logger
from pydal.objects import Row
from pydal.validators import IS_IN_SET
from datetime import datetime

DEFAULT_PAGINATION = 10

GEOM = f'st_asgeojson({db.segnalazioni_lista._rname}.geom)'

INCARICHI_APERTI = "count(DISTINCT v_incarichi_last_update.id) filter (where id_stato_incarico<3)"

def render(row):
    out = {k: v for k,v in row.items() if not k.startswith('_') and not isinstance(v, Row)}
    out['incarichi_aperti'] = row[INCARICHI_APERTI]
    out['geom'] = json.loads(row['_extra'][GEOM])
    out['lon'] = out['geom']['coordinates'][0]
    out['lat'] = out['geom']['coordinates'][1]
    if row.segnalazioni_lista.lavorazione_id is None:
        out['status'] = 'Da prendere in carico'
    elif row.segnalazioni_lista.in_lavorazione is False:
        out['status'] = 'Chiusa'
    else:
        out['status'] = 'In lavorazione'

    return out

state_validation = IS_IN_SET([
    ('0', 'Da prendere in carico',),
    ('1', 'In lavorazione',),
    ('2', 'Chiusa',),],
    error_message = "Valore non valido",
    zero = None
)

status_is_0 = (db.segnalazioni_lista.lavorazione_id == None)
status_is_2 = (db.segnalazioni_lista.in_lavorazione == False)
status_is_1 = (db.segnalazioni_lista.lavorazione_id != None) & \
    (db.segnalazioni_lista.in_lavorazione)

gt_ts = lambda ts: f"TO_TIMESTAMP(v_segnalazioni_lista.data_ora, 'YYY/MM/DD HH24:MI')>=TO_TIMESTAMP('{ts.strftime('%Y/%m/%d %H:%M')}', 'YYY/MM/DD HH24:MI')"
lt_ts = lambda ts: f"TO_TIMESTAMP(v_segnalazioni_lista.data_ora, 'YYY/MM/DD HH24:MI')<=TO_TIMESTAMP('{ts.strftime('%Y/%m/%d %H:%M')}', 'YYY/MM/DD HH24:MI')"

def fetch(status=None, start=None, end=None, page=None, paginate=DEFAULT_PAGINATION):

    dbset = db(
        (db.segnalazioni_lista.criticita_id==db.tipo_criticita.id)
    )

    if not end is None:
            dbset = dbset(lt_ts(end))

    if not status is None:

        if not start is None:
            dbset = dbset(gt_ts(start))

        if status=='0':
            dbset = dbset(status_is_0)
        elif status == '2':
            dbset = dbset(status_is_2)
        else:
            dbset = dbset(status_is_1)

    else:

        if not start is None:
            dbset = dbset(gt_ts(start)|status_is_0|status_is_1)

    logger.debug(dbset)
    if page is None:
        limitby = None
    else:
        limitby = (page*paginate, (page+1)*paginate)

    result = dbset.select(
        db.segnalazioni_lista.id.with_alias('id'),
        db.segnalazioni_lista.data_ora.with_alias('created_on'),
        db.segnalazioni_lista.descrizione.with_alias('description'),
        db.segnalazioni_lista.note.with_alias('note'),
        db.tipo_criticita.descrizione.with_alias('issue'),
        db.segnalazioni_lista.localizzazione.with_alias('address'),
        db.segnalazioni_lista.municipio.with_alias('municipio'),
        db.segnalazioni_lista.lavorazione_id,
        db.segnalazioni_lista.in_lavorazione,
        db.segnalazioni_lista.fine_sospensione.with_alias('sospensione_evento'),
        INCARICHI_APERTI,
        GEOM,
        left = db.incarichi_lista.on((db.segnalazioni_lista.id==db.incarichi_lista.segnalazione_id)),
        groupby = [
            db.segnalazioni_lista.id,
            db.segnalazioni_lista.data_ora,
            db.segnalazioni_lista.descrizione,
            db.segnalazioni_lista.note,
            db.tipo_criticita.descrizione,
            db.segnalazioni_lista.localizzazione,
            db.segnalazioni_lista.municipio,
            db.segnalazioni_lista.lavorazione_id,
            db.segnalazioni_lista.in_lavorazione,
            db.segnalazioni_lista.fine_sospensione,
            GEOM,
        ],
        limitby = limitby,
        orderby = ~db.segnalazioni_lista.data_ora
    )

    return list(map(render, result))
