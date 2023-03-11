import axios from 'axios'
import store from '../store'

export default async(filterData) => {
    let responseData = {};
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/dashboard";

    if (filterData.year != '' && filterData.year != null && filterData.year != 0) {
        url = url + "?year=" + parseInt(filterData.year)
        if (filterData.month != '' && filterData.month != null && filterData.month != 0) {
            url = url + "&month=" + filterData.month
        }
    }

    if (filterData.mission_id != '' && filterData.mission_id != null && filterData.mission_id != 0) {
        if (filterData.year == '' || filterData.year == null || filterData.year == 0) {
            url = url + "?mission_id=" + filterData.mission_id
        } else {
            url = url + "&mission_id=" + filterData.mission_id
        }
    }

    await axios({
        url: url,
        method: 'GET',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          responseData.error = false;
          responseData.message = response.data.message;
          responseData.data = response.data.data;
      })
      .catch(function(error) {
          if (error.response.data.errors[0].message) {
              responseData.error = true;
              responseData.message = error.response.data.errors[0].message;
          }
      });
    return responseData;
}