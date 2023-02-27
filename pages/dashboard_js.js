// import { format_dashboard_date } from "./dashboard_api.js";
// Add in dayjs plugin, because writng code is for noobs
import {
  format_date,
  convert_to_date,
  get_message_mp3_url,
} from "./dashboard_api.js";
// Cashe the DOM elements
const $button_message_list = $("#button_msg_list");
const $button_vis_campaign = $("#button_vis_campaign");
// const $button_vis_multi_campaign = $("#button_vis");
const $button_get_camapaign = $("#button_campaign_from_to");
const $button_create_campaign = $("#button_create_campaign");
const $button_create_message = $("#button_create_message");
const $button_test_message = $("#button_test_message");
const $button_download_mp3 = $("#button_download_mp3");
const $button_del = $("#button_delete");
const $header_cmp_list = $("#camp_list_header");
const $ui_date_start = $("#ui_date_start");
const $ui_date_end = $("#ui_date_end");
const $message_table = $("#msg_table");
//* API URL
const python_api_url = `${config.BASE_URL}user_campaign/`;
const genova_api_url = `${config.DB_URL}`;

// default values for the date picker
const default_end = new Date();
const default_start = new Date();

default_start.setDate(1);
default_start.setMonth(default_start.getMonth() - 1);

let date_start_picked = default_start;
let date_end_picked = default_end;
let date_picked = {
  date_start: date_start_picked,
  date_end: date_end_picked,
};

// Loads the userlist when document is loaded
$(retr_user_list(genova_api_url));
// Init date picker with JQuery

// Bootstrap datepicker support different options than jQuery-UI
// https://bootstrap-datepicker.readthedocs.io/en/v1.9.0/options.html
$(() => {
  $ui_date_start.val(
    `${date_start_picked.getDate()}/${
      date_start_picked.getMonth() + 1
    }/${date_start_picked.getFullYear()}`,
  );
  $ui_date_end.val(
    `${date_end_picked.getDate()}/${
      date_end_picked.getMonth() + 1
    }/${date_end_picked.getFullYear()}`,
  );
  $ui_date_end
    .datepicker({
      format: "dd/mm/yyyy",
      defaultViewDate: default_end,
      todayHighlight: true,
      endDate: new Date(),
      startDate: new Date(2020, 1, 1),
      todayBtn: true,
      clearBtn: true,
      changeYear: true,
      changeMonth: true,
      autoSize: true,
      language: "en",
      orientation: "bottom auto",
      showAnim: "fadeIn",
      showOnFocus: true,
    })
    .on("changeDate", function (e) {
      // `e` here contains the extra attributes
      date_picked.date_end = e.date;
      console.log(date_picked);
    });
});
$(() => {
  $ui_date_start
    .datepicker({
      format: "dd/mm/yyyy",
      defaultViewDate: default_start,
      endDate: new Date(),
      startDate: new Date(2020, 1, 1),
      todayHighlight: true,
      todayBtn: true,
      clearBtn: true,
      changeYear: true,
      changeMonth: true,
      autoSize: true,
      language: "en",
      orientation: "bottom auto",
      showAnim: "fadeIn",
      showOnFocus: true,
    })
    .on("changeDate", function (e) {
      // `e` here contains the extra attributes
      date_picked.date_start = e.date;
      console.log(date_picked);
    });
});

// TODO refactor to pure funtion style

