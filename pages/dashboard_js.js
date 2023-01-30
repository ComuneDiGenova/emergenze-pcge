// import { format_dashboard_date } from "./dashboard_api.js";

// Add in dayjs plugin, because writng code is for noobs
import { format_date, convert_to_date } from "./dashboard_api.js";
// Cashe the DOM elements
const $dashboard_text = $("#dashboard_text");
const $button_message_list = $("#button_msg_list");
const $button_vis_campaign = $("#button_vis_campaign");
// const $button_vis_multi_campaign = $("#button_vis");
const $button_get_camapaign = $("#button_campaign_from_to");
const $button_create_campaign = $("#button_create_campaign");
const $button_create_message = $("#button_create_message");
const $button_del = $("#button_delete");
const $header_cmp_list = $("#camp_list_header");
const $ui_date_start = $("#ui_date_start");
const $ui_date_end = $("#ui_date_end");
const $message_table = $("#msg_table");

//* API URL
const python_api_url = `${config.BASE_URL}user_campaign/`;
const genova_api_url = `${config.DB_URL}`;

// default values for the date picker
let date_start_picked = "2020-06-01";
let date_end_picked = "2022-01-31";
let date_picked = {
  date_start: date_start_picked,
  date_end: date_end_picked,
};
// return $.fn.datepicker to previously assigned value
var datepicker = $.fn.datepicker.noConflict();
// give $().bootstrapDP the bootstrap-datepicker functionality
$.fn.bootstrapDP = datepicker;

// Loads the userlist when document is loaded
$(retr_user_list(genova_api_url));
// Init date picker with JQuery

$(() => {
  $ui_date_end.datepicker({
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
    showAnim: "fadeIn",
  });
});
$(() => {
  $ui_date_start.datepicker({
    dateFormat: "yy-mm-dd",
    defaultDate: new Date(2020, 1, 1),
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
    showAnim: "fadeIn",
  });
});

// Registering listeners for the date pickers on change event
$ui_date_start.on("change", () => {
  // date_start = "2022-01-10 10:10"
  // Convert the date to the format of the API
  date_start_picked = $ui_date_start.val();
  console.log("Start date_picked_>", date_start_picked);
  date_picked.date_start = date_start_picked;
});
$ui_date_end.on("change", () => {
  // date_start = "2022-01-10 10:10"
  // Convert the date to the format of the API
  date_end_picked = $ui_date_end.val();
  console.log("End date_picked_>", date_end_picked);
  date_picked.date_end = date_end_picked;
});

// TODO refactor to pure funtion style

//* Main API calls
// ? Create campaign API call takes in 5 parameters. There is default behaviour on backend if something is not specified.
// ? Create campaign on backend will take message_ID if it is specified, otherwise it will take message_text/note and create a new message
// ? It does not chec if the message already exists in the database when given message_ID
$button_create_campaign.on("click", async () => {
  const $msg_note = $("#msg_note").val();
  const $msg_text = $("#msg_content").val();
  const group_number = document.querySelector(
    "input[name='group_option']:checked",
  ).value;
  const voice_picked = document.querySelector(
    "input[name='voice_options']:checked",
  ).value;
  const $test_numbers = $("#test_phone_numbers").val();
  // pyApi will create a new message if the message_id if no ID is given
  // retr_message call does not return
  let form_data = new FormData();
  form_data.append("message_text", $msg_text);
  form_data.append("message_note", $msg_note);
  form_data.append("group", group_number);
  form_data.append("voice_gender", voice_picked);
  form_data.append("test_phone_numbers", $test_numbers);
  await create_campaign(python_api_url, form_data);
  alert(`Campaign: Sent!`);
});

// JQuery registering listeners for the buttons
$button_message_list.on("click", async () => {
  await retr_message_list(python_api_url);
  await listen_delete();
});
$button_vis_campaign.on("click", () => {
  $campaign_id_to_visualize = $("#camp_id").val();
  if ($campaign_id_to_visualize == "") {
    alert("Please insert a campaign id first");
    return;
  }
  vis_campaign(python_api_url, $campaign_id_to_visualize);
});

