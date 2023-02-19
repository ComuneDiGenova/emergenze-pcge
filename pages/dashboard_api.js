// This file will export the dashboard api calls for fetching and managinging the dashboard data

export async function retr_message(root_div, message_id) {
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
  await fetch(`${root_div}_retrive_message_list`, request_options)
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
  console.log(`element with id ${message_id} was not found`);
  return null;
}

export function format_date(date) {
  const [date_str, time_str] = [
    date.split(" ")[0],
    date.split(" ")[1],
  ];
  const date_arr = date_str.split("-");
  const time_arr = time_str.split(":");
  const [year, month, day, hour, minute, second] = [
    date_arr[2],
    date_arr[1],
    date_arr[0],
    time_arr[0],
    time_arr[1],
    time_arr[2],
  ];
  return `${year}/${month}/${day} ${hour}:${minute}:${second}`;
}

export function convert_to_date(string_date) {
  const date = new Date(string_date);
  const date_year = date.getFullYear();
  const date_month =
    date.getMonth() + 1 <= 9
      ? "0" + (date.getUTCMonth() + 1)
      : date.getUTCMonth() + 1;
  const date_day =
    date.getDate() <= 9 ? "0" + date.getDate() : date.getDate();
  const date_hour =
    date.getHours() <= 9 ? "0" + date.getHours() : date.getHours();
  const date_minute =
    date.getMinutes() <= 9
      ? "0" + date.getMinutes()
      : date.getMinutes();
  const date_second =
    date.getSeconds() <= 9
      ? "0" + date.getSeconds()
      : date.getSeconds();
  return `${date_year}-${date_month}-${date_day} ${date_hour}:${date_minute}:${date_second}`;
}

export async function test_voice(root_url, form_data) {
  const request_options = {
    method: "POST",
    body: form_data,
    redirect: "follow",
  };
  await fetch(`${root_url}_test_voice`, request_options)
    .then((asyn_response) => asyn_response.json())
    .then((async_result) => {
      return async_result.result;
    })
    .catch((error) => console.log("error", error));
}

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
