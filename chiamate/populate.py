# -*- coding: utf-8 -*-

import pandas, pathlib
from ..common import db
from datetime import datetime
# from .tools import PC

FILEPATH = 'Chiamate_Vocali_Genova_29102021_alert_system.xlsx'

def populate(df, ref_date=None):
    for _,row in df.iterrows():

        db.recupero.validate_and_insert(
            nome = row.NOME.strip(),
            cognome = row.COGNOME.strip(),
            numero = row.TELEFONO.strip(),
            indirizzoCompleto = f"{row['VIA/PIAZZA'].strip()} {row.INDIRIZZO.strip()}",
            numeroCivico = row.CIVICO.strip(),
            gruppo = row.GRUPPO.strip()
        )


def load(path=FILEPATH):
    last_modified = datetime.fromtimestamp(pathlib.Path(FILEPATH).stat().st_mtime).date()
    df = pandas.read_excel(FILEPATH, engine='openpyxl', dtype=str)
    populate(df, ref_date=last_modified)
    db.commit()

# if __name__ == '__main__':
#     df = pandas.read_excel(FILEPATH, engine='openpyxl')
#     populate(df)
