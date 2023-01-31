# -*- coding: utf-8 -*-

import requests

from . import segnalazione
from . import settings
from .common import logger, logging

# if logger.getEffectiveLevel()==logging.DEBUG:
#     import http.client
#     http.client.HTTPConnection.debuglevel = 1
#     logger.propagate = True

import json
from itertools import chain
from datetime import datetime
from datetime import timedelta
from urllib.parse import urljoin

def ujoin(*parts):
    reduce(urljoin, parts)

def _rfs(func):
    """" Raise For Status """
    def wrapped(*args, **kwargs):
        response = func(*args, **kwargs)
        try:
            response.raise_for_status()
        except requests.exceptions.HTTPError:
            logger.warning(response.status_code)
            logger.error(response.text)
            raise
        else:
            return response
    return wrapped

class Wso2():

    url = settings.WSO2_URL
    root = settings.WSO2_VBT_ROOT

    def __init__(self) -> None:
        self.key = settings.WSO2_KEY
        self.secret = settings.WSO2_SECRET
        self._token = None
        self.expire = None

    @property
    def access_token(self) -> str:

        if self._token is None or self.expire <= datetime.utcnow():
            response = requests.get(
                urljoin(self.url, settings.WSO2_TOKEN_ENDPOINT),
                params = {'key': self.key, 'secret': self.secret})
            info = response.json()
            self.expire = datetime.utcnow() + timedelta(seconds=info['expires_in'])
            self._token = info['access_token']
        else:
            logger.debug(f"Token will expire in {int((self.expire-datetime.utcnow()).total_seconds())} seconds.")

        return self._token

    @property
    def headers(self) -> dict:
        return {
            'Authorization': f'Bearer {self.access_token}',
        }

    @_rfs
    def get(self, endpoint: str, params: dict = None) -> requests.Response:
        """ """
        return requests.get(
            urljoin(self.url, '/'.join((self.root, endpoint,))),
            params=params, headers=self.headers)

    @_rfs
    def put(self, endpoint: str, data: dict = None) -> requests.Response:
        return requests.put(
            urljoin(self.url, '/'.join((self.root, endpoint,))),
            data=data, headers=self.headers)

    @_rfs
    def post(self, endpoint, data: dict = None, json: dict = None) -> requests.Response:
        return requests.post(
            urljoin(self.url, '/'.join((self.root, endpoint,))),
            data = data,
            json = json,
            headers = self.headers
        )


proxy = Wso2()
        

class VerbatelError(requests.exceptions.HTTPError):
    """ """


class Verbatel(object):
    """docstring for Verbatel."""

    root = 'api'

    @classmethod
    def _url(cls, *endpoints):
        """ """

        try:
            _port = f":{settings.VBT_PORT}"
        except AttributeError:
            _port = ''

        root = f'{settings.VBT_PROT}://{settings.VBT_HOST}{_port}/{settings.VBT_ROOT}'

        return urljoin(root, cls._path(*endpoints))

        # url = f'{settings.VBT_PROT}://{settings.VBT_HOST}{_port}/{settings.VBT_ROOT}'
        # return '/'.join(chain((url.rstrip('/'), cls.root,), map(lambda ee: f'{ee}', endpoints)))

    @classmethod
    def _path(cls, *endpoints):
        """ """
        return '/'.join(map(lambda pp: str(pp).strip('/'), chain((cls.root,), endpoints)))

    @staticmethod
    def _payload(**kwargs):
        """ """
        # Useful preprocessing for preventing requests library to loop over and over
        return json.loads(json.dumps(kwargs))

    # @classmethod
    # def __nout(cls, response):
    #     try:
    #         response.raise_for_status()
    #     except requests.exceptions.HTTPError:
    #         logger.warning(response.status_code)
    #         logger.error(response.text)
    #         raise
    #     else:
    #         if response.headers['Content-Length']=='0':
    #             return
    #         else:
    #             try:
    #                 out=json.loads(response.json())
    #             except TypeError:
    #                 logger.debug("Single decode")
    #                 return response.json()
    #             else:
    #                 logger.debug("Double decode")
    #                 return out

    # @classmethod
    # def call(cls, func, *endpoints, )

    @classmethod
    def create(cls, *endpoints, encode=True, json=False, **payload):
        """ POST """
        # _url = cls._url(*endpoints)
        if encode is True:
            data = cls._payload(**payload)
        else:
            data = payload
        # logger.debug(f'"{_url}"')
        logger.debug(data)
        rendpoint = cls._path(*endpoints)
        if json is True:
            response = proxy.post(rendpoint, json=data) # <---
        else:
            response = proxy.post(rendpoint, data=data) # <---
        return response
        # try:
        #     return cls.__nout(response)
        # except requests.exceptions.HTTPError as err:
        #     import pdb; pdb.set_trace()
        #     raise
        #     pass

    @classmethod
    def update(cls, *endpoints, **payload):
        """ PUT """
        # _url = cls._url(*endpoints)
        data = cls._payload(**payload)
        # logger.debug(f'"{_url}"')
        logger.debug(data)
        rendpoint = cls._path(*endpoints)
        response = proxy.put(rendpoint, data=data) # <---
        return response
        # return cls.__nout(response)

    @classmethod
    def get(cls, *endpoints, **payload):
        """ GET """
        _url = cls._url(*endpoints)
        data = cls._payload(**payload)
        logger.debug(f'"{_url}"')
        logger.debug(data)
        response = proxy.get(_url, params=data) # <---
        return response
        # return cls.__nout(response)


