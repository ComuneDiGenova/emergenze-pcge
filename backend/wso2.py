
import requests
from getpass import getpass
from datetime import datetime
from datetime import timedelta
from urllib.parse import urljoin
from . import settings
from .common import logger

import json

# WSO2_URL = 'https://apitest.comune.genova.it:28243'
# WSO2_TOKEN_ROOT = 'manageToken/getToken'
# WSO2_VBT_ROOT = 'GestioneEmergenzeVerbatel'

VBT_PROT = "http"
VBT_HOST = "192.168.153.84"
VBT_PATH = "GestioneEmergenzeTest/api"

WSO2_URL = settings.WSO2_URL
WSO2_TOKEN_ROOT = settings.WSO2_TOKEN_ROOT
WSO2_VBT_ROOT = settings.WSO2_VBT_ROOT

class AccessTokenManager(object):
    url = WSO2_URL

    def __init__(self, key=settings.WSO2_KEY, secret=settings.WSO2_SECRET) -> None:
        self.key = key
        self.secret = secret
        self._token = None
        self.expire = None

    @property
    def access_token(self) -> str:
        if self._token is None or self.expire <= datetime.utcnow():
            response = requests.get(
                urljoin(self.url, WSO2_TOKEN_ROOT),
                params = {'key': self.key, 'secret': self.secret})
            info = response.json()
            self.expire = datetime.utcnow() + timedelta(seconds=info['expires_in'])
            self._token = info['access_token']
        else:
            logger.debug(f"Token will expire in {int((self.expire-datetime.utcnow()).total_seconds()):d} seconds.")

        return self._token

    @property
    def headers(self) -> dict:
        return {
            'Authorization': f'Bearer {self.access_token}',
        }

    def get(self, endpoint: str, data: dict = None) -> requests.Response:
        """ """
        response = requests.get(urljoin(self.url, endpoint), params=data, headers=self.headers)
        response.raise_for_status()
        return response

    def put(self, endpoint: str, data: dict = None) -> requests.Response:
        return requests.put(urljoin(self.url, endpoint), data=data, headers=self.headers)

    def post(self, endpoint, data: dict = None, json: dict = None) -> requests.Response:
        response = requests.post(
            urljoin(self.url, endpoint),
            data = data,
            json = json,
            headers = self.headers
        )
        # response.raise_for_status()
        return response

def test(key=settings.WSO2_KEY, secret=settings.WSO2_SECRET):
    """ """
    wso2 = AccessTokenManager(key, secret)

    info_evento = {'id': 174, 'inizio': '2023-01-18T17:43:56', 'fine': '2023-01-23T15:42:59.165413', 'fine_sospensione': None, 'chiusura': '2023-01-23T15:42:36.501646', 'valido': False, 'descrizione': 'Nivologico', 'municipi': ['Bassa Val Bisagno', 'Centro est', 'Centro Ovest', 'Levante', 'Media Val Bisagno', 'Medio Levante', 'Medio Ponente', 'Ponente', 'Val Polcevera'], 'foc': [{'fine': '2023-01-19T06:00:00', 'colore': '#009aff', 'inizio': '2023-01-18T14:00:00', 'descrizione': 'Attenzione'}], 'allerte': None, 'note': [{'nota': 'possibile neve notte tra 18 e 19 gennaio 23'}], 'stato': 'chiuso'}

    # vbt_url = urljoin(f'{VBT_PROT}://{VBT_HOST}', f'{VBT_PATH}/evento')
    # print(f"Chiamata all'URL: {vbt_url}")
    # response_from_verbatel = requests.post(vbt_url, data=info_evento)
    # print(f"Status della response ricevuta da Verbatel: {response_from_verbatel.status_code}")
    # print(f"Risposta ottenuta da Varbatel:\n{response_from_verbatel.text}")

    wso2_url = f'{WSO2_VBT_ROOT}/evento'
    response_from_wso2 = wso2.get(wso2_url, data=info_evento)

    print(response_from_wso2.text)

def test1(key=settings.WSO2_KEY, secret=settings.WSO2_SECRET):
    """ """
    wso2 = AccessTokenManager(key, secret)

    info_evento = {}

    # vbt_url = urljoin(f'{VBT_PROT}://{VBT_HOST}', f'{VBT_PATH}/evento')
    # print(f"Chiamata all'URL: {vbt_url}")
    # response_from_verbatel = requests.post(vbt_url, data=info_evento)
    # print(f"Status della response ricevuta da Verbatel: {response_from_verbatel.status_code}")
    # print(f"Risposta ottenuta da Varbatel:\n{response_from_verbatel.text}")

    wso2_url = f'{WSO2_VBT_ROOT}/segnalazione'
    response_from_wso2 = wso2.post(wso2_url, data=info_evento)

    print(response_from_wso2.text)

