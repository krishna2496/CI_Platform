import axios from 'axios'
import store from '../../store'

export default async(currentPage) => {
  const responseData = {};
  let defaultLanguage = '';
  if (store.state.defaultLanguage !== null) {
    defaultLanguage = (store.state.defaultLanguage).toLowerCase();
  }
  const url = `${process.env.VUE_APP_API_ENDPOINT}app/setting`;
  document.body.classList.add('loader-enable');
  await axios({
    url: url,
    method: 'GET',
    headers: {
      'X-localization': defaultLanguage,
      'token': store.state.token,
    }
  })
  .then((response) => {
    responseData.error = false;
    responseData.message = response.data.message;
    if (response.data.data) {
      responseData.data = response.data.data;
    } else {
      responseData.data = []
    }
    if (response.data.pagination) {
      responseData.pagination = response.data.pagination;
    }
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
};
