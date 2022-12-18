// This is a JQuery function that is called when the page is loaded
$(document).ready(() => {
  // // TODO Make a call for users list when the page is loaded
  // TODO Add button for deletion of messages in message table

  // TODO Fetching data stays iun browser cache, and for the same data it is downloaded again and again
  // TODO: Add a button to clear the cache
  // TODO: Make this a class of buttons, so that the same function can be called with different parameters
  // TODO Date from JQueryUI handles only dates, not time. So the time is always 00:00:00
  // ! Bootstrap table accepts only Arrays as input, not JSON objects

  const dashboard_header = (document.getElementById(
    "dashboard_header",
  ).innerHTML = "JS_bootto_strappo_dashboardo is working!");
  const dashboard_text = document.getElementById("dashboard_text");
  const button_message_list =
    document.getElementById("button_msg_list");
  const button_user_list = document.getElementById(
    "button_user_list",
  );
  const button_vis_campaign = document.getElementById(
    "button_vis_campaign",
  );
  const button_get_camapaign = document.getElementById(
    "button_campaign_from_to",
  );
  const header_cmp_list = document.getElementById("camp_list_header");
  const ui_date_start = document.getElementById("ui_date_start");
  const ui_date_end = document.getElementById("ui_date_end");
  const bstr_results = document.getElementById("bstr_user");
  const voice_picker_female = document.getElementById(
    "voice_picker_female",
  );
  const voice_picker_male = document.getElementById(
    "voice_picker_male",
  );
  const msg_send = document.getElementById("button_send_message");

  //* API URL
  const python_api_url =
    "http://localhost:8000/emergenze/user_campaign/";
  const genova_api_url = "https://emergenze-apit.comune.genova.it/";

  let date_start_picked = "2021-01-01";
  let date_end_picked = "2021-01-31";
  let date_picked = {
    date_start: date_start_picked,
    date_end: date_end_picked,
  };

  let voice_picked = "F";
  let group_picked = "1";

  // Loads the userlist when document is loaded
  // retr_user_list();
  $(() => {
    console.log("Hello World.This page is loaded!");
  });
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
    date_picked.date_start = date_start_picked;
  });
  $("#ui_date_end").on("change", () => {
    // date_start = "2022-01-10 10:10"
    // Convert the date to the format of the API
    date_end_picked = $("#ui_date_end").val();
    console.log("End date_picked_>", date_end_picked);
    date_picked.date_end = date_end_picked;
  });

  // JQuery style of registering listeners
  $("#voice_picker_female").on("click", () => {
    voice_picked = $("#voice_picker_female").val();
    console.log(`voice_picked: ${voice_picked}`);
    alert(`voice_picked: ${voice_picked}`);
  });
  $("#voice_picker_male").on("click", () => {
    voice_picked = $("#voice_picker_male").val();
    console.log(`voice_picked: ${voice_picked}`);
    alert(`voice_picked: ${voice_picked}`);
  });

  //* Main API calls

  $("#button_create_campaign").on("click", async () => {
    const msg_id_input = document.getElementById("msg_id").value;
    const group_number = document.querySelectorAll(
      "input[name='group_option']:checked",
    )[0].value;
    const message_returned = await retr_message(
      python_api_url,
      msg_id_input,
    );
    let msg_dict = {
      message_text: "Empty message",
      voice: voice_picked,
      group: group_number,
    };

    if (message_returned == null) {
      msg_dict.message_text =
        document.getElementById("msg_content").value;
    } else {
      msg_dict.message_text = message_returned.message.note;
    }
    await create_campaign(python_api_url, msg_dict);
    alert(`Campaign: Sent!`);
  });

  // JS Registering listeners for the buttons
  button_message_list.onclick = async () => {
    await retr_message_list(python_api_url);
    await listen_delete();
  };
  button_vis_campaign.onclick = () => {
    vis_campaign(python_api_url);
  };
  button_user_list.onclick = () => {
    retr_user_list(genova_api_url);
  };
  button_get_camapaign.onclick = () => {
    get_campaign_from_to(python_api_url, date_picked);
  };
  msg_send.onclick = () => {
    const msg_dict = {
      message: document.getElementById("msg_content").value,
      voice_gender: voice_picked,
      note: document.getElementById("msg_note").value,
    };
    create_message(python_api_url, msg_dict);
  };
  // async function fetch_generic(url, bootstrap_id, req_opt, table_id) {
  //   const bstr_container = document.getElementById(bootstrap_id);
  //   const message_table = $("#" + table_id);
  //   bstr_container.style.display = "block";
  //   try {
  //     const response = await fetch(url, req_opt);
  //     const result_1 = await response.json();
  //     message_table.bootstrapTable({
  //       data: message_list_dict,
  //       pagination: true,
  //       search: true,
  //       // showColumns: true,
  //       // showRefresh: true,
  //       showToggle: true,
  //       // showExport: true,
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
  //       exportOptions: {
  //         fileName: "export",
  //         jspdf: {
  //           orientation: "l",
  //           format: "a4",
  //           margins: { left: 20, right: 10, top: 10, bottom: 10 },
  //           autotable: {
  //             styles: {
  //               fillColor: "inherit",
  //               textColor: "inherit",
  //             },
  //             tableWidth: "auto",
  //           },
  //         },
  //       },
  //     });
  //     return result_1;
  //   } catch (error) {
  //     return console.log("error", error);
  //   }
  // }

  async function retr_message(root_div, message_id) {
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
    const returned_message = await fetch(
      `${root_div}_retrive_message_list`,
      request_options,
    )
      .then((asyn_response) => asyn_response.json())
      .then((async_result) => {
        const message_list = async_result.result;
        return Object.entries(message_list).forEach(
          ([key, value]) => {
            // message_list.forEach((element) => {
            if (key == message_id) {
              message_dict = {};
              message_dict.message = value;
            }
          },
        );
      })
      .catch((error) => console.log("error", error));
    if (message_dict != null) {
      return message_dict;
    }
    console.log(`element with id ${message_id} was not found`);
    return null;
  }

  function delete_message(message_id = "1", root_div) {
    const form_data = new FormData();
    // const msg_id = document.getElementById("msg_id").value;
    form_data.append("message_id_delete", message_id);
    const request_options = {
      method: "DELETE",
      body: form_data,
      redirect: "follow",
      // set the request mode to no-cors
      // mode: "no-cors",
    };
    fetch(`${root_div + "_delete_older_message"}`, request_options)
      .then((response) => response.json())
      .then((result) => console.log(result))
      .catch((error) => {
        alert(
          `Error while deleting the message from database: ${error}`,
        );
        console.log("error", error);
      });
  }

  // TODO Add option for using messages from the database
  async function create_campaign(
    root_div,
    dict_of_options = {
      message_text: "Test messagio per alert sistema",
      group: "1",
      voice: voice_picked,
    },
  ) {
    let formdata = new FormData();
    formdata.append("message_text", dict_of_options.message_text);
    formdata.append("group", dict_of_options.group);
    formdata.append("voice_gender", dict_of_options.voice);

    const requestOptions = {
      method: "POST",
      body: formdata,
      redirect: "follow",
    };

    fetch(`${root_div}_create_capmaign`, requestOptions)
      .then((response) => response.json())
      .then((result) => {
        console.log(result);
      })
      .catch((error) => console.log("error", error));
  }

  function create_message(
    root_div,
    dict_of_options = {
      message: "Sono romano, grana padano!",
      voice_gender: "M",
      note: "Gter_test_JS",
    },
  ) {
    document.getElementById("dashboard_text").innerHTML =
      "Creating message!";
    const bstr_message = document.getElementById("bstr_message");
    const message_table = $("#msg_table");
    // return console.log('dict_of_options', dict_of_options);
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
    fetch(`${root_div}_create_message`, request_options)
      .then((asyn_response) => asyn_response.json())
      .then((async_result) => {
        console.log("async_result from alertpy", async_result);
        document.getElementById("dashboard_text").innerHTML =
          "Message created!";
        retr_message_list(python_api_url);
      })
      .catch((error) => console.log("error", error));
  }
  // TODO get querySelectorAll

  /* This function operates on bootstrap table for
  deletions of rows*/
  async function listen_delete() {
    const $message_table = $("#msg_table");
    const $button = $("#button_delete");

    $(function () {
      $button.click(function () {
        var ids = $.map(
          $message_table.bootstrapTable("getSelections"),
          function (row) {
            return row.message_id;
          },
        );
        $message_table.bootstrapTable("remove", {
          field: "message_id",
          values: ids,
        });
        // Each element of the bootstrap table picked run this function
        ids.forEach((element) => {
          delete_message(element, python_api_url);
          console.log("Deleted id:", element);
        });
      });
    });
  }

  function fill_bootstrap_table(input, table_name) {
    table_name.bootstrapTable({
      data: input,
      uniqueId: "message_id",
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
  }
  // Retrieve the list of messages from url with GET method
  /**Retrieves list of messages in JSON format */
  async function retr_message_list(root_div) {
    document.getElementById("dashboard_text").innerHTML =
      "Retriving message list!";
    const bstr_message = document.getElementById("bstr_message");
    const message_table = $("#msg_table");
    const request_options = {
      method: "GET",
      redirect: "follow",
    };
    // Retrieve the list of messages from url with GET method
    // and then put it ien the div in table format
    fetch(`${root_div}_retrive_message_list`, request_options)
      .then((asyn_response) => asyn_response.json())
      .then((async_result) => {
        const message_list = async_result.result;
        const message_list_dict = [];
        const message_table = $("#msg_table");
        for (let i in message_list) {
          message_list_dict.push({
            message_date: message_list[i].data_creazione,
            message_dimension: message_list[i].dimensione,
            message_duration: message_list[i].durata,
            message_id: message_list[i].id_messaggio,
            message_note: message_list[i].note,
          });
        }
        bstr_message.style.display = "block";
        fill_bootstrap_table(message_list_dict, message_table);
      })
      .catch((error) => console.log("error", error));
  }

  /**Get info about campaign with {ID} in JSON format */
  function vis_campaign(
    root_div,
    campaign_id = "vo6274305ad55304.39423618",
  ) {
    dashboard_text.innerHTML = `Visualizing ${campaign_id} campaign info!`;
    const bstr_campaign = document.getElementById("bstr_camp_vis");
    const campaign_table = $("#camp_table");
    const request_options = {
      method: "GET",
      redirect: "follow",
    };
    // Retrieve the list of messages from url with GET method with user defnided ID
    // and then put it in the div in table format
    fetch(`${root_div + campaign_id}`, request_options)
      .then((async_response) => async_response.json())
      .then((async_result) => {
        bstr_campaign.style.display = "block";
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
        fill_bootstrap_table(campaign_list_dict, campaign_table);
      })
      .catch((error) => console.log("error", error));
  }

  /** Get users list info in JSON format */
  function retr_user_list(root_div) {
    dashboard_text.innerHTML = "Retriving users list!";
    const bstr_results = document.getElementById("bstr_user");
    const user_table = $("#user_table_1");
    const request_options = {
      method: "GET",
      redirect: "follow",
    };
    fetch(
      `${root_div}emergenze/soggettiVulnerabili/`,
      request_options,
    )
      .then((async_response) => async_response.json())
      .then((async_anwser) => {
        bstr_results.style.display = "block";
        const user_list_json = async_anwser.result;
        const user_list_dict = user_list_json.map((item) => {
          return {
            user_id: item.id,
            user_name: item.nome,
            user_surname: item.cognome,
            user_group: item.gruppo,
          };
        });
        fill_bootstrap_table(user_list_dict, user_table);
      })
      .catch((error) => {
        console.error("error", error);
        console.log(
          "Error in retriving user list! Check VPN connection!",
        );
      });
  }

  function get_campaign_from_to(
    root_div = "http://localhost:8000/",
    date_dict = {
      date_start: "2021-01-01",
      date_end: "2021-01-31",
    },
  ) {
    const bstr_results = document.getElementById("bstr_camp");
    const camp_table = $("#camp_table_time");
    const date_start = date_dict.date_start + " 12:00";
    const date_end = date_dict.date_end + " 12:00";
    console.log("date_start form date_dict: " + date_start);
    console.log("date_end form date_dict: " + date_end);
    header_cmp_list.innerHTML =
      "Campaign list from " + date_start + " to " + date_end;
    let form_data = new FormData();
    form_data.append("date_start", date_start);
    form_data.append("date_end", date_end);
    const requestOptions = {
      method: "POST",
      body: form_data,
      redirect: "follow",
    };

    fetch(`${root_div}get_campaign_from_to`, requestOptions)
      .then((async_response) => async_response.json())
      .then((async_anwser) => {
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
        fill_bootstrap_table(camp_list_dict, camp_table);
      })
      .catch((error) => console.log("error", error));
  }
});
