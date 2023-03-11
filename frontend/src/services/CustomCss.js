import axios from "axios";

export default async() => {
    let apiUrl = process.env.VUE_APP_API_ENDPOINT;
    let getDynamicCssUrl = apiUrl + "app/custom-css";
    await axios.get(getDynamicCssUrl).then(({data: {data: {custom_css = false}}}) => {
      // Reject the promise if no custom css defined
      if (!custom_css) {
        return Promise.reject();
      }

      // Add the CSS to the page
      document
        .getElementById("customCss")
        .setAttribute("href", `${custom_css}?v=2`);

      return Promise.resolve();
    });
};