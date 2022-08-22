# -*- coding: utf-8 -*-

from .common import db
DEFAULT_PAGINATION = 10
from pydal.objects import Row
import json
GEOM = f'st_asgeojson({db.letture_mire._rname}.geom)'

def render(row):
    out = {k: v for k,v in row.items() if not k.startswith('_') and not isinstance(v, Row)}

    out['geom'] = json.loads(row['_extra'][GEOM])
    # out['lon'] = out['geom']['coordinates'][0]
    # out['lat'] = out['geom']['coordinates'][1]
    # if row.segnalazioni_lista.lavorazione_id is None:
    #     out['status'] = 'Da prendere in carico'
    # elif row.segnalazioni_lista.in_lavorazione is False:
    #     out['status'] = 'Chiusa'
    # else:
    #     out['status'] = 'In lavorazione'

    return out

def fetch(page=None, paginate=DEFAULT_PAGINATION):

    dbset = db(db.letture_mire)

    if page is None:
        limitby = None
    else:
        limitby = (page*paginate, (page+1)*paginate)

    result = dbset.select(
        db.letture_mire.id,
        db.letture_mire.nome,
        GEOM,
        limitby = limitby,
        orderby = ~db.letture_mire.last_update
    )

    # import pdb; pdb.set_trace()

    return list(map(render, result))