$button_get_camapaign.on("click", () => {
  const date_picked = {
    date_start: $ui_date_start.val(),
    date_end: $ui_date_end.val(),
  };
  console.log("Date picked", date_picked);
  get_campaign_from_to(python_api_url, date_picked);
});
$button_create_message.on("click", () => {
  voice_picked = document.querySelector(
    "input[name='voice_options']:checked",
  ).value;
  const msg_dict = {
    message: $("#msg_content").val(),
    voice_gender: voice_picked,
    note: $("#msg_note").val(),
  };
  console.log("Object for message:", msg_dict);
  create_message(python_api_url, msg_dict);
});

/** Returns the list of messages from the database and puts it in the BS table*/
async function retr_message(root_url, message_id) {
  const request_options = {
    method: "GET",
    redirect: "follow",
  };
  // Retrieve the list of messages from url with GET method
  // and then put it ien the div in table format
  if (message_id == "") {
    return console.log("message_id is empty");
  }
  let message_dict = null;
  await fetch(`${root_url}_retrive_message_list`, request_options)
    .then((asyn_response) => asyn_response.json())
    .then((async_result) => {
      const message_list = async_result.result;
      return Object.entries(message_list).forEach(([key, value]) => {
        // message_list.forEach((element) => {
        if (key == message_id) {
          message_dict = {};
          message_dict.message = value;
        }
      });
    })
    .catch((error) => console.log("error", error));
  if (message_dict != null) {
    return message_dict;
  }
  console.log(
    `Element with id ${message_id} was not found. Creating new message!`,
  );
  return null;
}

/** Deletes the message from the database*/
function delete_message(root_url, message_id = "1") {
  const form_data = new FormData();
  form_data.append("message_id_delete", message_id);
  const request_options = {
    method: "DELETE",
    body: form_data,
    redirect: "follow",
  };

  fetch(`${root_url}_delete_older_message`, request_options)
    .then((response) => response.json())
    .then((result) => console.log(result))
    .catch((error) => {
      alert(
        `Error while deleting the message from database: ${error}`,
      );
      console.log("error", error);
    });
}

/** Calls the backend to create a new campaign*/
async function create_campaign(
  root_url,
  form_data_to_send = new FormData(),
) {
  const requestOptions = {
    method: "POST",
    body: form_data_to_send,
  };
  console.log(
    "This the fetch address",
    `${root_url}_create_campaign`,
  );
  fetch(`${root_url}_create_campaign`, requestOptions)
    .then((response) => response.json())
    .then((result) => {
      console.log(result);
    })
    .catch((error) => console.log("error", error));
}

/** Creates new message in the database*/
//! This is vunerable to SQL injection
function create_message(
  root_url,
  dict_of_options = {
    message: "Sono romano, grana padano!",
    voice_gender: "M",
    note: "Gter_test_JS",
  },
) {
  $dashboard_text.text("Creating message!");
  let formdata = new FormData();
  if (dict_of_options.message === "") {
    dict_of_options.message = "EMPTY MESSAGE";
  }
  formdata.append("message_text", dict_of_options.message);
  formdata.append("voice_gender", dict_of_options.voice_gender);
  formdata.append("message_note", dict_of_options.note);
  const request_options = {
    method: "POST",
    body: formdata,
    // redirect: 'follow',
  };
  fetch(`${root_url}_create_message`, request_options)
    .then((asyn_response) => asyn_response.json())
    .then((async_result) => {
      console.log("async_result from alertpy", async_result);
      document.getElementById("dashboard_text").innerHTML =
        "Message created!";
      // It calls the function to retrieve the list of messages from the database
      retr_message_list(python_api_url);
    })
    .catch((error) => console.log("error", error));
}

/** This function operates on bootstrap table delete button for deletions of rows*/
async function listen_delete() {
  $(() => {
    $button_del.click(() => {
      console.log("Button_del clicked");
      let ids = getIdSelections($message_table);
      $message_table.bootstrapTable("remove", {
        field: "message_id",
        values: ids,
      });
      // Each element of the bootstrap table picked run this function
      ids.forEach((element) => {
        delete_message(python_api_url, element);
        console.log("Deleted id:", element);
      });
      // $button_del.prop("disabled", true);
    });
  });
}

