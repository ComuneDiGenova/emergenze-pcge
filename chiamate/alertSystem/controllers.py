# -*- coding: utf-8 -*-

from datetime import datetime, timedelta
import geojson, json

from mptools.frameworks.py4web import shampooform as sf

from py4web import action, request, abort, redirect, URL, Field
from py4web.utils.form import Form
from pydal.validators import *
# from pydal.validators import Validator

from alertsystem import model
from alertsystem.azioni import do as alert_do
from pprint import pformat, pprint
from py4web.core import HTTP

from ...common import (
    session,
    T,
    cache,
    auth,
    logger,
    authenticated,
    unauthenticated,
    flash,
    db,
    cors,
    alertsystem_config,
)

def general_error_message(
    form: Form,
    error_message: str = "Bad Request",
    error_code: int = 400,
):
    """Return a general error message when using POST message"""
    if form == None:
        return {
            "error_code": error_code,
            "error_message": error_message,
        }
    error_body = sf.form2dict(form)
    error_body["error_status"] = f"{error_code} {error_message}"
    raise HTTP(
        status=error_code,
        body=json.dumps(error_body),
        headers={"Content-Type": "application/json"},
    )

# ?------------------------------------------------------------
# ?------------------------------------------------------------
# ?------------------------------------------------------------
# ? To run the docker container
# ? docker-compose -f docker-compose-dev.yml up -d web


