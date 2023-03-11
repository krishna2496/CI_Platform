import axios from 'axios'
import store from '../../store'

export default async(page) => {
    let responseData = {};
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/messages?page=" + page;
    await axios({
        url: url,
        method: 'GET',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          responseData.error = false;
          responseData.message = response.data.message;
          responseData.data = response.data.data;
          responseData.pagination = response.data.pagination;
      })
      .catch(function(error) {
          if (error.response.data.errors[0].message) {
              responseData.error = true;
              responseData.message = error.response.data.errors[0].message;
          }
      });
    return responseData;
}