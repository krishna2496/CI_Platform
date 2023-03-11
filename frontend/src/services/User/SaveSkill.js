import axios from 'axios'
import store from '../../store'

export default async(data) => {
    let skills = {
        skills: []
    }

    if (data.length > 0) {
        Object.keys(data).map(function(key) {
            skills['skills'].push({
                skill_id: data[key].id,
            });
        });
    }


    let responseData = {};
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    let url = process.env.VUE_APP_API_ENDPOINT + "app/user/skills";
    await axios({
        url: url,
        method: 'POST',
        data: skills,
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