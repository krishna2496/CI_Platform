import store from '../../store'
import axios from 'axios'
import constants from '../../constant';

export default async(data) => {
    let responseData = {};
    let defaultLanguage = '';
    let missionId = data.mission_id;
    let perPage = constants.RECENT_VOLUNTEERES_PER_PAGE

    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }

    let url = process.env.VUE_APP_API_ENDPOINT + "app/mission/" + missionId + "/volunteers?page=" + data.page + "&perPage=" + perPage
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
        } else {
            responseData.data = [];
            responseData.pagination = [];
        }

    })
      .catch(function() {
          responseData.error = true;
      });
    return responseData;
}