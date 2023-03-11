import "core-js/shim";
import "regenerator-runtime/runtime";

import Vue from "vue";
import VueScrollTo from "vue-scrollto";
import BootstrapVue from "bootstrap-vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import SimpleBar from "simplebar";
import "simplebar/dist/simplebar.css";
import axios from "axios";
import VueAxios from "vue-axios";
import Vuelidate from "vuelidate";
import interceptorsSetup from "./interceptors";
import toast from "./toast";
import i18n from "./i18n";
import AOS from "aos";
import "aos/dist/aos.css";
import BackToTop from "vue-backtotop";
import moment from 'moment'
import 'moment-timezone';
import customCss from './services/CustomCss'
import 'vue-search-select/dist/VueSearchSelect.css'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import VueSanitize from 'vue-sanitize';

Vue.use(Vuelidate, VueAxios, axios);
Vue.config.devtools = process.env.NODE_ENV !== 'production';
Vue.config.productionTip = false;
Vue.use(BootstrapVue);
Vue.use(VueScrollTo);
Vue.use(BackToTop);
Vue.use(toast);

Vue.use(VueSanitize, {
  allowedTags: VueSanitize.defaults.allowedTags.concat(['img']),
  allowedAttributes: {
    '*': [
      'style',
      'border',
      'cellpadding',
      'cellspacing',
      'title',
      'href',
      'src',
      'name',
      'alt'
    ]
  }
});

AOS.init({
    once: true,
    easing: "ease-in-out",
    duration: 700,
    offset: 0
});
export const eventBus = new Vue();
// call vue axios interceptors
interceptorsSetup();
let entryUrl = null;

// check requirment of authentication for path
router.beforeEach(async(to, from, next) => {
    if (store.state.isLoggedIn) {
        if(store.state.isProfileComplete != 1) {
           if(to.path != '/my-account' && to.path !== '/auth/slo') {
                next({
                    name: "myAccount"
                });
                return;
           }
        }
    }
    if (store.state.isLoggedIn) {
        if (entryUrl) {
            const url = entryUrl;
            entryUrl = null;
            return next(url); // goto stored url
        }
    }
    if (to.meta.requiresAuth && !store.state.isLoggedIn) {
        if (store.state.samlSettings
          && store.state.samlSettings.saml_access_only
        ) {
          window.location.href = store.state.samlSettings.sso_url;
          return;
        }

        entryUrl = to.path;
        const redirect = `${window.location.origin}${entryUrl}`;
        next({
            name: "login",
            query: {
                'returnUrl': redirect
            }
        });
        return;
    }

    // check for required tenant settings
    if (to.meta.requiredSettings && to.meta.requiredSettings.length) {
        const settings = JSON.parse(store.state.tenantSetting);
        if (settings) {
            if (!to.meta.requiredSettings.every(setting => settings.indexOf(setting) !== -1)) {
                return next({
                    name: 'home'
                });
            }
        }
    }

    if ((to.path === "/" || to.path === "/forgot-password" || to.path === "/reset-password") &&
        store.state.isLoggedIn) {
        next({
            name: "home"
        });
        return;
    }
    next();
});
router.afterEach((to) => {
    if (to.path == '/') {
        setTimeout(() => {
            document.body.classList.remove("loader-enable");
        }, 500);
    }
})
Vue.filter('formatDate', (value) => {
    if (value) {
        return moment(String(value)).format('DD/MM/YYYY');
    }
})

Vue.filter('formatStoryDate', (value) => {
    return moment(value, 'DD/MM/YYYY HH:mm:ss').format('DD/MM/YYYY');
})

Vue.filter('formatDateTime', (value) => {
    return moment(String(value)).format('DD/MM/YYYY, LT');
})



Vue.filter('filterGoal', (value) => {
    return parseInt(value);
})

Vue.filter('formatTime', (value) => {
    return moment(String(value)).format('LT');
})

Vue.filter('firstLetterCapital', (value) => {
    if (value) {
        value = value.toLowerCase();
        return value.charAt(0).toUpperCase() + value.slice(1);
    }
})

Vue.filter('firstLetterSmall', (value) => {
    if (value) {
        return value.toLowerCase();
    }
})


Vue.filter('substring', (value, data) => {
    if (typeof value !== 'string'
      && typeof value.toString === 'function'
    ) {
      value = value.toString();
    }

    if (value.length <= data) {
        return value;
    } else {
        return value.substring(0, data) + "...";
    }
});

Vue.filter('substringWithOutDot', (value, data) => {
    if (typeof value !== 'string'
      && typeof value.toString === 'function'
    ) {
      value = value.toString();
    }

    if (value.length <= data) {
        return value;
    } else {
        return value.substring(0, data);
    }
});

window.addEventListener('storage', function (e) {
    if (event.key === 'logout-event') {
        location.reload();
    }
},false);

Vue.mixin({
    methods: {
        settingEnabled(key) {
            const settingArray = JSON.parse(store.state.tenantSetting)
            if (settingArray != null) {
                if (settingArray.indexOf(key) !== -1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
})

new Vue({
    router,
    store,
    BootstrapVue,
    SimpleBar,
    VueScrollTo,
    i18n,
    AOS,
    toast,
    BackToTop,
    render: h => h(App)
}).$mount("#app");
