import axios from 'axios'
import store from '../../store'

export default async(timeSheetId) => {
    let responseData = [];

    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/timesheet/" + timeSheetId;

    await axios({
        url: url,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          if (response.data.data) {
              responseData = response.data.data
          }
      })
      .catch(function() {});
    return responseData;
}