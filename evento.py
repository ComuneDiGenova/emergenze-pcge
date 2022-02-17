# -*- coding: utf-8 -*-

from .common import db

municipio_nomi = f'array_agg(distinct {db.municipio._rname}.{db.municipio.nome._rname} '\
    f'order by {db.municipio._rname}.{db.municipio.nome._rname})'
# codici_municipio = _template.format(db.municipio.codice._rname)

focs = "json_agg(distinct jsonb_build_object("\
    f"'descrizione', {db.tipo_foc._rname}.{db.tipo_foc.descrizione._rname}, "\
    f"'colore', regexp_replace({db.tipo_foc._rname}.{db.tipo_foc.colore._rname}, E'[\\n\\r]+', '', 'g'), "\
    f"'inizio', {db.join_tipo_foc._rname}.{db.join_tipo_foc.inizio._rname}, "\
    f"'fine', {db.join_tipo_foc._rname}.{db.join_tipo_foc.fine._rname}"\
f")) filter (where {db.join_tipo_foc._rname}.{db.join_tipo_foc.inizio._rname} is not null)"

allerte = "json_agg(distinct jsonb_build_object("\
    f"'descrizione', {db.tipo_allerta._rname}.{db.tipo_allerta.descrizione._rname}, "\
    f"'colore', regexp_replace({db.tipo_allerta._rname}.{db.tipo_allerta.colore._rname}, E'[\\n\\r]+', '', 'g'), "\
    f"'inizio', {db.join_tipo_allerta._rname}.{db.join_tipo_allerta.inizio._rname}, "\
    f"'fine', {db.join_tipo_allerta._rname}.{db.join_tipo_allerta.fine._rname}"\
f"))"\
f" filter (where {db.join_tipo_allerta._rname}.{db.join_tipo_allerta.inizio._rname} is not null)"

note = "json_agg(distinct jsonb_build_object("\
    f"'nota', {db.nota_evento._rname}.{db.nota_evento.nota._rname}"\
"))"

def render(row):
    """ """
    rec = {k: v for k,v in row.items() if not k.startswith('_')}
    rec['municipi'] = row[municipio_nomi]
    rec['foc'] = row[focs]
    rec['allerte'] = row[allerte]
    rec['note'] = row[note]

    rec['inizio'] = row.inizio.isoformat(timespec='seconds')
    rec['fine'] = row.fine and row.fine.isoformat()

    if not row.fine is None:
        stato = 'chiuso'
    elif row.chiusura is None:
        stato = 'aperto'
    else:
        stato = 'chiusura'
    rec['stato'] = stato

    rec['valido'] = not (row.valido==False)

    return rec

def fetch(id=None, page=0, paginate=None, _foc_only=True, _all=True):
    """ """

    # Join

    dbset = db(
        (db.evento.id==db.join_tipo_evento.evento_id) & \
        (db.tipo_evento.id==db.join_tipo_evento.tipo_evento_id) & \
        (db.evento.id==db.join_municipio.evento_id) & \
        (db.municipio.id==db.join_municipio.municipio_id) &\
        (db.tipo_allerta.id==db.join_tipo_allerta.tipo_allerta_id)
    )

    left = (
        db.join_tipo_allerta.on(db.evento.id==db.join_tipo_allerta.evento_id),
        db.nota_evento.on(db.evento.id==db.nota_evento.evento_id)
    )

    if _foc_only:
        dbset = dbset(
            (db.evento.id==db.join_tipo_foc.evento_id) & \
            (db.tipo_foc.id==db.join_tipo_foc.tipo_foc_id)
        )
    else:
        left += (db.join_tipo_foc.on(db.evento.id==db.join_tipo_foc.evento_id),)

    # Filter

    dbset = dbset(
        # SOLO EVENTI VALIDI
        (f"{db.evento._rname}.valido is null" | (db.evento.valido == True)) & \
        (db.tipo_foc.valido==True) & \
        (db.tipo_allerta.valido==True)
    )

    if not id is None:
        dbset = dbset(db.evento.id==id)

    if not _all:
        dbset = dbset(db.tipo_allerta.id.belongs([1,3]))

    fields = (
        db.evento.id.with_alias('id'),
        db.evento.inizio.with_alias('inizio'),
        db.evento.fine.with_alias('fine'),
        db.evento.fine_sospensione.with_alias('fine_sospensione'),
        db.evento.chiusura.with_alias('chiusura'),
        db.evento.valido.with_alias('valido'),
        db.tipo_evento.descrizione.with_alias('descrizione'),
        municipio_nomi,
        focs,
        allerte,
        note,
    )

    result = map(render, dbset.select(
        *fields,
        orderby = ~db.evento.inizio|~db.evento.id,
        groupby = db.evento.id|db.evento.inizio|db.evento.fine_sospensione|db.evento.chiusura|db.tipo_evento.descrizione,
        limitby = None if None in (page, paginate,) else (page, max(paginate, 1),),
        left = left
    ))
    #import pdb; pdb.set_trace()
    return next(result) if not id is None or (not paginate is None and paginate<1) else result 
    # return result if not id is None or paginate is None or paginate>1 else next(result)
