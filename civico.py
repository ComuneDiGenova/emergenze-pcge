# -*- coding: utf-8 -*-

from .common import db

# from pydal.validators import *

# class IS_NUM_IN_RANGE(IS_INT_IN_RANGE):
#     """docstring for IS_NUM_IN_RANGE."""
#
#     def __init__(self, *args, digits=4, **kwargs):
#         IS_INT_IN_RANGE.__init__(self, *args, **kwargs)
#         self.digits = digits
#
#     def formatter(self, value):
#         return f"{value:d}:0{self.digits:d}"
#
# class IS_LETTERA(IS_LENGTH):
#     """docstring for IS_LETTERA."""
#
#     def __init__(
#         self,
#         maxsize=1,
#         minsize=0,
#         error_message="Inserire da %(min)g a %(max)g caratteri",
#     ):
#         IS_LENGTH.__init__(self, maxsize=maxsize, minsize=minsize, error_message=error_message)
#         self.maxsize = maxsize
#         self.minsize = minsize
#         self.error_message = error_message
#
#     def formatter(self, value):
#         return value.upper()

# class IS_NULL_OR(IS_EMPTY_OR):
#     """docstring for IS_NULL_OR."""
#
#     def formatter(self, value):
#         value, empty = is_empty(value, empty_regex=self.empty_regex)
#         if empty:
#             return self.null
#         return value


def render(row):
    rec = {k: v for k,v in row.items() if not k.startswith('_')}
    return rec


def fetch(desvia=None, numero=None, lettera=None, colore=None, cap=None,
    sounds_like=None, starts_with=None, near_by=None, page=0, paginate=None,
    epsg=4326
):
    """ """

    dbset = db(db.civico)

    if not numero is None:
        dbset = dbset(db.civico.numero == f'{numero:04}')
    if not lettera is None:
        dbset = dbset(db.civico.lettera.lower() == f'{lettera:s}'.lower())
    if not colore is None:
        dbset = dbset(db.civico.colore.lower() == f'{colore:s}'.lower())
    if not cap is None:
        dbset = dbset(db.civico.cap == f'{cap:05}')
    if not desvia is None:
        cnts = []
        for substr in desvia.split():
            cnts.append(f"CASE WHEN length(desvia) - length(replace(lower(desvia), lower('{substr}'), '')) > 0 THEN 1 ELSE 0 END")

    if not starts_with is None:
        dbset = dbset(db.civico.desvia.ilike(f'{starts_with}%'))

    orderby = db.civico.desvia | db.civico.numero | db.civico.lettera | db.civico.colore
    if not sounds_like is None:
        orderby = f"LEVENSHTEIN(lower('{sounds_like}'), lower(desvia)) ASC, "\
        f"{orderby}"

    if not near_by is None:

        orderby = f"ST_Distance(geom, ST_Transform(ST_SetSRID(ST_GeomFromGeoJSON('{near_by}'), {epsg}), 3003)) ASC, {orderby}"

    if not desvia is None:
        orderby = f"{' + '.join(cnts)} DESC, {orderby}"

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
