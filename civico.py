# -*- coding: utf-8 -*-

from .common import db


def render(row):
    rec = {k: v for k,v in row.items() if not k.startswith('_')}
    return rec


def fetch(desvia=None, numero=None, lettera=None, colore=None, cap=None,
    sounds_like=None, starts_with=None, near_by=None, page=0, paginate=None
):
    """ """

    dbset = db(db.civico)

    if not numero is None:
        dbset = dbset(db.civico.numero == f'{numero:d}')
    if not lettera is None:
        dbset = dbset(db.civico.lettera.lower() == f'{lettera:s}'.lower())
    if not colore is None:
        dbset = dbset(db.civico.colore.lower() == f'{colore:s}'.lower())
    if not cap is None:
        dbset = dbset(db.civico.cap == f'{cap:d}')
    if not desvia is None:
        for substr in desvia.split():
            dbset = dbset(db.civico.desvia.ilike(f'%{substr}%'))
    if not starts_with is None:
        dbset = dbset(db.civico.desvia.ilike(f'{starts_with}%'))

    orderby = db.civico.numero | db.civico.lettera | db.civico.colore
    if not sounds_like is None:
        orderby = f"LEVENSHTEIN(lower('{sounds_like}'), lower(desvia)) ASC, "\
        f"{orderby}"

    if not near_by is None:
        orderby = f"ST_Distance(geom, ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON('{near_by}'), 4326), 3003)) ASC, {orderby}"

    fields = (
        db.civico.codvia.with_alias('codvia'),
        db.civico.desvia.with_alias('desvia'),
        db.civico.numero.with_alias('numero'),
        db.civico.lettera.with_alias('lettera'),
        db.civico.colore.with_alias('colore'),
        db.civico.cap.with_alias('cap'),
        db.civico.desmunicipio.with_alias('municipio'),
    )

    result = map(render, dbset.select(*fields,
        orderby = orderby,
        limitby = None if None in (page, paginate,) else (page, max(paginate, 1),),
    ))

    return result if paginate is None or paginate>1 else next(result)



if __name__ == '__main__':
    rr = fetch()