def test_v1_wso2(key=settings.WSO2_KEY, secret=settings.WSO2_SECRET):
    """ """
    
    wso2 = AccessTokenManager(key, secret)
    
    info = {
        "stato" : 3,
        "idSegnalazione": 1013,
        "eventoId": 165,
        "operatore": 'Operatore GE',
        "tipoIntervento": 9,
        "nomeStrada" : 'VIA BARI',
        "codiceStrada": "04020",
        "tipoLocalizzazione" : 3,
        "daSpecificare": '15',
        "noteOperative": 'Note Operative',
        "reclamante" : 'SINDACO',
        "telefonoReclamante": '3475208085',
        "tipoRichiesta": 1,
        "dataInserimento": '2021-06-23T11:00:00',
        "latitudine": '44.47245435996428',
        "longitudine": '8.895533415673095',
        "motivoRifiuto": ''
    }

    wso2_url = f'{WSO2_VBT_ROOT}/Interventi'
    response_from_wso2 = wso2.post(wso2_url, json=info)
    
    print(response_from_wso2.json())
    
def test_v1():
    
    info = {
        "stato" : 3,
        "idSegnalazione": 1013,
        "eventoId": 165,
        "operatore": 'Operatore GE',
        "tipoIntervento": 9,
        "nomeStrada" : 'VIA BARI',
        "codiceStrada": "04020",
        "tipoLocalizzazione" : 3,
        "daSpecificare": '15',
        "noteOperative": 'Note Operative',
        "reclamante" : 'SINDACO',
        "telefonoReclamante": '3475208085',
        "tipoRichiesta": 1,
        "dataInserimento": '2021-06-23T11:00:00',
        "latitudine": '44.47245435996428',
        "longitudine": '8.895533415673095',
        "motivoRifiuto": ''
    }
    
    vbt_url = urljoin(f'{VBT_PROT}://{VBT_HOST}', f'{VBT_PATH}/interventi')
    print(f"Chiamata all'URL: {vbt_url}")
    response_from_verbatel = requests.post(vbt_url, data=info)
    print(f"Status della response ricevuta da Verbatel: {response_from_verbatel.status_code}")
    print(f"Risposta ottenuta da Varbatel:\n{response_from_verbatel.text}")

if __name__=='__main__':
    import argparse
    
    parser = argparse.ArgumentParser(description='WSO2 testing script')

    parser.add_argument('-k', '--key', help='WSO2 authentication Key')

    args = parser.parse_args()

    if args.key is None:
        key = input('WSO2 authentication Key: ')
    else:
        key = args.key

    secret = getpass(prompt='secret: ')

    wso2 = AccessTokenManager(key, secret)

    info_evento = {'id': 174, 'inizio': '2023-01-18T17:43:56', 'fine': '2023-01-23T15:42:59.165413', 'fine_sospensione': None, 'chiusura': '2023-01-23T15:42:36.501646', 'valido': False, 'descrizione': 'Nivologico', 'municipi': ['Bassa Val Bisagno', 'Centro est', 'Centro Ovest', 'Levante', 'Media Val Bisagno', 'Medio Levante', 'Medio Ponente', 'Ponente', 'Val Polcevera'], 'foc': [{'fine': '2023-01-19T06:00:00', 'colore': '#009aff', 'inizio': '2023-01-18T14:00:00', 'descrizione': 'Attenzione'}], 'allerte': None, 'note': [{'nota': 'possibile neve notte tra 18 e 19 gennaio 23'}], 'stato': 'chiuso'}

    # vbt_url = urljoin(f'{VBT_PROT}://{VBT_HOST}', f'{VBT_PATH}/evento')
    # print(f"Chiamata all'URL: {vbt_url}")
    # response_from_verbatel = requests.post(vbt_url, data=info_evento)
    # print(f"Status della response ricevuta da Verbatel: {response_from_verbatel.status_code}")
    # print(f"Risposta ottenuta da Varbatel:\n{response_from_verbatel.text}")

    wso2_url = f'{WSO2_VBT_ROOT}/evento'
    response_from_wso2 = wso2.get(wso2_url, data=info_evento)

    print(response_from_wso2.text)

