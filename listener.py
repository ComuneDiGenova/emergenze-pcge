# -*- coding: utf-8 -*-

import argparse
import select
from .common import db, logger
#from . import evento
#from .verbatel import evento, Evento
import json
from .verbatel import nuovoEventoDaFoc

#def create_sql_function(schema, table, function, trigger, notification):
def create_sql_function(schema, function_insert, notification_insert, function_update, notification_update):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function_insert}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = 'INSERT') then
            perform pg_notify('{notification_insert}',
            json_build_object(
                 'id', NEW.id_evento
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
                 'id', NEW.id_evento
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

elementi = [['eventi','join_tipo_foc'],['eventi','t_note_eventi']]

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
        
        create_sql_function( el[0], function_name_insert, notification_name_insert , function_name_update , notification_name_update)
        create_sql_trigger( el[0], el[1], function_name_insert, trigger_name_insert, function_name_update, trigger_name_update)
        
    db.commit()

def ciao():
    """ test pourpouses"""
    print("hell-o")
    print(f"new_{elementi[0][0]}_added")
    
def listen():
    """ Courtesy of: https://towardsdev.com/simple-event-notifications-with-postgresql-and-python-398b29548cef """

    listen_n = f"LISTEN new_{elementi[0][1]}_added;"
    listen_u = f"LISTEN new_{elementi[0][1]}_updated;"
    listen_n_nota = f"LISTEN new_{elementi[1][1]}_added;"
    listen_u_nota = f"LISTEN new_{elementi[1][1]}_updated;"
    #db.executesql("LISTEN new_item_added;")
    db.executesql(listen_n)
    db.executesql(listen_u)
    db.executesql(listen_n_nota)
    db.executesql(listen_u_nota)
    db.commit()

    while True:
        # sleep until there is some data
        select.select([db._adapter.connection],[],[])

        db._adapter.connection.poll()

        while db._adapter.connection.notifies:

            notification = db._adapter.connection.notifies.pop(0)

            # do whatever you want with the ID of the new row in segnalazioni.t_segnalazioni
            #logger.debug(f"channel: {notification.channel }")
            #logger.debug(f"message: {notification.payload}")
            
            if notification.channel in [f"new_{elementi[0][1]}_added", 
                                        f"new_{elementi[0][1]}_updated",
                                        f"new_{elementi[1][1]}_added",
                                        f"new_{elementi[1][1]}_updated"
                                        ]:
                
                id_actual = json.loads(notification.payload)
                
                #mio_evento = evento.fetch(id = id_actual["id"])
                nuovoEventoDaFoc(id_actual["id"])
                logger.debug(f"NOTIFICATION CHANNEL: {notification.channel} PAYLOAD: {notification.payload}")

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
