# -*- coding: utf-8 -*-

from pydal.validators import IS_JSON, ValidationError

from .common import db

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


def create(nome, evento_id, stato_id, afferenza, componenti):
    """
    nome            @string : Nome della squadra;
    evento_id      @integer : Id evento di riferimento;
    stato_id       @integer :
    afferenza       @string :
    componenti @list(@dict) :
    """

    # 1. Creazione sqaudra

    squadra_id = db.sqaudra.insert(
        nome = nome,
        evento_id = evento_id,
        stato_id = stato_id,
        afferenza = afferenza
    )

    # 2. Creazione del presidio




    for componente in componenti:
        agente = db.agente(matricola=componente[matricola])
        if agente is None:

            db.agente.insert(db.agente._filter_fields(**componente))
