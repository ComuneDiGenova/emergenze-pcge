# -*- coding: utf-8 -*-

import argparse
import select
from .common import db, logger

def create_sql_function(schema, table, function, trigger, notification):

    sql_notify_new_item = f"""CREATE or REPLACE FUNCTION {schema}.{function}()
        RETURNS trigger
         LANGUAGE 'plpgsql'
    as $BODY$
    declare
    begin
        if (tg_op = 'INSERT') then
            perform pg_notify('{notification}',
            json_build_object(
                 'id', NEW.id
               )::text);
        end if;

        return null;
    end
    $BODY$;"""

    db.executesql(sql_notify_new_item)

def create_sql_trigger(schema, table, function, trigger):
    clear_trigger = f'DROP TRIGGER IF EXISTS {trigger} on "{schema}"."{table}"';

    create_trigger = f"""CREATE TRIGGER {trigger}
        AFTER INSERT
        ON "{schema}"."{table}"
        FOR EACH ROW
        EXECUTE PROCEDURE {schema}.{function}();"""

    db.executesql(clear_trigger)
    db.executesql(create_trigger)

def trigger_setup(schema, table, function, trigger, notification):
    """ """


sqlloc = lambda tablename: db[tablename]._rname.split('.')

def setup():
    """ Set up connection, run only one time"""

    schema, table = sqlloc['segnalazione_lavorazione']
    create_sql_function(schema, table,
        'notify_new_lavorazione',
        'after_lavorazione_create',
        'new_lavorazione_added'
    )
    create_sql_trigger(schema, table, function, trigger)

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
