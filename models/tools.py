# -*- coding: utf-8 -*-

from ..common import db

new_id = lambda table: db(table).select(table.id, orderby=~table.id, limitby=(0,1,)).first().id+1
