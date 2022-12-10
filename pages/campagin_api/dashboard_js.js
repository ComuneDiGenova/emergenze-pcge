
window.addEventListener('load', () => {
    button_message_list.onclick = () => { _retr_message_list(); };
    button_vis_campaign.onclick = () => { _visualize_campaign(); };
    button_user_list.onclick = () => { _retr_user_list(); };
    alert("Hello World.This page is loaded!");
});
const dashboard_header = document.getElementById("dashboard_header").innerHTML = "JS_bootto_strappo_dashboardo is working!";

const button_message_list = document.getElementById("button_msg_list");
const button_vis_campaign = document.getElementById("button_vis_campaign");
const button_user_list = document.getElementById("button_user_list");
const button_result = document.getElementById("button_result");

// const bstr_results = document.getElementById("bstr_user");

const python_api_url = "/emergenze/user_campaign/";



// Content to bo loaded when the page is loaded:
// window.onload = function () {
// }

// Call the dashboard Get messages
// Call the API, returning promise

// Call the dashboard Get campaigns from to
// Call the API, returning promise

// Call Manuele API to get thje list of users
// https://emergenze-apit.comune.genova.it/emergenze/soggettiVulnerabili/

// Call the dashboard Get Visualise campaign
// Call the API, returning promise

/**Retrieves list of messages in JSON format */
function _retr_message_list(root_div = 'http://localhost:8000/') {
    document.getElementById("button_result").innerHTML = "Retriving message list!";
    const request_options = {
        method: 'GET',
        // body: form_data,
        redirect: 'follow'
    };
    // Retrieve the list of messages from url with GET method 
    // and then put it ien the div in table format
    fetch(root_div + python_api_url + '_retrive_message_list', request_options)
        .then(asyn_response => asyn_response.json())
        .then(async_result => console.log(async_result))
        .catch(error => console.log('error', error));

}

//?
/**Get info about campaign with {ID} in JSON format */
function _visualize_campaign(campaign_id = 'vo6274305ad55304.39423618', root_div = 'http://localhost:8000/') {
    button_result.innerHTML = "Visualizing campaign info!";
    const bstr_campaign = document.getElementById("bstr_campaign");
    const campaign_table = $('#campaign_table_1');
    const request_options = {
        method: 'GET',
        redirect: 'follow'
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
                }
            );
        })
        .catch(error => console.log('error', error));
}
function _retr_campaign_list(root_div = 'http://localhost:8000/') {
    button_result.innerHTML = "Retriving campaign list!";
    const user_defined_id = "vo6274305ad55304.39423618";
    const request_options = {
        method: 'GET',
        redirect: 'follow'
    };
    // Retrieve the list of messages from url with GET method with user defnided ID
    // and then put it in the div in table format
    console.log("fetching data from: " + root_div + python_api_url + user_defined_id)
    fetch(root_div + python_api_url + user_defined_id, request_options)
        .then(response => response.json())
        .then(result => console.log(result))
        .catch(error => console.log('error', error));
    // Promise.all
}

/** Get users list info in JSON format */
function _retr_user_list(root_div = 'https://emergenze-apit.comune.genova.it/') {
    button_result.innerHTML = "Retriving users list!";
    const bstr_results = document.getElementById("bstr_user");
    const user_table = $('#user_table_1');
    const request_options = {
        method: 'GET',
        redirect: 'follow'
    };
    // Retrieve the list of messages from url with GET method 
    // and then put it in the div in table format
    // "result": [
    //     {
    //       "cognome": "Rossetti",
    //       "gruppo": "3",
    //       "id": "pc-145",
    //       "indirizzo": "VIA GIUSEPPE CASAREGIS",
    //       "nome": "Roberta",
    //       "numero_civico": "13 / 1",
    //       "sorgente": "PC",
    //       "telefono": "+390103629638"
    //     },
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
            // Add option for export data
            user_table.bootstrapTable(
                {
                    data: user_list_dict,
                    pagination: true,
                    search: true,
                    showColumns: true,
                    showExport: true,
                    showRefresh: true,
                    showToggle: true,
                    exportTypes: ['csv', 'txt', 'sql', 'doc', 'excel', 'xlsx', 'pdf'],
                    exportDataType: "all",
                }
            )

        })
        .catch(error => {
            console.error('error', error);
            console.log("Error in retriving user list! Check VPN connection!");
        });
}