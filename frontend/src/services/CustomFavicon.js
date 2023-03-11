import axios from "axios";

export default async() => {
    let apiUrl = process.env.VUE_APP_API_ENDPOINT;
    let getDynamicFaviconUrl = apiUrl + "app/custom-favicon";
    await axios.get(getDynamicFaviconUrl).then(({data: {data: {custom_favicon = null}}}) => {
        // Reject the promise if no custom favicon defined
        if (!custom_favicon) {
            return Promise.reject();
        }

        // Replace the favicon of the page
        // (adding the 'v' parameter to avoid retrieving cached favicon after a favicon update)
        document
            .getElementById("favicon")
            .setAttribute("href", custom_favicon + '?v=' + Date.now().toString());

        return Promise.resolve();
    }).catch((error) => {
        // Reject the promise if favicon not found
        return Promise.reject(error);
    });
};