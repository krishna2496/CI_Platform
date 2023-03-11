import axios from 'axios'
import store from '../../store'

export default async(page) => {
    let responseData = {
        error: 'true'
    };

    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/timesheet/goal-requests?page=" + page;

    await axios({
        url: url,
        method: 'GET',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          if (response.data.data) {
              responseData.error = false;
              responseData.data = response.data.data
              responseData.pagination = response.data.pagination
              responseData.message = response.data.message
          }
      })
      .catch(function(error) {
          if (error.response.data.errors[0].message) {
              responseData.error = true;
              responseData.message = error.response.data.errors[0].message;
          }
      });
    return responseData;
}