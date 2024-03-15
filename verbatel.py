# -*- coding: utf-8 -*-

from requests import exceptions
import requests

from . import segnalazione
from . import settings
from .common import logger

# if logger.getEffectiveLevel()==logging.DEBUG:
#     import http.client
#     http.client.HTTPConnection.debuglevel = 1
#     logger.propagate = True

import json
from itertools import chain

from .wso2 import AccessTokenManager

WSO2_VBT_ROOT = settings.WSO2_VBT_ROOT

class VerbatelError(exceptions.HTTPError):
    """ """


class __Tools__(object):

    def uri(self, *path):
        return '/'.join(map(lambda ee: f'{ee}'.strip('/'), chain((self.root,), path)))

    @staticmethod
    def payload(**kwargs):
        """ """
        # Useful preprocessing for preventing requests library to loop over and over
        return json.loads(json.dumps(kwargs))

    @staticmethod
    def nout(response):
        try:
            response.raise_for_status()
        except exceptions.HTTPError:
            logger.warning(response.status_code)
            logger.error(response.text)
            # raise
        else:
            if response.headers.get('Content-Length')=='0':
                return
            else:
                out = response.json()
                try:
                    out=json.loads(out)
                except TypeError:
                    logger.debug("Single decode")
                else:
                    logger.debug("Double decode")
                
                return out

class VerbatelWSO2(AccessTokenManager, __Tools__):
    """ """

    root = WSO2_VBT_ROOT

    _get = AccessTokenManager.get

    def create(self, *path, encode=True, json=False, **payload):
        """ POST """
        
        if encode is True:
            data = self.payload(**payload)
        else:
            data = payload

        uri = self.uri(*path)

        if json is True:
            response = self.post(uri, json=data) # <---
        else:
            response = self.post(uri, data=data) # <---
        return self.nout(response)
    
    def update(self, *path, **payload):
        """ PUT """

        data = self.payload(**payload)
        uri = uri = self.uri(*path)

        response = self.put(uri, data=data) # <---
        return self.nout(response)

    def get(self, *path, **payload):
        """ GET """
        
        data = self.payload(**payload)
        uri = uri = self.uri(*path)

        response = self._get(uri, data=data)
        
        return self.nout(response)


class __Messanger__(object):
    """ """

    def message(self, id, **payload):
        """ POST """
        return self.create(id, 'comunicazione', encode=False, json=True, **payload)


class EventoWSO2(VerbatelWSO2):
    """ """
    root = VerbatelWSO2.uri(VerbatelWSO2, 'eventi')

    def sync(self, info_evento):
        try:
            self.create(**info_evento)
        except exceptions.HTTPError as err:
            logger.debug(err.response.text)
            if "Evento già inviata" in str(err.response.text):
                evento_id = info_evento.pop('id')
                self.update(evento_id, **info_evento)
                return 'SENT UPDATE'
        else:
            return 'SENT NEW'


class InterventoWSO2(VerbatelWSO2, __Messanger__):
    """ """
    root = 'interventi'


class PresidioWSO2(VerbatelWSO2, __Messanger__):
    """ """
    root = VerbatelWSO2.uri(VerbatelWSO2, 'servizi')
    

