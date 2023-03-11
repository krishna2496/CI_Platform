import store from '../../store'
import axios from 'axios'

export default async(data) => {
    let responseData = {};
    let defaultLanguage = '';

    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }

    let url = process.env.VUE_APP_API_ENDPOINT + "app/mission/" + data.missionId + "/comments?page=" + data.page
    await axios({
        url: url,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage,
        }
    }).then((response) => {
        responseData.error = false;
        if (response.data.data) {
            responseData.data = response.data.data;
            responseData.pagination = response.data.pagination;
        }
    })
      .catch(function() {
          responseData.error = true;
      });
    return responseData;
}