import store from '../../store'
import axios from 'axios'

export default async() => {
    let responseData;
    let defaultLanguage = '';
    let headerMenuData = {}

    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/explore-mission";

    await axios({
        url: url,
        method: 'get',
        headers: {
            'X-localization': defaultLanguage,
        }
    })
      .then((response) => {
          // Set header menu data
          if (response.data.data) {
              headerMenuData.top_theme = response.data.data.top_themes;
              headerMenuData.top_country = response.data.data.top_countries;
              headerMenuData.top_organization = response.data.data.top_organization;
          }
          store.commit('headerMenu', headerMenuData);
      })
      .catch(function() {});
    return responseData;
}