import axios from 'axios'
import store from '../../store'

export default async(deletFile) => {
    let responseData = [];
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/timesheet/" + deletFile.timesheet_id + "/document/" + deletFile.document_id;
    await axios({
        url: url,
        method: 'DELETE',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          if (response.data.message) {
              responseData = response.data.message
          }
      })
      .catch(function() {});
    return responseData;
}