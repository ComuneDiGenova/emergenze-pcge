// This file will export the dashboard api calls for fetching and managinging the dashboard data

export default async function retr_message(root_div, message_id) {
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
