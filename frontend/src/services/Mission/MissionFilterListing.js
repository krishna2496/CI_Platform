import store from '../../store'
import axios from 'axios'

export default async() => {
    let responseData;
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    await axios({
            url: process.env.VUE_APP_API_ENDPOINT + "app/user-filter",
            method: 'get',
            headers: {
                'X-localization': defaultLanguage,
            }
        })
        .then((response) => {
            if (response.data && response.data.data.filters) {
                let filterData = {};
                filterData.search = response.data.data.filters.search;
                filterData.countryId = response.data.data.filters.country_id;
                filterData.stateId = response.data.data.filters.state_id;
                filterData.cityId = response.data.data.filters.city_id;
                filterData.themeId = response.data.data.filters.theme_id;
                filterData.skillId = response.data.data.filters.skill_id;
                filterData.tags = response.data.data.filters.tags;
                filterData.sortBy = response.data.data.filters.sort_by;
                filterData.currentView = parseInt(response.data.data.filters.current_view);
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
                filterData.currentView = 0
                store.commit('userFilter', filterData)
            }
        })
    return responseData;
}