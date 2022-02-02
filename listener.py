# -*- coding: utf-8 -*-

import argparse
import select
from .common import db, logger

sql_notify_new_item = """CREATE or REPLACE FUNCTION segnalazioni.notify_new_item()
    RETURNS trigger
     LANGUAGE 'plpgsql'
as $BODY$
declare
begin
    if (tg_op = 'INSERT') then

        perform pg_notify('new_item_added',
        json_build_object(
             'id', NEW.id
           )::text);
    end if;

    return null;
end
$BODY$;"""

TRIGGER = 'after_insert_item'
SCHEMA = 'segnalazioni'
TABLE = 't_segnalazioni'

clear_trigger = f'DROP TRIGGER IF EXISTS {TRIGGER} on "{SCHEMA}"."{TABLE}"';

create_trigger = f"""CREATE TRIGGER after_insert_item
    AFTER INSERT
    ON "{SCHEMA}"."{TABLE}"
    FOR EACH ROW
    EXECUTE PROCEDURE segnalazioni.notify_new_item();"""

def setup():
    """ Set up connection, run only one time"""
    db.executesql(sql_notify_new_item)
    db.executesql(clear_trigger)
    db.executesql(create_trigger)
    db.commit()


def listen():
    """ Courtesy of: https://towardsdev.com/simple-event-notifications-with-postgresql-and-python-398b29548cef """

    db.executesql("LISTEN new_item_added;")
    db.commit()
    
    while True:
        # sleep until there is some data
        select.select([db._adapter.connection],[],[])
        
        db._adapter.connection.poll()
        
        while db._adapter.connection.notifies:
            
            notification = db._adapter.connection.notifies.pop(0)
            
            # do whatever you want with the ID of the new row in segnalazioni.t_segnalazioni
            logger.debug(f"channel: {notification.channel }")
            logger.debug(f"message: {notification.payload}")
            
            print(f"here we are {notification.channel} and {notification.payload}")

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='DB event listener management.')
    parser.add_argument_group('-s', '--set-up', dest='setup',
        action='store_true', default=False
    )

    args = parser.parse_args()

    if args.setup:
        setup()

    listen()
