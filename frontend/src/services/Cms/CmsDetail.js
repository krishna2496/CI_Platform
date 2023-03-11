import axios from 'axios'
import store from '../../store'

export default async(slug) => {
    let responseData = {};
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }

    await axios({
        url: `${process.env.VUE_APP_API_ENDPOINT}app/cms/${slug}`,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage
        }
    })
    .then((response) => {
        responseData.error = false;
        if (response.data.data) {
            responseData.data = response.data.data;
        }
    })
    .catch(function() {
        responseData.error = true;
    });

    return responseData;
}