import store from "../store";
import axios from "axios";

export default async(exportUrl, fileName) => {
    let url = `${process.env.VUE_APP_API_ENDPOINT}${exportUrl}`;
    let defaultLanguage = '';
    if (store.state.defaultLanguage !== null) {
        defaultLanguage = (store.state.defaultLanguage).toLowerCase();
    }
    await axios({
        url: url,
        responseType: "arraybuffer",
        method: "get",
        headers: {
            'X-localization': defaultLanguage,
        }
    }).then(response => {

        let blob = new Blob([response.data], { type: "application/xlsx" });

        if (navigator.appVersion.toString().indexOf('.NET') > 0) {
            window.navigator.msSaveBlob(blob, fileName);
        } else {

            let link = document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
            // Add the element to the DOM
            link.setAttribute("type", "hidden"); // make it hidden if needed
            link.download = fileName;
            link.href = URL.createObjectURL(blob);
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    });
};