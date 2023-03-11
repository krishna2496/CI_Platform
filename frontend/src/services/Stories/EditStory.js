import axios from 'axios'
import store from '../../store'

export default async(storyId) => {
    let responseData = {};
    var defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    var url = process.env.VUE_APP_API_ENDPOINT + "app/edit/story/" + storyId;
    // document.body.classList.add("loader-enable");
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
          responseData.data = response.data.data[0];
          // document.body.classList.remove("loader-enable");
      })
      .catch((error) => {
          responseData.status = error.response.status;
          if (error.response.data.errors[0].message) {
              responseData.error = true;
              responseData.message = error.response.data.errors[0].message;
          }
          // document.body.classList.remove("loader-enable");
      });
    return responseData;
}