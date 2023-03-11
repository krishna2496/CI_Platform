import axios from 'axios'
import store from '../../store'

export default async(data) => {
    let responseData = {};
    var defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    var url = process.env.VUE_APP_API_ENDPOINT + "app/story";

    await axios({
        url: url,
        method: 'POST',
        data,
        headers: {
            'X-localization': defaultLanguage,
            'Content-Type': 'multipart/form-data'
        }
    })
      .then((response) => {
          responseData.error = false;
          responseData.data = response.data.data.story_id
          responseData.message = response.data.message;
          document.body.classList.remove("loader-enable");
      })
      .catch(function(error) {
          if (error.response.data.errors[0].message) {
              responseData.error = true;
              responseData.message = error.response.data.errors[0].message;
          }
          document.body.classList.remove("loader-enable");
      });
    return responseData;
}