@action("user_campaign/_get_campaign_from_to", method=["POST"])
@action.uses(cors)
def user_campaign_get_campaign_from_to():
    """user_campaign_get_campaign_from_to _summary_

    Returns
    -------
    _type_
        _description_
    """
    form = Form(
        [
            Field(
                "date_start",
                "datetime",
                requires=IS_EMPTY_OR(IS_DATETIME("%Y-%m-%d %H:%M"))
                and IS_DATETIME_IN_RANGE(
                    minimum=datetime(2020, 1, 1),
                    maximum=datetime.now() + timedelta(days=1),
                ),
            ),
            Field(
                "date_end",
                "datetime",
                requires=IS_EMPTY_OR(IS_DATETIME("%Y-%m-%d %H:%M"))
                and IS_DATETIME_IN_RANGE(
                    minimum=datetime(2020, 1, 1)
                    + timedelta(seconds=1),
                    maximum=datetime.now() + timedelta(days=1),
                ),
            ),
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="_get_campaign_from_to",
        csrf_protection=False,
    )
    logger.debug(
        f"tuple_of_campaigns: {pformat(form, indent=4, width=1)}"
    )
    if form.accepted:
        date_start: datetime = form.vars.get("date_start")
        date_end: datetime = form.vars.get("date_end")
        logger.debug(
            f"date_start: {date_start} and date_end: {date_end}"
        )
        # ? alert_do.ricerca_campagne is using strftime to convert str to datetime so str mmust be passed
        (
            tuple_of_campaigns,
            alertsystem_response_status,
        ) = alert_do.ricerca_campagne(
            cfg=alertsystem_config,
            start_date=date_start,
            end_date=date_end,
        )
        tuple_of_campaigns = dict(
            (x.id_campagna, x) for x in tuple_of_campaigns
        )
        return {
            "result": tuple_of_campaigns,
            "alertsystem_response_status": alertsystem_response_status,
        }
    else:
        general_error_message(form=form)


# // TODO retrieve reposne status as well
@action("user_campaign/_retrive_message_list", method=["GET", "OPTIONS"])
@action.uses(cors)
def user_campaign_retrive_message_list():
    """user_campaign_retrive_message_list _summary_

    Returns
    -------
    _type_
        _description_
    """
    (
        message_list,
        alertsystem_response_status,
    ) = alert_do.visualizza_messaggi(cfg=alertsystem_config)
    message_list = dict((x.id_messaggio, x) for x in message_list)
    logger.debug(
        f"\talertsystem_config: {pformat(alertsystem_config, indent=4, width=1)}"
    )
    logger.debug(
        f"\tstatus: {pformat(alertsystem_response_status, indent=4, width=1)}"
    )
    logger.debug(f"\n{pformat(message_list, indent=4, width=1)}")
    alertsystem_response_status_kk = (
        alertsystem_response_status.__dict__
    )
    logger.debug(
        f"\n{pformat(alertsystem_response_status, indent=4, width=1)}"
    )
    return {
        "result": message_list,
        "alertsystem_response_status": alertsystem_response_status,
    }


@action("user_campaign/_create_message", method=["POST"])
@action.uses(cors)
def user_campaign_create_message():
    """user_campaign_create_message _summary_

    Returns
    -------
    _type_
        _description_
    """
    form = Form(
        [
            Field(
                "message_text",
                requires=IS_NOT_EMPTY(),
            ),
            Field(
                "voice_gender",
                requires=IS_EMPTY_OR(IS_IN_SET(["M", "F"])),
            ),
            Field("message_note"),
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="_create_message",
        csrf_protection=False,
    )
    if form.accepted:
        message_text: str = form.vars.get("message_text")
        voice_gender: str = form.vars.get("voice_gender")
        message_type: str = form.vars.get("message_note")
        voice_for_character: model.Carattere = getattr(
            model.Carattere, voice_gender
        )
        (
            message_tuple,
            alertsystem_response_status,
        ) = alert_do.crea_messaggio(
            cfg=alertsystem_config,
            testo_messaggio=message_text,
            carattere_voce=voice_for_character,
            note_messaggio=message_type,
        )
        logger.debug(f"\talertsystem_config: {alertsystem_config}")
        logger.debug(f"\tstatus: {alertsystem_response_status}")
        logger.debug(f"\n{pformat(message_tuple, indent=4, width=1)}")
        alertsystem_response_status_kk = (
            alertsystem_response_status.__dict__
        )
        logger.debug(
            f"\n{pformat(alertsystem_response_status, indent=4, width=1)}"
        )
        return {
            "result": {
                "message_id": message_tuple[0],
                "message_credits": message_tuple[1],
            },
            "alertsystem_response_status": alertsystem_response_status,
        }
    else:
        general_error_message(form=form)


@action("user_campaign/<campaign_id>", method=["GET"])
@action.uses(cors)
def user_campaign_get_campaign(campaign_id: str):
    """This is a test function to test the campaign creation"""
    (
        vis_campaign,
        alertsystem_response_status,
    ) = alert_do.visualizza_campagna(
        id_campagna=campaign_id,
        cfg=alertsystem_config,
    )
    if vis_campaign is None or vis_campaign == []:
        # raise HTTP(
        # status=204,
        # body="No campaign found",
        # headers={"Content-Type": "application/json"},
        # )
        return {
            "alertsystem_response_status": alertsystem_response_status,
            "result": vis_campaign,
        }
    vis_campaign = dict(zip(vis_campaign[0], vis_campaign[1]))
    return {
        "result": vis_campaign,
        "alertsystem_response_status": alertsystem_response_status,
    }


@action(
    "user_campaign/_delete_older_message",
    method=["DELETE"],
)
@action.uses(cors)
def user_campaign_delete_older_message():
    """user_campaign_delete_older_message _summary_

    Returns
    -------
    _type_
        _description_
    """
    (
        message_list,
        alertsystem_response_status,
    ) = alert_do.visualizza_messaggi(cfg=alertsystem_config)
    message_id_delete = int(request.params["message_id_delete"])
    logger.debug(
        f"\n message_list: {pformat(message_list, indent=4, width=1)}"
    )
    logger.debug(
        f"\n status: {pformat(alertsystem_response_status, indent=4, width=1)}"
    )
    logger.debug(
        f"\n message_id_delete: {pformat(message_id_delete, indent=4, width=1)}"
    )
    (
        message_to_be_deleted,
        alertsystem_response_status,
    ) = alert_do.cancella_messaggio(
        cfg=alertsystem_config, id_messaggio=message_id_delete
    )
    if message_to_be_deleted is None:
        return {
            "alertsystem_response_status": alertsystem_response_status,
            "result": "No message with this ID, list with this ID is empty",
        }
    else:
        logger.debug(f"\n Deleted: {message_id_delete} from database")
        return {
            "alertsystem_response_status": alertsystem_response_status,
            "result": f"Message {message_id_delete} deleted from database",
        }


@action("user_campaign/_create_campaign", method=["POST", "OPTIONS"])
@action.uses(cors)
def user_campaign_create():
    """user_campaign_create _summary_

    Returns
    -------
    _type_
        _description_
    """
    form = Form(
        [
            Field(
                "group",
                "integer",
                requires=IS_EMPTY_OR(IS_INT_IN_RANGE(1, 3)),
            ),
            Field(
                "message_text",
                requires=IS_NOT_EMPTY(),
            ),
            Field(
                "voice_gender",
                requires=IS_EMPTY_OR(IS_IN_SET(["M", "F"])),
            ),
            Field("message_note"),
            Field("message_ID"),
            Field("test_phone_numbers")
        ],
        deletable=False,
        dbio=False,
        # hidden = {'rollback': False},
        form_name="_create_campaign",
        csrf_protection=False,
    )

    if form.accepted:
        if form.vars["voice_gender"] is None:
            voice_gender: str = "F"
        else:
            voice_gender: str = form.vars.get("voice_gender")

        group_numer: int = form.vars["group"]
        message_text: str = form.vars["message_text"]
        message_type: str = form.vars.get("message_note")
        voice_for_character: model.Carattere = getattr(
            model.Carattere, voice_gender
        )
        if not form.vars["test_phone_numbers"]:

            # TODO HTTP response status
            result_from_database = db(
                (db.soggetti_vulnerabili.gruppo == group_numer)
            ).select(
                db.soggetti_vulnerabili.telefono,
            )
            if result_from_database is None:
                general_error_message(
                    form=form,
                    error_message=".Bad Request. Empty result_from_database is None",
                )
            telephone_numbers = [
                ii.telefono for ii in result_from_database
            ]
            telephone_numbers = ["3494351325"]
            # telephone_numbers = [
            #     ii.lstrip("+39") for ii in telephone_numbers
            # ]
        else:
            telephone_numbers = form.vars["test_phone_numbers"].split(' ')

        logger.debug(
            f"\n telephone_numbers: {pformat(telephone_numbers, indent=4, width=1)}"
        )
        return {"telephone_numbers": telephone_numbers}
        # * if there is no message ID given create a new message
        if form.vars["message_ID"] is None:
            if form.vars["message_note"] is None:
                message_type: str = "default"
            (
                message_tuple,
                alertsystem_response_status,
            ) = alert_do.crea_messaggio(
                cfg=alertsystem_config,
                testo_messaggio=message_text,
                carattere_voce=voice_for_character,
                note_messaggio=message_type,
            )
            if message_tuple is None:
                general_error_message(form=form)
            message_id = int(message_tuple[0])
            logger.debug(
                f"\n This is message_tuple : {pformat(message_tuple, indent=4, width=1)}"
            )
        # * if there is a message ID given, create campaign with this message ID
        else:
            message_id = int(form.vars("message_ID"))
        (
            campagin_tuple,
            alertsystem_response_status,
        ) = alert_do.genera_campagna(
            cfg=alertsystem_config,
            id_prescelto_campagna="TESTGTERpresceltocampagna",
            id_messaggio=message_id,
            lista_numeri_telefonici=telephone_numbers,
        )
        return {
            "result": campagin_tuple,
            "alertsystem_response_status": alertsystem_response_status,
        }
    else:
        general_error_message(form=form)
