# -*- coding: utf-8 -*-

import requests
from . import evento
from . import segnalazione
from . import settings
from .common import logger

class Verbatel(object):
    """docstring for Verbatel."""

    @classmethod
    def _url(cls, *endpoints):
        """ """

        try:
            _port = f":{settings.VBT_PORT}"
        except AttributeError:
            _port = ''

        url = f'{settings.VBT_PROT}://{settings.VBT_HOST}{_port}/{settings.VBT_PATH}'
        return '/'.join((url.rstrip('/'), cls.root)+endpoints)

    @classmethod
    def _create(cls, *endpoints, **payload):
        """ """
        response = requests.post(cls._url(*endpoints), data=payload)
        try:
            assert response.status_code<300
        except AssertionError:
            logger.warning(response.status_code)
            logger.error(response.text)
        else:
            if response.headers['Content-Length']=='0':
                return
            else:
                return response.json()

class Intervento(Verbatel):
    """docstring for Intervento."""
    root = 'interventi'

    @classmethod
    def create(cls, eventoId, idSegnalazione, operatore, tipoIntervento,
        nomeStrada, codiceStrada, tipoLocalizzazione, stato, tipoRichiesta,
        nomeStrada2=None, codiceStrada2=None, civico=None, daSpecificare=None,
        datiPattuglia=None, motivoRifiuto=None, latitudine=None, longitudine=None,
        noteOperative=None, reclamante=None, telefonoReclamante=None,
        dataInserimento=None, dataInLavorazione=None, dataChiusura=None,
        dataRifiuto=None, dataRiapertura=None):

        payload = vars()
        payload.pop('cls')

        return cls._create(**payload)


class Evento(Verbatel):
    """docstring for Evento."""
    root = 'eventi'

    @classmethod
    def create(cls, *args, **kwargs):
        return cls._create(*args, **kwargs)


def evento2verbatel(id):
    mio_evento = evento.fetch(id=id)
    return Evento.create(**mio_evento)

def segnalazione2verbatel(id):
    mia_segnalazione = segnalazione.fetch(id=id)
    return Intervento.create(**mia_segnalazione)
    


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

def call_new_evento():
    evento = Evento()
    return evento.create(**{
	"id": 7,
	"descrizione": "Idrologico",
	"inizio": "2021-12-22T16:00:00",
	"chiusura": "2021-12-21T16:00:00",
	"fine": "2021-12-20T16:00:00",
	"fine_sospensione": "2021-12-19T16:00:00",
	"valido": "true",
	"stato":"chiuso",
	"note": [
		{"nota": "Allerta gialla del 22.12.21"}
	],
	"allerte": [
		{
    		"colore": "#ffd800",
    		"descrizione": "Gialla",
    		"fine": "",
    		"inizio": "2021-06-23T12:00:00"
		}
	],
	"foc": [
		{
    		"colore": "#009aff",
    		"descrizione": "Attenzione",
    		"fine": "",
    		"inizio": "2021-06-23T11:00:00"
		},
		{
    		"colore": "#5945ff",
    		"descrizione": "Pre-allarme",
    		"fine": "2021-07-06T01:00:00",
    		"inizio": "2021-07-06T01:00:00"
       }
    ],
	"municipi": [
		"Bassa Val Bisagno",
		"Centro est",
		"Centro Ovest",
		"Levante"
	]})

def test():
    # response = call_new_evento()
    # response = call_new_intervento()
    # response = evento2verbatel(110)
    response = segnalazione2verbatel(417)
    logger.debug(response)
