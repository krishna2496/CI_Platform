import store from '../../store'
import axios from 'axios'

export default async(data) => {
    let responseData;
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/missions?page=" + data.page
    url = `${url}&with_donation_attributes=1&with_donation_statistics=true`

    if (data.search != '' && data.search != null) {
        url = url + "&search=" + data.search
    }

    if (data.countryId != '' && data.countryId != null) {
        url = url + "&country_id=" + data.countryId
    }
    if (data.stateId != '' && data.stateId != null) {
        url = url + "&state_id=" + data.stateId
    }
    if (data.cityId != '' && data.cityId != null) {
        url = url + "&city_id=" + data.cityId
    }
    if (data.themeId != '' && data.themeId != null) {
        url = url + "&theme_id=" + data.themeId
    }
    if (data.skillId != '' && data.skillId != null) {
        url = url + "&skill_id=" + data.skillId
    }

    if (data.sortBy != '' && data.sortBy != null) {
        url = url + "&sort_by=" + data.sortBy
    }

    if (data.exploreMissionType != '' && data.exploreMissionType != undefined) {
        url = url + "&explore_mission_type=" + data.exploreMissionType
    }

    if (data.exploreMissionParams != '' && data.exploreMissionParams != undefined) {
        url = url + "&explore_mission_params=" + data.exploreMissionParams
    }

    url = url + "&current_view=" + data.currentView

    await axios({
        url: url,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          responseData = response.data;
          // Set filter data
            if (response.data.meta_data.filters) {
                let filterData = {};
                filterData.search = response.data.meta_data.filters.search;
                filterData.countryId = response.data.meta_data.filters.country_id;
                filterData.stateId = response.data.meta_data.filters.state_id;
                //todo temp fix CIP-938; redo me when filters + tags will work
                filterData.cityId = data.cityId;
                filterData.themeId = response.data.meta_data.filters.theme_id;
                filterData.skillId = response.data.meta_data.filters.skill_id;
                filterData.tags = response.data.meta_data.filters.tags;
                filterData.sortBy = response.data.meta_data.filters.sort_by;
                filterData.currentView = parseInt(response.data.meta_data.filters.current_view);
                store.commit('userFilter', filterData)
            } else {
                let filterData = {};
                filterData.search = '';
                filterData.countryId = '';
                filterData.stateId = '';
                filterData.cityId = '';
                filterData.themeId = '';
                filterData.skillId = '';
                filterData.tags = '';
                filterData.sortBy = '';
                filterData.currentView = '';
                store.commit('userFilter', filterData)
            }
            if (store.state.clearFilterSet == "") {
                document.body.classList.remove("loader-enable");
            }
        })
        .catch(function() {
            // if (store.state.clearFilterSet == "") {
            //     document.body.classList.remove("loader-enable");
            // }
        });
    return responseData;
}