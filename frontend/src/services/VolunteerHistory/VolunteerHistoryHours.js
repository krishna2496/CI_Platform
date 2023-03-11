import axios from "axios";
import store from "../../store";

export default async(type = "theme", year = "") => {
    let responseData = [];
    let defaultLanguage = "";
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = store.state.defaultLanguage.toLowerCase();
    }
    let url = `${process.env.VUE_APP_API_ENDPOINT}app/volunteer/history/${type}`;
    url += year === "" ? "" : `?year=${year}`;
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