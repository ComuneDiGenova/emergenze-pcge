# -*- coding: utf-8 -*-

agente_form = (
    db.agente.matricola,
    db.agente.nome,
    db.agente.cognome,
    db.agente.livello2,
    #
    db.telefono.telefono,
    db.email.address,
)

def componente_form_validation(rec):
    """ """

    validators = {field.name: field for field in agente_form}

    for key, value in rec.items():
        try:
            val, msg = validators[key](value)
        except KeyError:
            pass
        else:
            pass
