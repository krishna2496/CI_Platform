import store from '../../store'
import axios from 'axios'

export default async() => {
  let responseData = {}
  responseData.error = false;
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  document.body.classList.add("loader-enable");
  await axios({
    url: process.env.VUE_APP_API_ENDPOINT + "app/logout",
    method: 'get',
    headers: {
      'X-localization': defaultLanguage
    }
  }).then((response) => {

    //Store login data in local storage
    store.commit('logoutUser', response.data.data)

    setTimeout(() => {
      document.body.classList.remove("loader-enable");
    }, 700)

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