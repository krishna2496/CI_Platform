import axios from 'axios'
import store from '../store'

export default async(data) => {
    let responseData;
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }

    let url = process.env.VUE_APP_API_ENDPOINT + "app/filter-data";

    if (data.countryId != '') {
        url = url + "?country_id=" + data.countryId
    }
    if (data.stateId != '') {
        if (data.countryId != '') {
            url = url + "&state_id=" + data.stateId
        } else {
            url = url + "?state_id=" + data.stateId
        }
    }
    
    if (data.cityId != '') {
        if (data.countryId != '' ||  data.stateId != '') {
            url = url + "&city_id=" + data.cityId
        } else {
            url = url + "?city_id=" + data.cityId
        }
    }

    if (data.themeId != '') {
        if (data.countryId != '' ||  data.stateId != '' || data.cityId != '') {
            url = url + "&theme_id=" + data.themeId
        } else {
            url = url + "?theme_id=" + data.themeId
        }
    }

    if (data.search != '') {
        if (data.countryId != '' ||  data.stateId != '' || data.cityId != '' || data.themeId != '') {
            url = url + "&search=" + data.search
        } else {
            url = url + "?search=" + data.search
        }
    }

    if (data.exploreMissionType != '') {
        if (data.countryId != '' || data.cityId != '' || data.themeId != '' || data.search != '') {
            url = url + "&explore_mission_type=" + data.exploreMissionType
        } else {
            url = url + "?explore_mission_type=" + data.exploreMissionType
        }
    }

    if (data.exploreMissionParams != '') {
        if (data.countryId != '' || data.cityId != '' || data.themeId != '' || data.search != '' || data.exploreMissionType != '') {
            url = url + "&explore_mission_params=" + data.exploreMissionParams
        } else {
            url = url + "?explore_mission_params=" + data.exploreMissionParams
        }
    }

    await axios({
        url: url,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage,
        },

    })
      .then((response) => {
          if (response.data.data) {
              responseData = response.data.data;
          } else {
              responseData = ''
          }
      })
      .catch(function() {});
    return responseData;
}