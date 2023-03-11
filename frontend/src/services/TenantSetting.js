import axios from 'axios'
import store from '../store'

export default async() => {
    let responseData;
    let url = process.env.VUE_APP_API_ENDPOINT + "app/tenant-settings";

    await axios({
        url: url,
        method: 'get',
    })
      .then((response) => {
          let settingArray = [];
          if (response.data.data) {
              let responseDataArray = response.data.data;
              responseDataArray.filter((module, index) => {
                  settingArray[index] = module.key
              });

              responseData = response.data.data;

          } else {
              settingArray = null
          }
          store.commit("setTenantSetting", settingArray);

      })
      .catch(function() {});
    return responseData;
}