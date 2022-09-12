# -*- coding: utf-8 -*-

from pydal.validators import IS_JSON, ValidationError

from ..common import db

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


def create(nome, evento_id, afferenza, percorso, componenti,
    stato_id=1, profilo_id=6, note=''
):
    """
    nome            @string : Nome della squadra;
    evento_id      @integer : Id evento di riferimento;
    stato_id       @integer : Id stato squadra (default 1: In azione)
    afferenza       @string :
    componenti @list(@dict) :
    """

    # 1. Creazione sqaudra

    squadra_id = db.squadra.insert(
        nome = nome,
        evento_id = evento_id,
        stato_id = stato_id,
        afferenza = afferenza
    )

    # 2. Creazione del presidio

    percorso_presidio = db.presidi_mobili(percorso=percorso)

    presidio_id = db.presidio.insert(
        profilo_id = profilo_id,
        descrizione = percorso,
        evento_id = evento_id,
        note = note,
        geom = percorso_presidio.geom
    )

    # 3. Assegnazione dello stato al presidio
    
    stato_presidio_id = db.stato_presidio.insert(
        presidio_id = presidio_id,
        stato_presidio_id = 2 # 2: Preso in carico
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

    for componente in componenti:
        agente = db.agente(matricola=componente['matricola'])
        if agente is None:
            agente_id = db.agente.insert(livello2=livello2, **db.agente._filter_fields(componente))
            agente = db.agente[agente_id]
        
        db.telefono.update_or_insert(
            {'codice': squadra_id, 'matricola': componente['matricola']},
            codice = squadra_id,
            matricola = componente['matricola'],
            telefono = componente['telefono']
        )

        db.email.update_or_insert(
            {'codice': squadra_id, 'matricola': componente['matricola']},
            codice = squadra_id,
            matricola = componente['matricola'],
            email = componente['email']
        )

    return squadra_id