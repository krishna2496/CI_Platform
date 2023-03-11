import store from '../../store'
import axios from 'axios'

export default async() => {
  let responseData;
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  await axios({
    url: process.env.VUE_APP_API_ENDPOINT + "app/cms/listing",
    method: 'get',
    headers: {
      'X-localization': defaultLanguage
    }
  })
    .then((response) => {
      responseData = response.data.data;
    })
  return responseData;
}