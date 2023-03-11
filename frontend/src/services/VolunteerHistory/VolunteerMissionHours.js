import axios from "axios";
import store from "../../store";

export default async(page = 1) => {
    let responseData = [];
    let defaultLanguage = "";
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = store.state.defaultLanguage.toLowerCase();
    }
    let url = `${process.env.VUE_APP_API_ENDPOINT}app/volunteer/history/time-mission?page=${page}`;
    await axios({
        url: url,
        method: "get",
        headers: {
            "X-localization": defaultLanguage,
            token: store.state.token
        }
    })
      .then(response => {
          if (response.data.data !== "undefined") {
              responseData.error = false;
              responseData.message = response.data.message;
              responseData.data = response.data.data;
              responseData.pagination = response.data.pagination;
          } else {
              responseData.error = false;
              responseData.message = response.data.message;
          }
      })
      .catch(function(error) {
          responseData.error = true;
          responseData.message = error.message;
      });
    return responseData;
};