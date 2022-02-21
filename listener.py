# -*- coding: utf-8 -*-

import argparse
import select
from .common import db, logger
#from . import evento
#from .verbatel import evento, Evento
import json
from .verbatel import syncEvento
from . import evento
import traceback

#def create_sql_function(schema, table, function, trigger, notification):

def create_sql_function(schema, function_insert, notification_insert, function_update, notification_update, payload):

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


def create_sql_trigger(schema, table, function_insert, trigger_insert, function_update, trigger_update):
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
elementi = [['eventi','join_tipo_foc', 'id_evento'],['eventi','t_note_eventi','id_evento'],['segnalazioni','t_incarichi','id']]

def setup():
    """ Set up connection, run only one time"""

    # pre il momento semplifico la cosa in questa maniera
    #elementi = [['segnalazioni','t_segnalazioni']]
    
    for el in elementi:
        
        function_name_insert = f"notify_new_{el[1]}"
        notification_name_insert = f"new_{el[1]}_added"
        trigger_name_insert = f"after_insert_{el[1]}"
        
        function_name_update = f"notify_updated_{el[1]}"
        notification_name_update = f"new_{el[1]}_updated"
        trigger_name_update = f"after_updated_{el[1]}"
        
        create_sql_function( el[0], function_name_insert, notification_name_insert , function_name_update , notification_name_update, el[2])
        create_sql_trigger( el[0], el[1], function_name_insert, trigger_name_insert, function_name_update, trigger_name_update)
        
    db.commit()

def ciao():
    """ test pourpouses"""
    print("hell-o")
    print(f"new_{elementi[0][0]}_added")

def set_listen():
    # db._adapter.reconnect()
    listen_n_foc = f"LISTEN new_{elementi[0][1]}_added;"
    listen_u_foc = f"LISTEN new_{elementi[0][1]}_updated;"
    listen_n_nota = f"LISTEN new_{elementi[1][1]}_added;"
    listen_u_nota = f"LISTEN new_{elementi[1][1]}_updated;"
        #interventi
    listen_n_interventi = f"LISTEN new_{elementi[2][1]}_added;"
    listen_u_interventi = f"LISTEN new_{elementi[2][1]}_updated;"
    
    #db.executesql("LISTEN new_item_added;")
    db.executesql(listen_n_foc)
    db.executesql(listen_u_foc)
    db.executesql(listen_n_nota)
    db.executesql(listen_u_nota)
        #interventi
    db.executesql(listen_n_interventi)
    db.executesql(listen_u_interventi)
    
    db.commit()

def do_stuff(channel, **payload):

    mio_evento = evento.fetch(id=payload["id"])

    if channel in [
        f"new_{elementi[0][1]}_added", 
        f"new_{elementi[0][1]}_updated"
    ]:
        # Creazione/aggiornamento FOC
        
        logger.debug(payload)

        # In caso di FOC mio_evento NON deve poter essere nullo
        out = syncEvento(mio_evento)
        logger.debug(out)
        logger.debug(f"NOTIFICATION CHANNEL: {channel} PAYLOAD: {payload}")
        if out == 'SENT NEW':
            # nuovoEventoDaFoc restituisce None solo incaso di UPDATE
            newid = db.evento_inviato.insert(
                evento_id = payload["id"]
            )
            logger.debug(f"Segnato! {newid}")
    elif not mio_evento is None and channel in [
        f"new_{elementi[1][1]}_added",
        f"new_{elementi[1][1]}_updated"
    ]:
        # Creazione/aggiornamento nota
        # INFO: Questo evento di fatto potrebbe essere inutile.
        # da interfaccia sembra che le note possano essere create solo in concomitanza
        # del nuovo evento e mai modificate.
        out = syncEvento(mio_evento)
        logger.debug(f"NOTIFICATION CHANNEL: {channel} PAYLOAD: {payload}")
    elif channel in [
        f"new_{elementi[2][1]}_added",
        f"new_{elementi[2][1]}_updated"
    ]:
        logger.debug(f"NOTIFICATION CHANNEL: {channel} PAYLOAD: {payload}")
        
def listen():
    """ Courtesy of: https://towardsdev.com/simple-event-notifications-with-postgresql-and-python-398b29548cef """

    while True:
        set_listen()
        # sleep until there is some data
        logger.debug('Waiting!')
        select.select([db._adapter.connection],[],[])
        logger.debug('Catched!')

        db._adapter.connection.poll()

        while db._adapter.connection.notifies:

            notification = db._adapter.connection.notifies.pop(0)

            payload = json.loads(notification.payload)

            try:
                do_stuff(notification.channel, **payload)
            except:
                # così si evita che il questo script cada
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
    
    # pseudocodice<
    #mio_evento = evento.fetch(id = 110)
	#Evento.create(**mio_evento)
