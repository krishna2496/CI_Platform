import axios from 'axios'
import store from '../../store'

export default async(data) => {
    let responseData = {
        error: 'true'
    };

    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/timesheet";

    await axios({
        url: url,
        method: 'POST',
        data,
        headers: {
            'X-localization': defaultLanguage,
            'Content-Type': 'multipart/form-data'
        }
    })
      .then((response) => {
          if (response.data.data) {
              responseData.error = false;
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