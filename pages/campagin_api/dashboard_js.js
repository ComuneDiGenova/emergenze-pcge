// import express from 'express';
console.log("Dashboard JS is working!");
document.getElementById("dashboard_header").innerHTML = "JS_bootto_strappo_dashboardo is working!";

let python_api_url = "/emergenze/user_campaign/";


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

//?
/**Retrieves list of messages in JSON format */
function _retr_message_list(root_div = 'http://localhost:8000/') {
    document.getElementById("button_result").innerHTML = "Retriving message list!";
    // Call the API, returning promise
    //async function
    // var form_data = new FormData();
    var request_options = {
        method: 'GET',
        // body: form_data,
        redirect: 'follow'
    };
    // Retrieve the list of messages from url with GET method 
    // and then put it ien the div in table format
    message_list_promise = fetch(root_div + python_api_url + '_retrive_message_list', request_options)
        .then(response => response.json())
        .then(result => console.log(result))
        .catch(error => console.log('error', error));

}

//?
/**Get info about campaign with {ID} in JSON format */
function _retr_campaign_list(root_div = 'http://localhost:8000/') {
    // var user_defined_id = document.getElementById("user_defined_id").value;
    var user_defined_id = "vo5f771ca26f0793.07232404";
    document.getElementById("button_result").innerHTML = "Retriving campaign list!";
    var request_options = {
        method: 'GET',
        redirect: 'follow'
    };
    // Retrieve the list of messages from url with GET method with user defnided ID
    // and then put it in the div in table format
    user_list = fetch(root_div + python_api_url + user_defined_id, request_options)
        .then(response => { response.json(); })
        .then(result => console.log(result))
        .catch(error => console.log('error', error));
    // Promise.all
}

/** Get users list info in JSON format */
function _retr_user_list(root_div = 'https://emergenze-apit.comune.genova.it/') {
    document.getElementById("button_result").innerHTML = "Retriving users list!";

    var user_table = $('#user_table_1');
    var request_options = {
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
    user_list = fetch(root_div + "emergenze/soggettiVulnerabili/", request_options)
        .then((response) => response.json()
        )
        .then(anwser => {
            var user_list_json = anwser.result;
            var user_list_dict = [{
                user_id: "group_string",
                user_name: "id_string",
                user_surname: "name_string",
                user_group: "surname_string"

            }];
            var user_list_array = user_list_json.map((item) => {
                return {
                    user_id: item.id,
                    user_name: item.nome,
                    user_surname: item.cognome,
                    user_group: item.gruppo,
                }
            });
            console.log(user_table)
            // console.log(user_list_json);
            // user_list_dict["user_id"] = user_list_json[key]["id"];
            // user_list_dict["user_name"] = user_list_json[key]["nome"];
            // user_list_dict["user_surname"] = user_list_json[key]["cognome"];
            // user_list_dict["user_group"] = user_list_json[key]["gruppo"];
            user_table.bootstrapTable(
                {
                    pagination: true,
                    data: user_list_array
                }
            )
        })
        .catch(error => console.log('error', error));
}

// function change_keys(dict) {
//     var new_dict = {};
//     for (var key in dict) {
//         new_dict[key.replace(" ", "_")] = dict[key];
//     }
//     return new_dict;
// }