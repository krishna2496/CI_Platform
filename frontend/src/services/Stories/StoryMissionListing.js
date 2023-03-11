import axios from 'axios'
import store from '../../store'

export default async() => {
  let responseData = {};
  var defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  var url = process.env.VUE_APP_API_ENDPOINT + "app/user/missions";
  await axios({
    url: url,
    method: 'GET',
    headers: {
      'X-localization': defaultLanguage,
    }
  })
    .then((response) => {
      responseData.error = false;
      responseData.data = response.data.data;
    })
    .catch(function() {
      responseData.error = true;
    });
  return responseData;
}