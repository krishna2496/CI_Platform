import store from '../../store'
import axios from 'axios'
import {policy} from '../../services/service';

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
    url: process.env.VUE_APP_API_ENDPOINT + "app/login",
    data,
    method: 'post',
    headers: {
      'X-localization': defaultLanguage
    }
  }).then((response) => {

    //Store login data in local storage
    store.commit('loginUser', response.data.data)
    policy().then(response => {
      if (!response.error && response.data.length > 0) {
        store.commit('policyPage', response.data);
      } else {
        store.commit('policyPage', null);
      }
    });
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