/** Operator format for the bootstrap table message list. Adds icons and <a> tags to buttons*/
function op_formttr_msg_list(value, row, index) {
  const remove_msg_class = "remove_msg";
  const create_campaign_class = "create_campaign";
  return [
    `<a class="${create_campaign_class}" href="javascript:void(0)" title="Create campaign from this message">`,
    `<i class="fa fa-bullhorn"></i>`,
    "</a>",
    ,
    `<a class="${remove_msg_class}" href="javascript:void(0)" title="Remove this message from database">`,
    `<i class="fa fa-trash"></i>`,
    "</a>",
  ].join("");
}

/** Operator format for the bootstrap table campaign list. Adds icons and <a> tags to buttons */
function op_formttr_cmp_list(value, row, index) {
  const visualize_campaign_class = "vis_camp_event";
  return [
    `<a class="${visualize_campaign_class}" href="javascript:void(0)" title="Visualize">`,
    `<i class="fa fa-eye"></i>`,
    "</a>",
  ].join("");
}

/** Creates message from message list entry */
async function create_campaign_from_table_msg(e, value, row, index) {
  const group_number = document.querySelector(
    "input[name='group_option']:checked",
  ).value;
  const voice_picked = document.querySelector(
    "input[name='voice_options']:checked",
  ).value;
  form_data = new FormData();
  form_data.append("message_ID", row.message_id);
  await create_campaign(python_api_url, form_data);
  alert(
    "You have created a campaign from message, row: " +
      JSON.stringify(row),
  );
}

/**  Operates <a> tags events in bootstrap tables*/
window.operateEvents = {
  // This is just an example of how to use custom buttons
  "click .create_campaign": create_campaign_from_table_msg,
  // * this is a trash button for deleting rows in message table
  "click .remove_msg": function (e, value, row, index) {
    $message_table.bootstrapTable("remove", {
      field: "message_id",
      values: [row.message_id],
    });
    delete_message(python_api_url, row.message_id);
    alert("You have removed row: " + JSON.stringify(row.message_id));
  },
  "click .vis_camp_event": function (e, value, row, index) {
    vis_campaign(python_api_url, row.camp_id);
    // alert("You have visualized row: " + JSON.stringify(row.camp_id));
  },
};

// todo: make a generic function for creating bs tables
function create_tables(table_name, dict_of_columns) {}

/**Retrieves list of messages in JSON format */
async function retr_message_list(root_url) {
  $dashboard_text.text("Retriving message list!");
  const $bstr_message = $("#bstr_message");
  const request_options = {
    method: "GET",
    redirect: "follow",
  };
  // Retrieve the list of messages from url with GET method
  // and then put it ien the div in table format
  fetch(`${root_url}_retrive_message_list`, request_options)
    .then((asyn_response) => asyn_response.json())
    .then((async_result) => {
      const message_list = async_result.result;
      const message_list_dict = [];
      Object.entries(message_list).forEach(([key, value]) => {
        message_list_dict.push({
          message_date: convert_to_date(
            format_date(
              value.data_creazione,
              "DD-MM-YYYY HH:mm:ss",
              "YYYY/MM/DD HH:mm:ss",
            ),
          ),
          message_dimension: value.dimensione,
          message_duration: value.durata,
          message_id: value.id_messaggio,
          message_note: value.note,
        });
      });
      $bstr_message.show();
      $message_table.bootstrapTable("destroy").bootstrapTable({
        columns: [
          {
            field: "state",
            checkbox: true,
            // Not used because the header columns are 1 not 2
            // rowspan: 2,
            align: "center",
            valign: "middle",
          },
          {
            field: "message_id",
            title: "Item ID",
            align: "center",
            valign: "middle",
          },
          {
            field: "message_date",
            title: "Message Date",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "message_note",
            title: "Message note",
            align: "center",
          },
          {
            field: "message_duration",
            title: "Message duration",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "message_dimension",
            title: "Message dimension",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "operate",
            title: "Actions",
            align: "center",
            clickToSelect: false,
            events: operateEvents,
            formatter: op_formttr_msg_list,
          },
        ],
        data: message_list_dict,
        uniqueId: "message_id",
        striped: true,
        sortable: true,
        sortOrder: "desc",
        sortName: "message_date",
        pageNumber: 1,
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        searchHighlight: true,
        pagination: true,
        search: true,
        showToggle: true,
        showExport: true,
        exportDataType: "all",
        exportTypes: [
          "csv",
          "txt",
          "sql",
          "doc",
          "excel",
          "xlsx",
          "pdf",
        ],
      });
    })
    .catch((error) => console.log("error", error));
  $message_table.on(
    "check.bs.table uncheck.bs.table " +
      "check-all.bs.table uncheck-all.bs.table",
    () => {
      $button_del.prop(
        "disabled",
        !$message_table.bootstrapTable("getSelections").length,
      );

      // save your data, here just save the current page
      // push or splice the selections if you want to save all data selections
    },
  );
  $button_del.prop("disabled", true);
  // Applies disabled to button
}

