import axios from 'axios';

export default async(userEmail) => {

    let responseData = {}
    responseData.error = false;

    await axios.get(`${process.env.VUE_APP_API_ENDPOINT}app/get-user-language?email=${userEmail}`)
      .then((response) => {
          responseData = response.data;
      }).catch((error) => {
          if (error.response.data.errors[0].message) {
              responseData.error = true;
              responseData.message = error.response.data.errors[0].message;
          }
      });
    return responseData;
}