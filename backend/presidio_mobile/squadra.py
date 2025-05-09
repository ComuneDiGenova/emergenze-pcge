# -*- coding: utf-8 -*-

from pydal.validators import IS_JSON, ValidationError, IS_IN_DB, IS_NOT_IN_DB, IS_EMPTY_OR
from ..verbatel import PresidioWSO2 as Presidio
from ..common import db
from ..common import logger

presidio = Presidio()

db.telefono.telefono.requires = IS_EMPTY_OR(db.telefono.telefono.requires)

agente_form = (
    db.agente.matricola,
    db.agente.nome,
    db.agente.cognome,
    db.agente.livello2,
    #
    db.telefono.telefono,
    db.email.email,
)


class IS_JSON_LIST_OF_COMPONENTI(IS_JSON):
    """docstring for IS_JSON_LIST_OF_COMPONENTI."""

    # def __init__(self, *args, native_json=True, **kwargs):
    #     super(IS_JSON_LIST_OF_COMPONENTI, self).__init__(*args, native_json=True, **kwargs)

    def validate(self, value, record_id=None):
        componenti = IS_JSON.validate(self, value, record_id)
        try:
            assert isinstance(componenti, list)
        except AssertionError:
            raise ValidationError(self.translator('Richiesta una lista di oggetti'))
        else:
            validators = {field.name: field for field in agente_form}
            for componente in componenti:
                for fieldName, field in validators.items():
                    param = componente.get(fieldName)

                    try:
                        assert not (field.required and param is None)
                    except AssertionError:
                        raise ValidationError(f'Parametro "{fieldName}" per ogni componente non può essere nullo')

                    try:
                        field.validate(param, record_id=None)
                    except KeyError:
                        # In questo caso ci sarebbe un parametro non previsto
                        raise ValidationError(f'Parametro {param} non previsto')
        return componenti

    # def formatter(self, value):
    #     if value is None:
    #         return None
    #     else:
    #         return json.loads(value)


DEFAULT_STATO_SQUADRA_ID = 2 # A disposizione
# 1 # In azione

def create(nome, evento_id, afferenza, componenti, pattuglia_id=None,
    percorso='A1', stato_id=None, profilo_id=6, note='',
    preview=None, start=None, stop=None, descrizione="Percorso assegnato d'ufficio"
):
    """
    nome            @string : Nome della squadra;
    evento_id      @integer : Id evento di riferimento;
    stato_id       @integer : Id stato squadra (default 1: In azione)
    afferenza       @string :
    componenti @list(@dict) :
    pattuglia_id   @integer : Identificativo pattuglia Verbatel
    preview       @datetime :
    start         @datetime :
    stop          @datetime :
    """


    DEFAULT_STATO_PRESIDIO_ID = 1
    # 2 # Preso in carico
    # 1 # Inviato ma non ancora preso in carico

    if stato_id==1:
        stato_presidio_id = 2
    elif stato_id ==2:
        stato_presidio_id = 1
    elif stato_id ==3:
        stato_presidio_id = 3
    elif stato_id is None and not stop is None:
        stato_presidio_id = 3
        stato_id = 3
    elif stato_id is None and not start is None:
        stato_presidio_id = 2
        stato_id = 1
    elif stato_id is None and not preview is None:
        stato_presidio_id = 1
        stato_id = 2
    else:
        stato_id = DEFAULT_STATO_SQUADRA_ID
        stato_presidio_id = DEFAULT_STATO_PRESIDIO_ID

    # 1. Creazione sqaudra

    squadra_id = db.squadra.insert(
        nome = nome,
        evento_id = evento_id,
        stato_id = stato_id,
        afferenza = afferenza
    )

    # 2. Creazione del presidio

    # percorso_presidio = db.presidi_mobili(percorso=percorso)
    geom = "st_transform (geom, 4326)"
    percorso_presidio = db(db.presidi_mobili.percorso==percorso).select(geom).first()

    presidio_id = db.presidio.insert(
        profilo_id = profilo_id,
        descrizione = descrizione,
        evento_id = evento_id,
        note = note,
        geom = percorso_presidio[geom],
        preview = preview,
        start = start,
        stop = stop
    )

    # 3. Assegnazione dello stato al presidio

    stato_presidio_id = db.stato_presidio.insert(
        presidio_id = presidio_id,
        stato_presidio_id = stato_presidio_id
    )

    # 3.1 Registrazione squadra PM

    db.pattuglia_pm.insert(
        squadra_id = squadra_id,
        pattuglia_id = pattuglia_id,
        presidio_id = presidio_id
    )

    # 4. Assegno la squadra al presidio mobile

    db.join_presidio_squadra.insert(
        presidio_id = presidio_id,
        squadra_id = squadra_id,
    )

    # 5. Log dell'evento

    db.log.insert(
        schema = 'segnalazioni',
        operatore = None,
        operazione = f'Inviato presidio/sopralluogo "{presidio_id}"'
    )

    # 6. Formo la squadra con i componenti segnalati

    if not afferenza is None:
        livello2 = db.livello2(id2=int(afferenza[6:])).descrizione
    else:
        livello2 = None

    emails = []
    for componente in componenti:
        agente = db.agente(matricola=componente['matricola'])
        if agente is None:
            agente_id = db.agente.insert(livello2=livello2, **db.agente._filter_fields(componente))
            agente = db.agente[agente_id]

        if componente['telefono']:

            db.telefono.update_or_insert(
                {'codice': squadra_id, 'matricola': componente['matricola']},
                codice = squadra_id,
                matricola = componente['matricola'],
                telefono = componente['telefono']
            )

        # Rispetto al telefono la mail non può essere ripetuta all'interno della
        # stessa squadra a causa del vincolo differente di chiave primaria
        if componente['email'] and not componente['email'] in emails:
            emails.append(componente['email'])

            db.email.update_or_insert(
                {'codice': squadra_id, 'matricola': componente['matricola']},
                codice = squadra_id,
                matricola = componente['matricola'],
                email = componente['email']
            )

    return squadra_id