class MessaggioWSO2(VerbatelWSO2):
    """ """
    root = 'messaggi'



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
        return '/'.join(chain((url.rstrip('/'), cls.root,), map(lambda ee: f'{ee}', endpoints)))

    @staticmethod
    def _payload(**kwargs):
        """ """
        # Useful preprocessing for preventing requests library to loop over and over
        return json.loads(json.dumps(kwargs))

    @classmethod
    def __nout(cls, response):
        try:
            response.raise_for_status()
        except exceptions.HTTPError:
            logger.warning(response.status_code)
            logger.error(response.text)
            # raise
        else:
            if response.headers['Content-Length']=='0':
                return
            else:
                try:
                    out=json.loads(response.json())
                except TypeError:
                    logger.debug("Single decode")
                    return response.json()
                else:
                    logger.debug("Double decode")
                    return out

    @classmethod
    def create(cls, *endpoints, encode=True, json=False, **payload):
        """ POST """
        _url = cls._url(*endpoints)
        if encode is True:
            data = cls._payload(**payload)
        else:
            data = payload
        logger.debug(f'"{_url}"')
        logger.debug(data)
        if json is True:
            response = requests.post(_url, json=data) # <---
        else:
            response = requests.post(_url, data=data) # <---
        return cls.__nout(response)

    @classmethod
    def update(cls, *endpoints, **payload):
        """ PUT """
        _url = cls._url(*endpoints)
        data = cls._payload(**payload)
        logger.debug(f'"{_url}"')
        logger.debug(data)
        response = requests.put(_url, data=data) # <---
        return cls.__nout(response)

    @classmethod
    def get(cls, *endpoints, **payload):
        """ GET """
        _url = cls._url(*endpoints)
        data = cls._payload(**payload)
        logger.debug(f'"{_url}"')
        logger.debug(data)
        response = requests.get(_url, params=data) # <---
        return cls.__nout(response)

class Evento(Verbatel):
    """docstring for Evento."""
    root = 'eventi'

class Intervento(Verbatel):
    """docstring for Intervento."""
    root = 'interventi'

    @classmethod
    def message(cls, id, **payload):
        """ POST """
        return cls.create(id, 'comunicazione', encode=False, json=True, **payload)

class Presidio(Verbatel):
    """docstring for Intervento."""
    root = 'servizi' # <- guess (manca ancora la doc da Verbatel)

    @classmethod
    def message(cls, id, **payload):
        """ POST """
        return cls.create(id, 'comunicazione', encode=False, json=True, **payload)

    @classmethod
    def end(cls, id, **payload):
        return cls.create(id, 'termina', encode=False, json=False, **payload)


class Messaggio(Verbatel):
    """docstring for Messaggio."""
    root = 'messaggi'


def syncEvento(mio_evento):
    """ Segnala nuovo evento verso Verbatel """

    try:
        Evento.create(**mio_evento)
    except exceptions.HTTPError as err:

        #aa = err
        #import pdb; pdb.set_trace()
        logger.debug(err.response.text)
        if "Evento già inviata" in str(err.response.text):
            evento_id = mio_evento.pop('id')
            Evento.update(evento_id, **mio_evento)
            return 'SENT UPDATE'
    else:
        return 'SENT NEW'



# def nuovoEvento(id):
#     """ DEPRECATO Segnala nuovo evento verso Verbatel """
#     mio_evento = evento.fetch(id=id)
#     return Evento.create(**mio_evento)
#
# def aggiornaEvento(id):
#     """ Segnala aggiornamento evento verso Verbatel """
#     mio_evento = evento.fetch(id=id)
#     evento_id = mio_evento.pop('id')
#     return Evento.update(evento_id, **mio_evento)


# def segnalazione2verbatel(id):
#     # DEPRECATED
#     mia_segnalazione = segnalazione.fetch(id=id)
#     return Intervento.create(**mia_segnalazione)



def test():
    """ """
    evento = EventoWSO2()
    return evento.get()
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


def testPresidio():
    """ """
    presidio = PresidioWSO2()
    presidio.message(1005, **{
        # 'idSquadra': row.idSquadra,
        'operatore': 'anonimo',
        'testo': 'ciao squadra',
    })

def testPresidio2():
    Presidio.message(1005, **{
        # 'idSquadra': row.idSquadra,
        'operatore': 'anonimo',
        'testo': 'ciao squadra',
    })
    

if __name__=='__main__':

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

    def test1():
        response = evento2verbatel(110)
        logger.debug(response)

    def test2():
        response = segnalazione2verbatel(398)
        logger.debug(response)
