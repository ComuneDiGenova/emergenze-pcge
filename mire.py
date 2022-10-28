# -*- coding: utf-8 -*-

from .common import db

DEFAULT_PAGINATION = 10
from pydal.objects import Row
import json

GEOM = f"st_asgeojson({db.letture_mire._rname}.geom)"


def render(row):
    out = {
        k: v for k, v in row.items() if not k.startswith("_") and not isinstance(v, Row)
    }

    out["geom"] = json.loads(row["_extra"][GEOM])
    out["lon"], out["lat"] = out["geom"]["coordinates"]

    return out


def fetch(page=None, paginate=DEFAULT_PAGINATION):

    dbset = db(db.letture_mire.geom != None)

    if page is None:
        limitby = None
    else:
        limitby = tuple(map(lambda ee: ee * int(paginate), (page, page + 1)))

    result = dbset.select(
        db.letture_mire.id.with_alias("id"),
        db.letture_mire.nome.with_alias("nome"),
        db.letture_mire.lettura.with_alias("lettura"),
        db.letture_mire.last_update.with_alias("last_update"),
        db.letture_mire.livello.with_alias("livello"),
        db.letture_mire.lettura_1h.with_alias("lettura_1h"),
        db.letture_mire.last_update_1h.with_alias("last_update_1h"),
        db.letture_mire.livello_1h.with_alias("livello_1h"),
        db.letture_mire.lettura_2h.with_alias("lettura_2h"),
        db.letture_mire.last_update_2h.with_alias("last_update_2h"),
        db.letture_mire.livello_2h.with_alias("livello_2h"),
        db.letture_mire.lettura_3h.with_alias("lettura_3h"),
        db.letture_mire.last_update_3h.with_alias("last_update_3h"),
        db.letture_mire.livello_3h.with_alias("livello_3h"),
        db.letture_mire.lettura_4h.with_alias("lettura_4h"),
        db.letture_mire.last_update_4h.with_alias("last_update_4h"),
        db.letture_mire.livello_4h.with_alias("livello_4h"),
        db.letture_mire.lettura_5h.with_alias("lettura_5h"),
        db.letture_mire.last_update_5h.with_alias("last_update_5h"),
        db.letture_mire.livello_5h.with_alias("livello_5h"),
        db.letture_mire.lettura_6h.with_alias("lettura_6h"),
        db.letture_mire.last_update_6h.with_alias("last_update_6h"),
        db.letture_mire.livello_6h.with_alias("livello_6h"),
        GEOM,
        limitby=limitby,
    )

    return list(map(render, result))
