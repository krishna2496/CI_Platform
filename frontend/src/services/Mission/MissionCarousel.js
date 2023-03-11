import store from '../../store'
import axios from 'axios'

export default async(missionId) => {
  let responseData = {};
  let url = process.env.VUE_APP_API_ENDPOINT + "app/mission-media/" + missionId
  await axios({
    url: url,
    method: 'get',
    headers: {
    }
  }).then((response) => {
    responseData.error = false;
    if (response.data.data) {
      responseData.data = response.data.data;
    } else {
      responseData.data = [];
      responseData.pagination = [];
    }

  })
    .catch(function() {
      responseData.error = true;
    });
  return responseData;
}