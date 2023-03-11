import store from '../../store'
import axios from 'axios'

export default async(data) => {
  // login api call with params email address and password
  let responseData = {}
  responseData.error = false;
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  document.body.classList.add("loader-enable");
  await axios({
    url: process.env.VUE_APP_API_ENDPOINT + "app/transmute",
    data,
    method: 'post',
    headers: {
      'X-localization': defaultLanguage
    }
  }).then((response) => {
    setTimeout(() => {
      document.body.classList.remove("loader-enable");
    }, 700);
  })
  .catch(error => {
    document.body.classList.remove("loader-enable");
    if (error.response.data.errors[0].message) {
      responseData.error = true;
      responseData.message = error.response.data.errors[0].message;
    }
  })
  return responseData;
}
