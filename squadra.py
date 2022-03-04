# -*- coding: utf-8 -*-

from .common import db

agente_form = (
    db.agente.matricola,
    db.agente.nome,
    db.agente.cognome,
    db.agente.livello2,
    #
    db.telefono.telefono,
    db.email.address,
)

class ParameterError(KeyError):
    """ """

def componente_form_validation(rec):
    """ """

    validators = {field.name: field for field in agente_form}

    errors = {}
    for key, value in rec.items():
        try:
            val, msg = validators[key](value)
        except KeyError:
            # In questo caso ci sarebbe un parametro non previsto
            raise ParameterError(f'Parametro {key} non previsto')
        else:
            if not msg is None:
                errors[key] = msg

    return errors
