import axios from 'axios'
import i18n from "../../i18n"
import store from '../../store'

const loadedLanguages = []

function setI18nLanguage(lang) {
    i18n.locale = lang
    axios.defaults.headers.common['X-localization'] = lang
    document.querySelector('html').setAttribute('lang', lang)
    return lang;
}

export default async(lang) => {

    if (lang) {
        lang = lang.toLowerCase();
    }

    if ((lang != 'null')) {
        await axios.get(`${process.env.VUE_APP_LANGUAGE_API_ENDPOINT + lang}`, {
            method: "get",
        }).then(function(res) {
            store.commit('setlanguageLabel', res.data.data);
            if (res.data) {
                i18n.setLocaleMessage(
                  res.data.locale,
                  res.data.data
                );
                loadedLanguages.push(res.data.locale)
                return Promise.resolve(setI18nLanguage(res.data.locale))
            }
        })
    }
}