def update_by_pattuglia_pm_id(pattuglia_id, preview=None, start=None, stop=None):
    """ """
    pattuglia_pm = db.pattuglia_pm(pattuglia_id=pattuglia_id)
    db(db.presidio.id==pattuglia_pm.presidio_id).update(
        preview = preview,
        start = start,
        stop = stop
    )
    if not stop is None:
        db.stato_presidio.insert(
            presidio_id = pattuglia_pm.presidio_id,
            stato_presidio_id = 3 # Chiuso
        )

        db.log.insert(
            schema = 'segnalazioni',
            operatore = None,
            operazione = f'presidio mobile (o sopralluogo) "{pattuglia_pm.presidio_id}" chiuso'
        )

        db(db.squadra.id==pattuglia_pm.squadra_id).update(
            stato_id = 3 # A riposo
        )
    elif not start is None:
        db.stato_presidio.insert(
            presidio_id = pattuglia_pm.presidio_id,
            stato_presidio_id = 2 # Preso in carico
        )

        db.log.insert(
            schema = 'segnalazioni',
            operatore = None,
            operazione = f'presidio mobile (o sopralluogo) "{pattuglia_pm.presidio_id}" preso in carico'
        )

        db(db.squadra.id==pattuglia_pm.squadra_id).update(
            stato_id = 1 # In azione
        )
    elif not preview is None:
        db.stato_presidio.insert(
            presidio_id = pattuglia_pm.presidio_id,
            stato_presidio_id = 1 # Inviato ma non ancora preso in carico
        )

        db.log.insert(
            schema = 'segnalazioni',
            operatore = None,
            operazione = f'presidio mobile (o sopralluogo) "{pattuglia_pm.presidio_id}" inviato ma non ancora preso in carico'
        )

        db(db.squadra.id==pattuglia_pm.squadra_id).update(
            stato_id = 2 # A disposizione
        )

    return 'Ok'


# def delete_by_pattuglia_pm_id(squadra_id):
#     """ """
#     pattuglia_pm = db.pattuglia_pm(pattuglia_id=pattuglia_id)
#     db(db.presidio.id==pattuglia_pm.presidio_id).delete()
#     db(db.squadra.id==pattuglia_pm.squadra_id)
#     return 'Ok'


def valida_pattuglia(form):
    """ """
    fieldname = 'pattuglia_id'

    _, msg = IS_IN_DB(db(db.pattuglia_pm), db.pattuglia_pm[fieldname])(form.vars[fieldname])

    if msg:
        form.errors[fieldname] = msg

def valida_nuova_pattuglia(form):
    """ """
    fieldname = 'pattuglia_id'

    _, msg = IS_NOT_IN_DB(db(db.pattuglia_pm), db.pattuglia_pm[fieldname])(form.vars[fieldname])

    if msg:
        form.errors[fieldname] = msg

PRESIDIO_CHIUSO = 3

def after_insert_stato_presidio(presidio_id, stato_presidio_id, timeref):
    """ payload['presidio_id'], payload['stato_presidio_id'], payload['timeref']
    """
    logger.debug(f"after insert stato_presidio")

    if stato_presidio_id==PRESIDIO_CHIUSO:

        pattuglia = db(
            (db.stato_presidio.presidio_id==db.pattuglia_pm.presidio_id) & \
            (db.stato_presidio.presidio_id==presidio_id) &
            (db.stato_presidio.stato_presidio_id==stato_presidio_id) &
            (db.stato_presidio.timeref==timeref)
        ).select(
            db.pattuglia_pm.pattuglia_id.with_alias("idSquadra"),
            limitby = (0,1,)
        ).first()

        if not pattuglia is None:
            presidio.end(pattuglia.idSquadra, operatore='Operatore di PC')
