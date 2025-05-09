# -*- coding: utf-8 -*-

import argparse
import select
from . import settings
from .common import db, logger
#from . import evento
#from .verbatel import evento, Evento
import json
# from .verbatel import syncEvento
from .verbatel import EventoWSO2
from . import evento
import traceback
from .segnalazione import after_insert_lavorazione, after_update_lavorazione, \
    after_insert_t_storico_segnalazioni_in_lavorazione, \
    after_insert_comunicazione_segnalazione
from .incarico import after_insert_incarico, after_update_incarico

from .incarico.comunicazione import after_insert_comunicazione as after_insert_comunicazione_incarico
from .presidio_mobile.comunicazione import after_insert_comunicazione as after_insert_comunicazione_presidio_mobile
from .presidio_mobile.squadra import after_insert_stato_presidio

from psycopg2.extensions import ISOLATION_LEVEL_AUTOCOMMIT

#def create_sql_function(schema, table, function, trigger, notification):

import psycopg2
import selectors, time

syncEvento = EventoWSO2()

# Non rimuovere!
db._adapter.connection.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)

def create_sql_function(schema, function_name, notification_name, payload, action):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_name}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = '{action}') then
            perform pg_notify('{notification_name}',
            json_build_object(
                 'id', NEW.{payload}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)


def create_sql_function_true(schema, function_insert, notification_insert, function_update, notification_update, payload):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_insert}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = 'INSERT') then
            perform pg_notify('{notification_insert}',
            json_build_object(
                 'id', NEW.{payload}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)

    sql_notify_updated_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_update}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = 'UPDATE') then
            perform pg_notify('{notification_update}',
            json_build_object(
                 'id', NEW.{payload}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_updated_item)


def create_sql_function_comunicazione(schema, function_name, notification_name, payload, action):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_name}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = '{action}') then
            perform pg_notify('{notification_name}',
            json_build_object(
                 'id', NEW.{payload[0]},
                 'data', NEW.{payload[1]}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)

def create_sql_function_stato_presidio(schema, function_name, notification_name, payload, action):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_name}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = '{action}') then
            perform pg_notify('{notification_name}',
            json_build_object(
                 'id_sopralluogo', NEW.{payload[0]},
                 'id_stato_sopralluogo', NEW.{payload[1]},
                 'data_ora_stato', NEW.{payload[2]}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)

def create_sql_function_lavorazione(schema, function_name, notification_name, payload, action):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_name}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = '{action}') then
            perform pg_notify('{notification_name}',
            json_build_object(
                 'id', NEW.{payload[0]},
                 'in_lavorazione', NEW.{payload[1]}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)

def create_sql_function_storico_vavorazione(schema:str, function_name:str, notification_name:str, payload:list, action:str) -> None:
    """
    """
    
    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_name}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = '{action}') then
            perform pg_notify('{notification_name}',
            json_build_object(
                 'id_lavorazione', NEW.{payload[0]},
                 'messaggio_log', NEW.{payload[1]}
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)

def create_sql_trigger(schema, table, function_name, trigger_name, action):
    clear_trigger_insert = f'DROP TRIGGER IF EXISTS {trigger_name} on "{schema}"."{table}"';

    create_trigger_insert = f"""CREATE TRIGGER {trigger_name}
        AFTER {action}
        ON "{schema}"."{table}"
        FOR EACH ROW
        EXECUTE PROCEDURE {schema}.{function_name}();"""

    db.executesql(clear_trigger_insert)
    db.commit()
    db.executesql(create_trigger_insert)


def create_sql_trigger_true(schema, table, function_insert, trigger_insert, function_update, trigger_update):
    clear_trigger_insert = f'DROP TRIGGER IF EXISTS {trigger_insert} on "{schema}"."{table}"';

    create_trigger_insert = f"""CREATE TRIGGER {trigger_insert}
        AFTER INSERT
        ON "{schema}"."{table}"
        FOR EACH ROW
        EXECUTE PROCEDURE {schema}.{function_insert}();"""

    db.executesql(clear_trigger_insert)
    db.commit()
    db.executesql(create_trigger_insert)

    clear_trigger_update = f'DROP TRIGGER IF EXISTS {trigger_update} on "{schema}"."{table}"';

    create_trigger_update = f"""CREATE TRIGGER {trigger_update}
        AFTER UPDATE
        ON "{schema}"."{table}"
        FOR EACH ROW
        EXECUTE PROCEDURE {schema}.{function_update}();"""

    db.executesql(clear_trigger_update)
    db.commit()
    db.executesql(create_trigger_update)

# list of list, the inner list contains [schema, tabella, payload in a form of string]
# # terzo elemento old ['segnalazioni','t_incarichi','id']
elementi = [
    ['eventi','join_tipo_foc', 'id_evento'],
    ['eventi','t_note_eventi','id_evento'],
    #,['segnalazioni','join_segnalazioni_incarichi','id_incarico']
]

segnalaz = [
    ['segnalazioni','join_segnalazioni_incarichi','id_incarico'],
    ['segnalazioni','t_incarichi','id'],
    ['segnalazioni','join_segnalazioni_in_lavorazione','id_segnalazione_in_lavorazione'],
    ['segnalazioni','t_comunicazioni_incarichi_inviate',['id_incarico', 'data_ora_stato']],
    ['segnalazioni','t_comunicazioni_sopralluoghi_mobili_inviate',['id_sopralluogo','data_ora_stato']],
    ['segnalazioni', 'stato_sopralluoghi_mobili', ['id_sopralluogo', 'id_stato_sopralluogo', 'data_ora_stato']],
    ['eventi', 't_eventi', 'id'],
    ['segnalazioni', 't_segnalazioni_in_lavorazione', ['id', 'in_lavorazione']],
    ['segnalazioni', 't_storico_segnalazioni_in_lavorazione', ['id_segnalazione_in_lavorazione', 'log_aggiornamento']],
    ['segnalazioni', 't_comunicazioni_segnalazioni', ['id_lavorazione', 'data_ora_stato']]
    ]

def setup():
    """ Set up connection, run only one time"""

    # pre il momento semplifico la cosa in questa maniera
    #elementi = [['segnalazioni','t_segnalazioni']]
    # per quanto  concerne gli eventi
    for el in elementi:

        function_name_insert = f"notify_new_{el[1]}"
        notification_name_insert = f"new_{el[1]}_added"
        trigger_name_insert = f"after_insert_{el[1]}"

        function_name_update = f"notify_updated_{el[1]}"
        notification_name_update = f"new_{el[1]}_updated"
        trigger_name_update = f"after_updated_{el[1]}"

        create_sql_function_true( el[0], function_name_insert, notification_name_insert , function_name_update , notification_name_update, el[2])
        create_sql_trigger_true( el[0], el[1], function_name_insert, trigger_name_insert, function_name_update, trigger_name_update)

    db.commit()

def setup_segn():
    """ setup segnalaz"""
    # per quanto concerne le segnalaz
    function_name_n = f"notify_new_{segnalaz[0][1]}"
    notification_name_n = f"new_{segnalaz[0][1]}_added"
    trigger_name_n = f"after_insert_{segnalaz[0][1]}"
    create_sql_function(segnalaz[0][0], function_name_n, notification_name_n, segnalaz[0][2], "INSERT")
    create_sql_trigger(segnalaz[0][0], segnalaz[0][1], function_name_n, trigger_name_n, "INSERT")

    function_name_u = f"notify_updated_{segnalaz[1][1]}"
    notification_name_u = f"new_{segnalaz[1][1]}_updated"
    trigger_name_u = f"after_updated_{segnalaz[1][1]}"
    create_sql_function(segnalaz[1][0], function_name_u, notification_name_u, segnalaz[1][2], "UPDATE")
    create_sql_trigger(segnalaz[1][0], segnalaz[1][1], function_name_u, trigger_name_u, "UPDATE")

    function_name_n_lav = f"notify_new_{segnalaz[2][1]}"
    notification_name_n_lav = f"new_{segnalaz[2][1]}_added"
    trigger_name_n_lav = f"after_insert_{segnalaz[2][1]}"
    create_sql_function(segnalaz[2][0], function_name_n_lav, notification_name_n_lav, segnalaz[2][2], "INSERT")
    create_sql_trigger(segnalaz[2][0], segnalaz[2][1], function_name_n_lav, trigger_name_n_lav, "INSERT")

    #elemento 4 ovver [3] comunicazioni
    function_name_n_com = f"notify_new_{segnalaz[3][1]}"
    notification_name_n_com = f"new_{segnalaz[3][1]}_added"
    trigger_name_n_com = f"after_insert_{segnalaz[3][1]}"
    create_sql_function_comunicazione(segnalaz[3][0], function_name_n_com, notification_name_n_com, segnalaz[3][2], "INSERT")
    create_sql_trigger(segnalaz[3][0], segnalaz[3][1], function_name_n_com, trigger_name_n_com, "INSERT")

    #elemento 5 ovver [4] comunicazioni
    function_name_n_comsopr = f"notify_new_{segnalaz[4][1]}"
    notification_name_n_comsopr = f"new_{segnalaz[4][1]}_added"
    trigger_name_n_comsopr = f"after_insert_{segnalaz[4][1]}"
    create_sql_function_comunicazione(segnalaz[4][0], function_name_n_comsopr, notification_name_n_comsopr, segnalaz[4][2], "INSERT")
    create_sql_trigger(segnalaz[4][0], segnalaz[4][1], function_name_n_comsopr, trigger_name_n_comsopr, "INSERT")

    function_name_n_stato_presidio = f"notify_new_{segnalaz[5][1]}"
    notification_name_n_stato_presidio = f"new_{segnalaz[5][1]}_added"
    trigger_name_n_stato_presidio = f"after_insert_{segnalaz[5][1]}"
    create_sql_function_stato_presidio(segnalaz[5][0], function_name_n_stato_presidio, notification_name_n_stato_presidio, segnalaz[5][2], "INSERT")
    create_sql_trigger(segnalaz[5][0], segnalaz[5][1], function_name_n_stato_presidio, trigger_name_n_stato_presidio, "INSERT")

    function_name_u_stato_presidio = f"notify_updated_{segnalaz[5][1]}"
    notification_name_u_stato_presidio = f"new_{segnalaz[5][1]}_updated"
    trigger_name_u_stato_presidio = f"after_updated_{segnalaz[5][1]}"
    create_sql_function_stato_presidio(segnalaz[5][0], function_name_u_stato_presidio, notification_name_u_stato_presidio, segnalaz[5][2], "UPDATE")
    create_sql_trigger(segnalaz[5][0], segnalaz[5][1], function_name_u_stato_presidio, trigger_name_u_stato_presidio, "UPDATE")

    function_name_u_evento = f"notify_updated_{segnalaz[6][1]}"
    notification_name_u_evento = f"new_{segnalaz[6][1]}_updated"
    trigger_name_u_evento = f"after_updated_{segnalaz[6][1]}"
    create_sql_function(segnalaz[6][0], function_name_u_evento, notification_name_u_evento, segnalaz[6][2], "UPDATE")
    create_sql_trigger(segnalaz[6][0], segnalaz[6][1], function_name_u_evento, trigger_name_u_evento, "UPDATE")

    function_name_u_lavorazione = f"notify_updated_{segnalaz[7][1]}"
    notification_name_u_lavorazione = f"new_{segnalaz[7][1]}_updated"
    trigger_name_u_lavorazione = f"after_updated_{segnalaz[7][1]}"
    create_sql_function_lavorazione(
        segnalaz[7][0],
        function_name_u_lavorazione,
        notification_name_u_lavorazione,
        segnalaz[7][2],
        "UPDATE"
    )
    create_sql_trigger(
        segnalaz[7][0],
        segnalaz[7][1],
        function_name_u_lavorazione,
        trigger_name_u_lavorazione,
        "UPDATE"
    )

    function_name_n_storico_lavorazione = f"notify_new_{segnalaz[8][1]}"
    notification_name_n_storico_lavorazione = f"new_{segnalaz[8][1]}_added"
    trigger_name_n_storico_lavorazione = f"after_insert_{segnalaz[8][1]}"
    create_sql_function_storico_vavorazione(
        segnalaz[8][0],
        function_name_n_storico_lavorazione,
        notification_name_n_storico_lavorazione,
        segnalaz[8][2], "INSERT")
    create_sql_trigger(
        segnalaz[8][0],
        segnalaz[8][1],
        function_name_n_storico_lavorazione, trigger_name_n_storico_lavorazione, "INSERT")

    
    function_name_n_comunicazione_segnalazione = f"notify_new_{segnalaz[9][1]}"
    notification_name_n_comunicazione_segnalazione = f"new_{segnalaz[9][1]}_added"
    trigger_name_n_comunicazione_segnalazione = f"after_insert_{segnalaz[9][1]}"
    create_sql_function_comunicazione(
        segnalaz[9][0],
        function_name_n_comunicazione_segnalazione,
        notification_name_n_comunicazione_segnalazione,
        segnalaz[9][2],
        "INSERT"
    )
    create_sql_trigger(
        segnalaz[9][0],
        segnalaz[9][1],
        function_name_n_comunicazione_segnalazione,
        trigger_name_n_comunicazione_segnalazione,
        "INSERT"
    )

    db.commit()

# def ciao():
#     """ test pourpouses"""
#     print("hell-o")
#     print(f"new_{elementi[0][0]}_added")

def set_listen():
    # db._adapter.reconnect()
    listen_n_foc = f"LISTEN new_{elementi[0][1]}_added;"
    listen_u_foc = f"LISTEN new_{elementi[0][1]}_updated;"
    listen_n_nota = f"LISTEN new_{elementi[1][1]}_added;"
    listen_u_nota = f"LISTEN new_{elementi[1][1]}_updated;"
        #interventi
    listen_n_interventi = f"LISTEN new_{segnalaz[0][1]}_added;"
    listen_u_interventi = f"LISTEN new_{segnalaz[1][1]}_updated;"
            #segnalaz
    listen_n_interventi_lav = f"LISTEN new_{segnalaz[2][1]}_added;"
            #scomunicaz
    listen_n_interventi_com = f"LISTEN new_{segnalaz[3][1]}_added;"
    listen_n_interventi_comsopr = f"LISTEN new_{segnalaz[4][1]}_added;"

    listen_n_stato_presidio = f"LISTEN new_{segnalaz[5][1]}_added;"
    listen_u_stato_presidio = f"LISTEN new_{segnalaz[5][1]}_updated;"

    listen_u_evento = f"LISTEN new_{segnalaz[6][1]}_updated;"

    listen_u_lavorazione = f"LISTEN new_{segnalaz[7][1]}_updated;"
    
    listen_n_storico_lavorazione = f"LISTEN new_{segnalaz[8][1]}_added;"
    
    listen_n_comunicazione_segnalazione = f"LISTEN new_{segnalaz[9][1]}_added;"

    #db.executesql("LISTEN new_item_added;")
    db.executesql(listen_n_foc)
    db.executesql(listen_u_foc)
    db.executesql(listen_n_nota)
    db.executesql(listen_u_nota)
    #interventi
    db.executesql(listen_n_interventi)
    db.executesql(listen_u_interventi)

    db.executesql(listen_n_interventi_lav)

    db.executesql(listen_n_interventi_com)
    db.executesql(listen_n_interventi_comsopr)

    db.executesql(listen_n_stato_presidio)
    db.executesql(listen_u_stato_presidio)

    db.executesql(listen_u_evento)
    
    db.executesql(listen_u_lavorazione)
    
    db.executesql(listen_n_storico_lavorazione)
    db.executesql(listen_n_comunicazione_segnalazione)

    db.commit()

def do_stuff(channel, **payload):

    #scrivere do_stuff in maniera che evento.fetch venga chiamata solo per gli eventi??
    #mio_evento = evento.fetch(id=payload["id"])

    logger.debug(f"NOTIFICATION CHANNEL: {channel} PAYLOAD: {payload}")

    if settings.VBT_HOST is None:
        return

    if channel in [
        f"new_{elementi[0][1]}_added",
        f"new_{elementi[0][1]}_updated"
    ]:
        # Creazione/aggiornamento FOC

        mio_evento = evento.fetch(id=payload["id"])

        # In caso di FOC mio_evento NON deve poter essere nullo
        out = syncEvento(mio_evento)
        logger.debug(out)

        if out == 'SENT NEW':
            # nuovoEventoDaFoc restituisce None solo incaso di UPDATE
            newid = db.evento_inviato.insert(
                evento_id = payload["id"]
            )
            logger.debug(f"Segnato! {newid}")

    #elif not mio_evento is None and channel in [
    elif channel in [
        f"new_{elementi[1][1]}_added",
        f"new_{elementi[1][1]}_updated"
    ]:

        # Creazione/aggiornamento nota
        # INFO: Questo evento di fatto potrebbe essere inutile.
        # da interfaccia sembra che le note possano essere create solo in concomitanza
        # del nuovo evento e mai modificate.

        mio_evento = evento.fetch(id=payload["id"])

        if not mio_evento is None:
            out = syncEvento(mio_evento)

    #listen_n_interventi = f"LISTEN new_{segnalaz[0][1]}_added;"
    #listen_u_interventi = f"LISTEN new_{segnalaz[1][1]}_updated;"
    elif channel in f"new_{segnalaz[1][1]}_updated":
        after_update_incarico(payload["id"])

    elif channel in f"new_{segnalaz[0][1]}_added":
        after_insert_incarico(payload["id"])
    elif channel in f"new_{segnalaz[2][1]}_added":
        after_insert_lavorazione(payload["id"])

    elif channel in f"new_{segnalaz[3][1]}_added":
        after_insert_comunicazione_incarico(payload["id"], payload["data"])
        #after_insert_com(payload["id"])
    elif channel in f"new_{segnalaz[4][1]}_added":
        after_insert_comunicazione_presidio_mobile(payload["id"], payload["data"])
        #after_insert_comsopr(payload["id"])
    elif channel in [
        f"new_{segnalaz[5][1]}_added",
        f"new_{segnalaz[5][1]}_updated"
    ]:
         # 'presidio_id', NEW.{payload[0]},
         # 'stato_presidio_id', NEW.{payload[1]},
         # 'timeref', NEW.{payload[2]}

        after_insert_stato_presidio(payload['id_sopralluogo'], payload['id_stato_sopralluogo'], payload['data_ora_stato'])
    elif channel in f"new_{segnalaz[6][1]}_updated":
        mio_evento = evento.fetch(id=payload["id"], _foc_only=False)

        if not mio_evento is None:
            out = syncEvento(mio_evento)
    
    elif channel in f"new_{segnalaz[7][1]}_updated":
        after_update_lavorazione(payload["id"], payload["in_lavorazione"])
    
    elif channel in f"new_{segnalaz[8][1]}_added":
        after_insert_t_storico_segnalazioni_in_lavorazione(payload["id_lavorazione"], payload["messaggio_log"])

    elif channel in f"new_{segnalaz[9][1]}_added":
        after_insert_comunicazione_segnalazione(lavorazione_id=payload["id"], timeref=payload["data"])
    else:
        logger.warning(f"Channel not catched: {channel}")
        logger.warning(payload)


def wait_for_notifications(sleep=1):
    """ Courtesy of: https://chatgpt.com/share/c0369392-adbb-475b-93b6-39666caf4515
    """
    logger.info('Starting!')
    logger.info('Waiting for notifications!')
    queue = []
    # Creare un oggetto Selector
    selector = selectors.DefaultSelector()

    # Registrare il file descriptor per la connessione del database per la lettura
    # (supponendo che db._adapter.connection sia il file descriptor)
    fileobj = db._adapter.connection
    selector.register(fileobj, selectors.EVENT_READ)
    # Ora puoi usare il metodo select() del selettore per controllare se ci sono eventi pronti
    # per la lettura sul file descriptor della connessione del database
    events = selector.select(timeout=None)  # Puoi specificare un timeout in secondi se necessario
    # time.sleep(sleep)

    # events sarà una lista di tuple, ognuna delle quali contiene un oggetto SelectorKey e un mask
    for key, mask in events:
        if key.fileobj == fileobj:
            # Eseguire azioni in base al tipo di evento che è pronto
            if mask & selectors.EVENT_READ:
                # L'evento di lettura è pronto sul file descriptor della connessione del database
                # Esegui le azioni appropriate qui
                db._adapter.connection.poll()
                while db._adapter.connection.notifies:
                    notification = db._adapter.connection.notifies.pop()
                    # https://github.com/psycopg/psycopg2/issues/686#issuecomment-591725603
                    # db._adapter.connection.poll()
                    logger.debug(notification)
                    yield notification

def listen():
    """ Courtesy of: https://towardsdev.com/simple-event-notifications-with-postgresql-and-python-398b29548cef """

    set_listen()
    
    logger.info(db.executesql('SELECT * FROM pg_listening_channels();'))
    
    while True:

        for notification in wait_for_notifications():

            payload = json.loads(notification.payload)

            try:
                do_stuff(notification.channel, **payload)
            except:
                # così si evita che questo script cada
                # in caso di errori cercare il traceback nel log
                db.rollback()
                full_traceback = traceback.format_exc()
                logger.critical(full_traceback)
            else:
                db.commit()
        


if __name__ == '__main__':

    parser = argparse.ArgumentParser(description='DB event listener management.')
    parser.add_argument_group('-s', '--set-up', dest='setup',
        action='store_true', default=False
    )

    args = parser.parse_args()

    if args.setup:
        setup()

    listen()