class Evento(Verbatel):
    """docstring for Evento."""

    root = 'api/Eventi'


class Intervento(Verbatel):
    """docstring for Intervento."""
    root = 'api/Interventi'

    @classmethod
    def message(cls, id, **payload):
        """ POST """
        return cls.create(
            f'../../{cls.root.lower()}',
            id, 'comunicazione', encode=False, json=True, **payload)


class Presidio(Verbatel):
    """docstring for Intervento."""
    # root = 'api/servizi' # <- guess (manca ancora la doc da Verbatel)

    @classmethod
    def message(cls, id, **payload):
        """ POST """
        return cls.create(cls.root, 'servizi', id, 'comunicazione', encode=False, json=True, **payload)

    @classmethod
    def end(cls, id, **payload):
        return cls.create(cls.root, 'servizi', id, 'termina', encode=False, json=False, **payload)


class Messaggio(Verbatel):
    """docstring for Messaggio."""
    root = 'api/messaggi'


def syncEvento(mio_evento):
    """ Segnala nuovo evento verso Verbatel """

    try:
        Evento.create(**mio_evento)
    except requests.exceptions.HTTPError as err:

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

def test_evento_create():
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze import evento
    ee = evento.fetch(paginate=1)
    evt = next(ee)
    Evento.create(**evt)
    breakpoint()

def test_evento_update():
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze import evento
    ee = evento.fetch(paginate=1)
    evt = next(ee)
    evt_id = evt.pop('id')
    response = Evento.update(evt_id, **evt)
    breakpoint()

def test_intervento_create():
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze.incarico import incarico
    ii = incarico.fetch(542)
    iid, icmc = ii
    # evt = next(ee)
    response = Intervento.create(**icmc)
    breakpoint()

def test_intervento_update():
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze.incarico import incarico
    from apps.emergenze.common import db
    incarico_id = 542
    intervento = db.intervento(incarico_id=incarico_id)
    ii = incarico.fetch(incarico_id)
    iid, icmc = ii
    # evt = next(ee)
    response = Intervento.update(intervento.intervento_id, **icmc)
    breakpoint()

def test_intervento_messaggio():
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze.incarico import comunicazione
    ii = comunicazione.fetch(None)
    iid, icmc = ii
    # evt = next(ee)
    Intervento.message(iid, **icmc)
    breakpoint()

def test_presidio_messaggio():
    """ TODO """
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze.presidio_mobile import comunicazione
    ii = comunicazione.fetch(None)
    iid, icmc = ii
    # evt = next(ee)
    breakpoint()
    Presidio.message(iid, **icmc)
    breakpoint()

def test_presidio_fine():
    """ TODO """
    # from apps.emergenze.verbatel import Evento
    from apps.emergenze.common import db
    pattuglia = db(
        (db.stato_presidio.presidio_id==db.pattuglia_pm.presidio_id) # & \
        # (db.stato_presidio.presidio_id==presidio_id) &
        # (db.stato_presidio.stato_presidio_id==stato_presidio_id) &
        # (db.stato_presidio.timeref==timeref)
    ).select(
        db.pattuglia_pm.pattuglia_id.with_alias("idSquadra"),
        orderby = ~db.stato_presidio.timeref,
        limitby = (0,1,)
    ).first()

    Presidio.end(pattuglia.idSquadra)
    breakpoint()

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
