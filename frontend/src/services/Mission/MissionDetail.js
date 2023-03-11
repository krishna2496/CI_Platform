import store from '../../store'
import axios from 'axios'

export default async(data) => {
    let responseData = {};
    let defaultLanguage = '';

    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    const missionId = data.mission_id;

    document.body.classList.add("loader-enable");
    let url = process.env.VUE_APP_API_ENDPOINT + "app/mission/" + missionId
    if (data.donation_mission) {
        url = `${url}?with_donation_attributes=1`
    }
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
        } else {
            responseData.data = [];
        }
        document.body.classList.remove("loader-enable");
    })
      .catch(function() {
          responseData.error = true;
          document.body.classList.remove("loader-enable");
      });
    return responseData;
}