//* Main API calls
$button_test_message.on("click", async () => {
  // It takes operation type, message text, voice gender and message note
  const $msg_note = $("#msg_note").val();
  const $msg_text = $("#msg_content").val();
  const voice_picked = document.querySelector(
    "input[name='voice_options']:checked",
  ).value;
  // Hardcoded operation type because we are testing the message
  const operation_type = "Preview";
  let form_data = new FormData();
  if ($msg_text === "") {
    alert("Please enter a message text first");
    return;
  }
  form_data.append("message_text", $msg_text);
  form_data.append("message_note", $msg_note);
  form_data.append("operation", operation_type);
  form_data.append("voice_gender", voice_picked);
  try {
    const audio_url = await get_message_mp3_url(
      python_api_url,
      form_data,
    );
    new Audio(audio_url).play();
    // console.log(`Audio URL: ${audio_url}`);
    $button_download_mp3.attr("disabled", false);
    $button_download_mp3.attr("href", audio_url);
  } catch (error) {
    console.error(error);
  }
});

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
  get_campaign_from_to(python_api_url, date_picked);
});
$button_create_message.on("click", () => {
  const voice_picked = document.querySelector(
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
  console.log("create_message called");
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
      console.log("Message created!");
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
    `<a class="btn btn-warning ${create_campaign_class}" href="javascript:void(0)" title="Create campaign from this message">`,
    `<i class="fa fa-bullhorn"></i>`,
    "</a>",
    ,
    `<a class="btn btn-danger ${remove_msg_class}" href="javascript:void(0)" title="Remove this message from database">`,
    `<i class="fa fa-trash"></i>`,
    "</a>",
  ].join("");
}

/** Operator format for the bootstrap table campaign list. Adds icons and <a> tags to buttons */
function op_formttr_cmp_list(value, row, index) {
  const visualize_campaign_class = "vis_camp_event";
  return [
    `<a class="btn btn-primary ${visualize_campaign_class}" href="javascript:void(0)" title="Visualize">`,
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
  let form_data = new FormData();
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
  console.log("retr_message_list called");
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
            title: "ID",
            align: "center",
            valign: "middle",
          },
          {
            field: "message_date",
            title: "Data messaggio",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "message_note",
            title: "Note",
            align: "center",
          },
          {
            field: "message_duration",
            title: "Durata",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "message_dimension",
            title: "Dimensioni",
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
  // console.log(`Visualizing ${campaign_id} campaign info!`);
  const $bstr_campaign = $("#bstr_camp_vis");
  const $campaign_table = $("#camp_table");
  const request_options = {
    method: "GET",
    redirect: "follow",
  };
  // Retrieve the list of messages from url with GET method with user defnided ID
  // and then put it in the div in table format

  Promise.all([
    fetch(`${root_url + campaign_id}`, request_options),
    fetch(`${genova_api_url}soggettiVulnerabili`, {method: "GET", redirect: "follow"})
  ]).then(responses => Promise.all(responses.map(response => response.json()))).then((values) => {
      const anagrafica_by_tel = values[1].result.reduce((acc, curr) => {
          acc[curr.telefono.replace('+39', '')] = curr;
          return acc;    
      }, {});
      // console.log(anagrafica_by_tel);
      const campaign_list_dict = Object.values(values[0].result.sort((elc, elf) => {
          if ( elc.DataCampagna < elf.DataCampagna ) {
              return 0;
          } else if ( elc.DataCampagna < elf.DataCampagna ) {
              return -1;
          } else { return 1; };
      }).reduce((acc, curr) => {
          acc[curr.NumeroTelefonico] = curr;
          return acc;
      }, {})).map((cmp)=>{
          // console.log(cmp.NumeroTelefonico);
          console.log(anagrafica_by_tel[cmp.NumeroTelefonico]);
          return {...cmp, ...anagrafica_by_tel[cmp.NumeroTelefonico]}
      });
      // console.log(campaign_list_dict);
      $campaign_table.bootstrapTable("destroy").bootstrapTable({
        columns: [
          // {
          //   field: "state",
          //   checkbox: true,
          //   // Not used because the header columns are 1 not 2
          //   // rowspan: 2,
          //   align: "center",
          //   valign: "middle",
          // },
          // {
          //   field: "Identificativo",
          //   title: "ID",
          //   align: "center",
          //   valign: "middle",
          // },
          {
            field: "NumeroTelefonico",
            title: "Telefono",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          // {
          //   field: "telefono",
          //   title: "Telefono",
          //   align: "center",
          //   valign: "middle",
          //   sortable: true,
          // },
          // {
          //   field: "IdentificativoCampagna",
          //   title: "Note",
          //   align: "center",
          // },
          // {
          //   field: "Tipo",
          //   title: "Tipo campagna",
          //   align: "center",
          //   valign: "middle",
          //   sortable: true,
          // },
          {
            field: "Durata",
            title: "Durata",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          // {
          //   field: "DataCampagna",
          //   title: "Inizio campagna",
          //   align: "center",
          //   valign: "middle",
          //   sortable: true,
          // },
          {
            field: "DataChiamata",
            title: "Data/Ora chiamata",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "Esito",
            title: "Esito",
            align: "center",
            valign: "middle",
            sortable: true,
            filterControl: 'select',
          },
          // {
          //   field: "IdentificativoCampagna",
          //   title: "Identificativo",
          //   align: "center",
          //   valign: "middle",
          //   sortable: true,
          // },
          // {
          //   field: "Contatti",
          //   title: "Contatti",
          //   align: "center",
          //   valign: "middle",
          //   sortable: true,
          // },
          {
            field: "nome",
            title: "Nome",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "cognome",
            title: "Cognome",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "gruppo",
            title: "Gruppo",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "indirizzo",
            title: "Indirizzo",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "numero_civico",
            title: "civ.",
            align: "center",
            valign: "middle",
            sortable: false,
          },
          // {
          //   field: "sorgente",
          //   title: "Sorgente",
          //   align: "center",
          //   valign: "middle",
          //   sortable: false,
          // },

        ],
        data: campaign_list_dict,
        uniqueId: "campaign_id",
        striped: true,
        sortable: true,
        sortOrder: "desc",
        sortName: "DataChiamata",
        filterControl: true,
        // showMultiSort: true,
        // multiSortStrictSort: true,
        // sortPriority: [
        //   {"sortName": "numero", "sortOrder": "asc"},
        //   {"sortName": "DataChiamata", "sortOrder": "desc"},
        // ],
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
      $bstr_campaign.show();
      
  });

  // fetch(`${root_url + campaign_id}`, request_options)
  //   .then((async_response) => async_response.json())
  //   .then((async_result) => {
  //     $bstr_campaign.show();
  //     // const campaign_json = async_result.result;
  //     const campaign_list_dict = async_result.result;
  //     console.log(async_result.result);
  //     // const campaign_list_dict = [
  //     //   {
  //     //     campaign_id: campaign_json.IdCampagna,
  //     //     campaign_telephone: campaign_json.NumeroTelefonico,
  //     //     campaign_note: campaign_json.IdentificativoCampagna,
  //     //     campaign_type: campaign_json.Tipo,
  //     //     campaign_duration: campaign_json.Durata,
  //     //     campaign_start_date: campaign_json.DataCampagna,
  //     //     campaign_end_date: campaign_json.DataChiamata,
  //     //     campaign_status: campaign_json.Esito,
  //     //     campaign_identifier: campaign_json.Identificativo,
  //     //   },
  //     // ];
  //     $campaign_table.bootstrapTable("destroy").bootstrapTable({
  //       columns: [
  //         // {
  //         //   field: "state",
  //         //   checkbox: true,
  //         //   // Not used because the header columns are 1 not 2
  //         //   // rowspan: 2,
  //         //   align: "center",
  //         //   valign: "middle",
  //         // },
  //         // {
  //         //   field: "Identificativo",
  //         //   title: "ID",
  //         //   align: "center",
  //         //   valign: "middle",
  //         // },
  //         {
  //           field: "NumeroTelefonico",
  //           title: "Telefono",
  //           align: "center",
  //           valign: "middle",
  //           sortable: true,
  //         },
  //         // {
  //         //   field: "IdentificativoCampagna",
  //         //   title: "Note",
  //         //   align: "center",
  //         // },
  //         // {
  //         //   field: "Tipo",
  //         //   title: "Tipo campagna",
  //         //   align: "center",
  //         //   valign: "middle",
  //         //   sortable: true,
  //         // },
  //         {
  //           field: "Durata",
  //           title: "Durata campagna",
  //           align: "center",
  //           valign: "middle",
  //           sortable: true,
  //         },
  //         // {
  //         //   field: "DataCampagna",
  //         //   title: "Inizio campagna",
  //         //   align: "center",
  //         //   valign: "middle",
  //         //   sortable: true,
  //         // },
  //         {
  //           field: "DataChiamata",
  //           title: "Data/Ora chiamata",
  //           align: "center",
  //           valign: "middle",
  //           sortable: true,
  //         },
  //         {
  //           field: "Esito",
  //           title: "Esito chiamata",
  //           align: "center",
  //           valign: "middle",
  //           sortable: true,
  //         },
  //         // {
  //         //   field: "IdentificativoCampagna",
  //         //   title: "Identificativo",
  //         //   align: "center",
  //         //   valign: "middle",
  //         //   sortable: true,
  //         // },
  //         {
  //           field: "Contatti",
  //           title: "Contatti",
  //           align: "center",
  //           valign: "middle",
  //           sortable: true,
  //         },
  //       ],
  //       data: campaign_list_dict,
  //       uniqueId: "campaign_id",
  //       striped: true,
  //       sortable: true,
  //       pageNumber: 1,
  //       pageSize: 10,
  //       pageList: [10, 25, 50, 100],
  //       searchHighlight: true,
  //       pagination: true,
  //       search: true,
  //       showToggle: true,
  //       showExport: true,
  //       exportDataType: "all",
  //       exportTypes: [
  //         "csv",
  //         "txt",
  //         "sql",
  //         "doc",
  //         "excel",
  //         "xlsx",
  //         "pdf",
  //       ],
  //     });
  //   })
  //   .catch((error) => console.log("error", error));
}

/** Get users list info in JSON format */
function retr_user_list(root_url) {
  console.log(`Retriving users list from ${root_url}`);
  console.log("Retriving users list!");
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
          indirizzo: item.indirizzo,
          numero_civico: item.numero_civico,
          telefono: item.telefono,
          user_group: item.gruppo,
          sorgente: item.sorgente,
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
            title: "Nome",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "user_surname",
            title: "Cognome",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "indirizzo",
            title: "Indirizzo",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "numero_civico",
            title: "Civico",
            align: "center",
            valign: "middle",
            sortable: false,
          },
          {
            field: "telefono",
            title: "Telefono",
            align: "center",
            valign: "middle",
            sortable: false,
          },
          {
            field: "user_group",
            title: "User group",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "sorgente",
            title: "Fonte dati",
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
      console.log(`Retriving users list from ${root_url}`);
      console.error("error", error);
      console.log(
        "Error in retriving user list!!!! Check VPN connection!",
      );
    });
}

/** Retrieves campaign in given timeframe. Creates bootstrap table and fills the data */
function get_campaign_from_to(root_url = "http://localhost:8000/") {
  const $bstr_div_camp = $("#bstr_camp");
  const $camp_table = $("#camp_table_time");
  const date_start = `${date_picked.date_start.getFullYear()}-${
    date_picked.date_start.getMonth() + 1
  }-${date_picked.date_start.getDate()} 12:00:00`;
  const date_end = `${date_picked.date_end.getFullYear()}-${
    date_picked.date_end.getMonth() + 1
  }-${date_picked.date_end.getDate()} 12:00:00`;
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
          // {
          //   field: "state",
          //   checkbox: true,
          //   // Not used because the header columns are 1 not 2
          //   // rowspan: 2,
          //   align: "center",
          //   valign: "middle",
          // },
          // {
          //   field: "camp_id",
          //   title: "Campaign ID",
          //   align: "center",
          //   valign: "middle",
          // },
          // {
          //   field: "camp_type",
          //   title: "Tipo campagna",
          //   align: "center",
          //   valign: "middle",
          // },
          {
            field: "camp_date",
            title: "Data campagna",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "camp_identifier",
            title: "Note",
            align: "center",
          },
          {
            field: "camp_user",
            title: "Utente",
            align: "center",
            valign: "middle",
            sortable: true,
          },
          {
            field: "camp_contact",
            title: "Contatti",
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
