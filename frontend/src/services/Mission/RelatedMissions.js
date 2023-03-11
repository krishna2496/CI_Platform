import store from '../../store'
import axios from 'axios'

export default async(missionId) => {
    let responseData = {};
    let defaultLanguage = '';

    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }

    let url = process.env.VUE_APP_API_ENDPOINT + "app/related-missions/" + missionId
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
        }
    })
    return responseData;
}