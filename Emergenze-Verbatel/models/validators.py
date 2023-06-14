# -*- coding: utf-8 -*-

# from pydal.validators import *
from pydal.validators import Validator, ValidationError

from codicefiscale import codicefiscale
import phonenumbers

class isValidPhoneNumber(Validator):
    """docstring for isValidCf"""

    def validate(self, value, record_id=None):
        """ """
        try:
            pn = phonenumbers.parse(value, None)
        except phonenumbers.phonenumberutil.NumberParseException:
            try:
                pn = phonenumbers.parse(value, 'IT')
            except phonenumbers.phonenumberutil.NumberParseException:
                raise ValidationError("Formato numero telefonico non valido")

        try:
            assert phonenumbers.is_valid_number(pn)
        except AssertionError:
            raise ValidationError("Formato numero telefonico non valido")
        else:
            return phonenumbers.format_number(pn, phonenumbers.PhoneNumberFormat.E164)
