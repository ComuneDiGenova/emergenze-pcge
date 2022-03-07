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

class IS_JSON(Validator):
    """
    Example:
        Used as::
            INPUT(_type='text', _name='name',
                requires=IS_JSON(error_message="This is not a valid json input")
            >>> IS_JSON()('{"a": 100}')
            ({u'a': 100}, None)
            >>> IS_JSON()('spam1234')
            ('spam1234', 'invalid json')
    """

    def __init__(self, error_message="Invalid json", native_json=False):
        self.native_json = native_json
        self.error_message = error_message

    def validate(self, value, record_id=None):
        if isinstance(value, (str, bytes)):
            try:
                if self.native_json:
                    json.loads(value)  # raises error in case of malformed json
                    return value  # the serialized value is not passed
                else:
                    return json.loads(value)
            except JSONErrors:
                raise ValidationError(self.translator(self.error_message))
        else:
            return value

    def formatter(self, value):
        if value is None:
            return None
        if self.native_json:
            return value
        else:
            return json.dumps(value)

class IS_JSON_LIST_OF_COMPONENTI(IS_JSON):
    """docstring for IS_JSON_LIST_OF_COMPONENTI."""

    def validate(self, value, record_id=None):
        value = IS_JSON.validate(self, value, record_id)
        try:
            assert isinstance(value, list)
        except AssertionError:
            raise ValidationError(self.translator('A list of object is required'))
        else:
            pass




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

def componenti_validation(recs):
    """ """

    return [err for err in map(componente_form_validation, recs)]
