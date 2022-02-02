# -*- coding: utf-8 -*-

import requests
from . import settings

class Verbatel(object):
    """docstring for Verbatel."""

    def __init__(self):
        super(Verbatel, self).__init__()

    def _url(self, *endpoints):
        """ """
        _port = '' if not 'PORT' in settings.VERBATEL else ":"+ settings.VERBATEL["PORT"]
        url = f'{settings.VERBATEL["PROTOCOL"]}://{settings.VERBATEL["HOST"]}{port}/{settings.VERBATEL["BASE_PATH"]}'
        return '/'.join((url.rstrip('/'),)+endpoints)

    def _create(self, *endpoints, **payload):
        """ """
        response = requests.post(self._url(*endpoints), data=payload)
        return response.json()

class Intervento(Verbatel):
    """docstring for Intervento."""
    endpoint = 'interventi'

    def __init__(self):
        super(Intervento, self).__init__()

    def create(self, eventoId, idSegnalazione, operatore, tipoIntervento,
        nomeStrada, codiceStrada, tipoLocalizzazione, stato, tipoRichiesta,
        nomeStrada2=None, codiceStrada2=None, civico=None, daSpecificare=None,
        datiPattuglia=None, motivoRifiuto=None, latitudine=None, longitudine=None,
        noteOperative=None, reclamante=None, telefonoReclamante=None,
        dataInserimento=None, dataInLavorazione=None, dataChiusura=None,
        dataRifiuto=None, dataRiapertura=None):

        payload = vars()
        payload.pop('self')

        return self._create(self.endpoint, **payload)


def call_new_intervento():
    intervento = Intervento()
    return intervento.create(**{
    	'stato' : 1,
    	'idSegnalazione': 2000,
    	'eventoId': 4,
    	'operatore': 'Operatore PC',
    	'tipoIntervento': 1,
    	'nomeStrada' : 'VIA ALBISOLA',
    	'codiceStrada': 955,
    	'tipoLocalizzazione' : 1,
    	'civico': '2',
    	'noteOperative': 'note note note',
    	'reclamante' : 'commissione',
    	'telefonoReclamante': '3475208085',
    	'tipoRichiesta': 1,
    	'dataInserimento': '2021-06-23T11:00:00',
    	'longitudine': '44.47245435996428',
    	'latitudine': '8.895533415673095'
    })

if __name__ == '__main__':
    response = call_new_intervento()
