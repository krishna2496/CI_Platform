import axios from 'axios'
import store from '../../store'

export default async(data) => {
  const responseData = {};
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  const url = `${process.env.VUE_APP_API_ENDPOINT}/app/setting`;

  await axios({
    url: url,
    method: 'POST',
    data,
    headers: {
      'X-localization': defaultLanguage,
      'token': store.state.token
    }
  })
  .then((response) => {
    responseData.error = false;
    responseData.message = response.data.message;
    document.body.classList.remove('loader-enable');
  })
  .catch(function(error) {
    if (error.response.data.errors[0].message) {
      responseData.error = true;
      responseData.message = error.response.data.errors[0].message;
    }
    document.body.classList.remove('loader-enable');
  });
  return responseData;
}