function getIdSelections($table_name) {
  return $.map(
    $table_name.bootstrapTable("getSelections"),
    function (row) {
      return row.message_id;
    },
  );
}
/**Get info about campaign with {ID} in JSON format */
function vis_campaign(
  root_url,
  campaign_id = "vo6274305ad55304.39423618",
) {
  $dashboard_text.text(`Visualizing ${campaign_id} campaign info!`);
  const $bstr_campaign = $("#bstr_camp_vis");
  const $campaign_table = $("#camp_table");
  const request_options = {
    method: "GET",
    redirect: "follow",
  };
  // Retrieve the list of messages from url with GET method with user defnided ID
  // and then put it in the div in table format
  fetch(`${root_url + campaign_id}`, request_options)
    .then((async_response) => async_response.json())
    .then((async_result) => {
      $bstr_campaign.show();
      const campaign_json = async_result.result;
      const campaign_list_dict = [
        {
          campaign_id: campaign_json.IdCampagna,
          campaign_telephone: campaign_json.NumeroTelefonico,
          campaign_note: campaign_json.IdentificativoCampagna,
          campaign_type: campaign_json.Tipo,
          campaign_duration: campaign_json.Durata,
          campaign_start_date: campaign_json.DataCampagna,
          campaign_end_date: campaign_json.DataChiamata,
          campaign_status: campaign_json.Esito,
          campaign_identifier: campaign_json.Identificativo,
        },
      ];
      $campaign_table.bootstrapTable("destroy").bootstrapTable({
        columns: [
          {
            field: "state",
            checkbox: true,
            // Not used because the header columns are 1 not 2
            // rowspan: 2,
            align: "center",
            valign: "middle",
          },
          {
            field: "campaign_id",
            title: "Campaign ID",
            align: "center",
            valign: "middle",
          },
          {
            field: "campaign_telephone",
            title: "Campaign telephone",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_note",
            title: "Campaign note",
            align: "center",
          },
          {
            field: "campaign_type",
            title: "Campaign type",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_duration",
            title: "Campaign duration",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_start_date",
            title: "Campaign start date",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_end_date",
            title: "Campaign end date",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_status",
            title: "Campaign status",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_identifier",
            title: "Campaign identifier",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "campaign_contacts_num",
            title: "Campaign contacts amount",
            align: "center",
            valign: "middle",
            sortable: true,
          },
        ],
        data: campaign_list_dict,
        uniqueId: "campaign_id",
        striped: true,
        sortable: true,
        pageNumber: 1,
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        searchHighlight: true,
        pagination: true,
        search: true,
        showToggle: true,
        showExport: true,
        exportDataType: "all",
        exportTypes: [
          "csv",
          "txt",
          "sql",
          "doc",
          "excel",
          "xlsx",
          "pdf",
        ],
      });
    })
    .catch((error) => console.log("error", error));
}

