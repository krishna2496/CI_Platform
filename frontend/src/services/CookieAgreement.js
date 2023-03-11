import store from '../store'
import axios from 'axios'

export default async(data) => {
  let responseData = {}
  responseData.error = false;
  responseData.data = [];
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  await axios({
    url: process.env.VUE_APP_API_ENDPOINT + "app/accept-cookie-agreement",
    method: 'POST',
    data,
    headers: {
      'X-localization': defaultLanguage,
    }
  }).then((response) => {
    responseData.error = false;
    responseData.message = response.data.message;
  })
    .catch(function() {
      responseData.error = true;

    });
  return responseData;
}