
window.addEventListener("load", () => {
    alert("Hello World.This page is loaded!");
});
$(document).ready(() => {

    //TODO Make a call for users list when the page is loaded
    //TODO Add button for deletion of messages in message table

// TODO Fetching data stays iun browser cache, and for the same data it is downloaded again and again
// TODO: Add a button to clear the cache
// TODO: Make this a class of buttons, so that the same function can be called with different parameters
// TODO Date from JQueryUI handles only dates, not time. So the time is always 00:00:00
// ! Bootstrap table accepts only Arrays as input, not JSON objects

const dashboard_header = document.getElementById("dashboard_header").innerHTML = "JS_bootto_strappo_dashboardo is working!";
const button_message_list = document.getElementById("button_msg_list");
const button_vis_campaign = document.getElementById("button_vis_campaign");
const button_user_list = document.getElementById("button_user_list");
const button_result = document.getElementById("button_result");
const button_get_camapaign = document.getElementById("button_campaign_from_to");
const header_cmp_list = document.getElementById("camp_list_header");
const ui_date_start = document.getElementById("ui_date_start");
const ui_date_end = document.getElementById("ui_date_end");
const bstr_results = document.getElementById("bstr_user");

const python_api_url = "/emergenze/user_campaign/";

let date_start_picked = "2021-01-01";
let date_end_picked = "2021-01-31";
let date_picked = { date_start: date_start_picked, date_end: date_end_picked };

// Register the date picker with JQuery
$(() => {
    $("#ui_date_end").datepicker({
        dateFormat: "yy-mm-dd",
        defaultDate: new Date(),
        maxDate: new Date(),
        minDate: new Date(2020, 1, 1),
        changeYear: true,
        // changeMonth: true,
        todayHighlight: true,
        autoSize: true,
        autoclose: true,
        clearBtn: true,
        language: "en",
        orientation: "bottom auto",
    });
});
$(() => {
    $("#ui_date_start").datepicker({
        dateFormat: "yy-mm-dd",
        defaultDate: new Date(),
        maxDate: new Date(),
        minDate: new Date(2020, 1, 1),
        changeYear: true,
        // changeMonth: true,
        todayHighlight: true,
        autoSize: true,
        autoclose: true,
        clearBtn: true,
        language: "en",
        orientation: "bottom auto",
    });
});

// Registering listeners for the date pickers on change event
$("#ui_date_start").on("change", () => { 
    // date_start = "2022-01-10 10:10"
    // Convert the date to the format of the API
    date_start_picked = $("#ui_date_start").val();
    console.log("Start date_picked_>", date_start_picked);
    date_picked.date_start = date_start_picked
});
$("#ui_date_end").on("change", () => { 
    // date_start = "2022-01-10 10:10"
    // Convert the date to the format of the API
    date_end_picked = $("#ui_date_end").val();
    console.log("End date_picked_>", date_end_picked);
    date_picked.date_end = date_end_picked
});

button_message_list.onclick = () => { _retr_message_list(); };
button_vis_campaign.onclick = () => { _vis_campaign(); };
button_user_list.onclick = () => { _retr_user_list(); };
button_get_camapaign.onclick = () => { _get_campaign_from_to("http://localhost:8000/", date_picked); };

async function fetch_generic(url, bootstrap_id, req_opt, table_id) {
    const bstr_container = document.getElementById(bootstrap_id);
    const message_table = $("#" + table_id);
    bstr_container.style.display = "block";
    try {
        const response = await fetch(url, req_opt);
        const result_1 = await response.json();
        message_table.bootstrapTable(
            {
                data: message_list_dict,
                pagination: true,
                search: true,
                // showColumns: true,
                // showRefresh: true,
                showToggle: true,
                // showExport: true,
                exportDataType: "all",
                exportTypes: ["csv", "txt", "sql", "doc", "excel", "xlsx", "pdf"],
                exportOptions: {
                    fileName: "export",
                    jspdf: {
                        orientation: "l",
                        format: "a4",
                        margins: { left: 20, right: 10, top: 10, bottom: 10 },
                        autotable: {
                            styles: { fillColor: "inherit", textColor: "inherit" },
                            tableWidth: "auto"
                        }

                    }
                }
            }
        );
        return result_1;
    } catch (error) {
        return console.log("error", error);
    }
}

function _create_message(root_div = "http://localhost:8000/", dict_of_options = { message : "Sono romano, grana padano!", voice_gender : "M"}) {
    document.getElementById("button_result").innerHTML = "Creating message!";
    const bstr_message = document.getElementById("bstr_message");
    const message_table = $("#msg_table");
    const form_data = new FormData();
    form_data.append("message", bstr_message.value);
    const request_options = {
        method: "POST",
        body: form_data,
        redirect: "follow"
    };

    fetch(root_div + python_api_url + "_create_message", request_options)
        .then(asyn_response => asyn_response.json())
        .then(async_result => {
            console.log("async_result", async_result);
            document.getElementById("button_result").innerHTML = "Message created!";
            bstr_message.value = "";
            _retr_message_list();
        })
        .catch(error => console.log("error", error));
}




    // Retrieve the list of messages from url with GET method
/**Retrieves list of messages in JSON format */
function _retr_message_list(root_div = "http://localhost:8000/") {
    document.getElementById("button_result").innerHTML = "Retriving message list!";
    const bstr_message = document.getElementById("bstr_message");
    const message_table = $("#msg_table");
    const request_options = {
        method: "GET",
        // body: form_data,
        redirect: "follow"
    };

    // Retrieve the list of messages from url with GET method 
    // and then put it ien the div in table format
    fetch(root_div + python_api_url + "_retrive_message_list", request_options)
        .then(asyn_response => asyn_response.json())
        .then(async_result => {
            const message_list = async_result.result;
            const message_list_dict = [];
            // "data_creazione": "01-12-2021 12:03:09",
            // "dimensione": 181998,
            // "durata": "0:11",
            // "id_messaggio": 2551638356544,
            // "note": "prova prima sessione corso"
            for (let i in message_list) {
                message_list_dict.push({
                    message_date: message_list[i].data_creazione,
                    message_dimension: message_list[i].dimensione,
                    message_duration: message_list[i].durata,
                    message_id: message_list[i].id_messaggio,
                    message_note: message_list[i].note,
                });
            }
            const message_table = $("#msg_table");
            bstr_message.style.display = "block";
            message_table.bootstrapTable(
                {
                    data: message_list_dict,
                    pagination: true,
                    search: true,
                    // showColumns: true,
                    // showRefresh: true,
                    showToggle: true,
                    showExport: true,
                    exportDataType: "all",
                    exportTypes: ["csv", "txt", "sql", "doc", "excel", "xlsx", "pdf"],
                }
            );
        })
        .catch(error => console.log("error", error));

}

//?
/**Get info about campaign with {ID} in JSON format */
function _vis_campaign(campaign_id = "vo6274305ad55304.39423618", root_div = "http://localhost:8000/") {
    button_result.innerHTML = "Visualizing campaign info!";
    const bstr_campaign = document.getElementById("bstr_camp_vis");
    const campaign_table = $("#camp_table");
    const request_options = {
        method: "GET",
        redirect: "follow"
    };
    // Retrieve the list of messages from url with GET method with user defnided ID
    // and then put it in the div in table format
    fetch(root_div + python_api_url + campaign_id, request_options)
        .then(async_response => async_response.json())
        .then(async_result => {
            bstr_campaign.style.display = "block";
            const campaign_json = async_result.result;
            const campaign_list_dict = [{
                campaign_id: campaign_json.IdCampagna,
                campaign_telephone: campaign_json.NumeroTelefonico,
                campaign_note: campaign_json.IdentificativoCampagna,
                campaign_type: campaign_json.Tipo,
                campaign_duration: campaign_json.Durata,
                campaign_start_date: campaign_json.DataCampagna,
                campaign_end_date: campaign_json.DataChiamata,
                campaign_status: campaign_json.Esito,
                campaign_identifier: campaign_json.Identificativo,
            }];
            campaign_table.bootstrapTable(
                {
                    data: campaign_list_dict,
                    showToggle: true,
                }
            );
        })
        .catch(error => console.log("error", error));
}

/** Get users list info in JSON format */
function _retr_user_list(root_div = "https://emergenze-apit.comune.genova.it/") {
    button_result.innerHTML = "Retriving users list!";
    const bstr_results = document.getElementById("bstr_user");
    const user_table = $("#user_table_1");
    const request_options = {
        method: "GET",
        redirect: "follow"
    };
    fetch(root_div + "emergenze/soggettiVulnerabili/", request_options)
        .then((async_response) => async_response.json()
        )
        .then(async_anwser => {
            bstr_results.style.display = "block";
            const user_list_json = async_anwser.result;
            const user_list_dict = user_list_json.map((item) => {
                return {
                    user_id: item.id,
                    user_name: item.nome,
                    user_surname: item.cognome,
                    user_group: item.gruppo,
                }
            });
            user_table.bootstrapTable(
                {
                    data: user_list_dict,
                    pagination: true,
                    search: true,
                    // showColumns: true,
                    // showExport: true,
                    // showRefresh: true,
                    showToggle: true,
                    exportTypes: ["csv", "txt", "sql", "doc", "excel", "xlsx", "pdf"],
                    exportDataType: "all",
                }
            )
        })
        .catch(error => {
            console.error("error", error);
            console.log("Error in retriving user list! Check VPN connection!");
        });
}

function _get_campaign_from_to(root_div = "http://localhost:8000/", date_dict = { date_start: "2021-01-01", date_end: "2021-01-31" }) {
    const bstr_results = document.getElementById("bstr_camp");
    const camp_table = $("#camp_table_time");
    const date_start = date_dict.date_start + " 12:00";
    const date_end = date_dict.date_end + " 12:00";
    console.log("date_start form date_dict: " + date_start);
    console.log("date_end form date_dict: " + date_end);
    header_cmp_list.innerHTML = "Campaign list from " + date_start + " to " + date_end;
    let form_data = new FormData();
    form_data.append("date_start", date_start);
    form_data.append("date_end", date_end);
    const requestOptions = {
        method: "POST",
        body: form_data,
        redirect: "follow"
    };

    fetch(root_div + "emergenze/user_campaign/_get_campaign_from_to", requestOptions)
        .then(async_response => async_response.json())
        .then(async_anwser => {
            bstr_results.style.display = "block";
            const camp_list_json = async_anwser.result;
            console.log(camp_list_json);
            let camp_list_dict = [];
            for (let i in camp_list_json) {
                camp_list_dict.push({
                    camp_id: camp_list_json[i].id_campagna,
                    camp_type: camp_list_json[i].tipo,
                    camp_date: camp_list_json[i].data_campagna,
                    camp_user: camp_list_json[i].utente,
                    camp_contact: camp_list_json[i].contatti,
                    camp_identifier: camp_list_json[i].identificativo,
                });
            }
            console.log(camp_list_dict);
            camp_table.bootstrapTable({
                data: camp_list_dict,
                pagination: true,
                search: true,
                // showColumns: true,
                // showExport: true,
                // showRefresh: true,
                showToggle: true,
                exportTypes: ["csv", "txt", "sql", "doc", "excel", "xlsx", "pdf"],
                exportDataType: "all",
            });
        })
        .catch(error => console.log("error", error));
}
});