/** Get users list info in JSON format */
function retr_user_list(root_url) {
  $dashboard_text.text("Retriving users list!");
  const $bstr_div = $("#bstr_user");
  const $user_table = $("#user_table_1");
  const request_options = {
    method: "GET",
    redirect: "follow",
  };
  fetch(`${root_url}soggettiVulnerabili`, request_options)
    .then((async_response) => async_response.json())
    .then((async_anwser) => {
      $bstr_div.show();
      const user_list_json = async_anwser.result;
      const user_list_dict = user_list_json.map((item) => {
        return {
          user_id: item.id,
          user_name: item.nome,
          user_surname: item.cognome,
          user_group: item.gruppo,
        };
      });
      $user_table.bootstrapTable("destroy").bootstrapTable({
        columns: [
          {
            field: "user_id",
            title: "User ID",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "user_name",
            title: "User name",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "user_surname",
            title: "User surname",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "user_group",
            title: "User group",
            align: "center",
            valign: "middle",
            sortable: true,
          },
        ],
        data: user_list_dict,
        uniqueId: "user_id",
        striped: true,
        sortable: true,
        pageNumber: 1,
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        searchHighlight: true,
        pagination: true,
        search: true,
        showToggle: true,
        showExport: true,
        exportDataType: "all",
        exportTypes: [
          "csv",
          "txt",
          "sql",
          "doc",
          "excel",
          "xlsx",
          "pdf",
        ],
      });
    })
    .catch((error) => {
      console.error("error", error);
      console.log(
        "Error in retriving user list! Check VPN connection!",
      );
    });
}

/** Retrieves campaign in given timeframe. Creates bootstrap table and fills the data */
function get_campaign_from_to(
  root_url = "http://localhost:8000/",
  date_dict = {
    date_start: "2020-06-01",
    date_end: "2022-01-31",
  },
) {
  const $bstr_div_camp = $("#bstr_camp");
  const $camp_table = $("#camp_table_time");
  const date_start = date_dict.date_start + " 12:00";
  const date_end = date_dict.date_end + " 12:00";
  $header_cmp_list.text(
    `Campaign dashboard from ${date_start} to ${date_end}`,
  );
  let form_data = new FormData();
  form_data.append("date_start", date_start);
  form_data.append("date_end", date_end);
  const requestOptions = {
    method: "POST",
    body: form_data,
    redirect: "follow",
  };

  fetch(`${root_url}_get_campaign_from_to`, requestOptions)
    .then((async_response) => async_response.json())
    .then((async_anwser) => {
      $bstr_div_camp.show();
      const camp_list_json = async_anwser.result;
      const camp_list_dict = [];
      Object.entries(camp_list_json).forEach(([key, value]) => {
        camp_list_dict.push({
          camp_id: value.id_campagna,
          camp_type: value.tipo,
          camp_date: value.data_campagna,
          camp_user: value.utente,
          camp_contact: value.contatti,
          camp_identifier: value.identificativo,
        });
      });
      console.log(camp_list_dict);
      $camp_table.bootstrapTable("destroy").bootstrapTable({
        columns: [
          {
            field: "state",
            checkbox: true,
            // Not used because the header columns are 1 not 2
            // rowspan: 2,
            align: "center",
            valign: "middle",
          },
          {
            field: "camp_id",
            title: "Campaign ID",
            align: "center",
            valign: "middle",
          },
          {
            field: "camp_type",
            title: "Type",
            align: "center",
            valign: "middle",
          },
          {
            field: "camp_date",
            title: "Campaign Date",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "camp_identifier",
            title: "Campaign note",
            align: "center",
          },
          {
            field: "camp_user",
            title: "Campaign user",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "camp_contact",
            title: "Campaign contact",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "operate",
            title: "Actions",
            align: "center",
            clickToSelect: false,
            events: operateEvents,
            formatter: op_formttr_cmp_list,
          },
        ],
        data: camp_list_dict,
        uniqueId: "message_id",
        striped: true,
        sortable: true,
        sortOrder: "desc",
        sortName: "camp_date",
        pageNumber: 1,
        pageSize: 10,
        pageList: [10, 25, 50, 100],
        searchHighlight: true,
        pagination: true,
        search: true,
        showToggle: true,
        showExport: true,
        exportDataType: "all",
        exportTypes: [
          "csv",
          "txt",
          "sql",
          "doc",
          "excel",
          "xlsx",
          "pdf",
        ],
      });
    })
    .catch((error) => console.log("error", error));
}
