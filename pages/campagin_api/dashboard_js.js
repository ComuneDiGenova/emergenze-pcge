// import express from 'express';
console.log("Dashboard JS is working!");

document.getElementById("empty_paragraph").innerHTML = "JS_boostrap_dashboard is working!";

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
    // Call the API, returning promise
    //async function
    // var form_data = new FormData();
    var request_options = {
        method: 'GET',
        // body: form_data,
        redirect: 'follow'
    };
    // Retrieve the list of messages from url with GET method 
    // and then put it in the div in table format
    fetch(root_div + 'emergenze/user_campaign/_retrive_message_list', request_options)
        .then(response => response.json())
        .then(result => console.log(result))
        .catch(error => console.log('error', error));
}

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
    fetch(root_div + 'emergenze/user_campaign/' + user_defined_id, request_options)
        .then(response => response.json())
        .then(result => console.log(result))
        .catch(error => console.log('error', error));
}

// main();
