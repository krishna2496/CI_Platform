<template>
<div class="signin-page-wrapper" v-if="isPageShown">
    <TheSlider v-if="isShowComponent" />
    <div class="signin-form-wrapper"
      :class="{ 'custom-text-wrap' : customText !== ''}">
        <div class="lang-drodown-wrap">
            <AppCustomDropdown :optionList="langList"
              :defaultText="defautLang"
              translationEnable="false"
              @updateCall="setLanguage"
              v-if="isShowComponent" />
        </div>
        <div class="signin-form-block">
            <div v-if="customText != '' && customTextPosition == 'before_logo'"
                class="custom-text-block">
                <p v-html="customText"></p>
            </div>

            <router-link to="/" class="logo-wrap" v-if="this.$store.state.logo">
                <img :src="this.$store.state.logo">
            </router-link>

            <div v-if="customText != '' && customTextPosition == 'after_logo'"
                class="custom-text-block">
                <p v-html="customText"></p>
            </div>

            <b-alert v-if="this.$store.state.samlSettings && this.$store.state.samlSettings.saml_access_only" />
            <div v-else>
                <!-- success or error msg -->
                <b-alert show :variant="classVariant" dismissible v-model="showDismissibleAlert">{{ message }}</b-alert>
                <!-- login form start -->
                <b-form class="signin-form">
                    <b-form-group>
                        <label for="">{{ languageData.label.email_address }}</label>
                        <b-form-input id=""
                            type="email"
                            v-model="login.email"
                            v-bind:placeholder='languageData.placeholder.email_address'
                            :class="{ 'is-invalid': $v.login.email.$error }"
                            ref="email"
                            autofocus
                            maxlength="120"
                            @keypress.enter.prevent="handleSubmit"
                            @keydown.space.prevent>
                        </b-form-input>
                        <div v-if="submitted && !$v.login.email.required" class="invalid-feedback">
                            {{ languageData.errors.email_required }}</div>
                        <div v-if="submitted && !$v.login.email.email" class="invalid-feedback">
                            {{ languageData.errors.invalid_email }}</div>
                    </b-form-group>
                    <b-form-group>
                        <label for="">{{ languageData.label.password }}</label>
                        <b-form-input id=""
                            type="password"
                            v-model="login.password"
                            required
                            :placeholder="languageData.placeholder.password"
                            :class="{ 'is-invalid': $v.login.password.$error }"
                            maxlength="120"
                            @keypress.enter.prevent="handleSubmit"
                            @keydown.space.prevent autocomplete="password">
                        </b-form-input>
                        <div v-if="submitted && !$v.login.password.required" class="invalid-feedback">
                            {{ languageData.errors.password_required }}</div>
                    </b-form-group>
                    <b-button
                        type="button"
                        @click="handleSubmit"
                        class=" btn-bordersecondary">
                        {{ languageData.label.login }}
                    </b-button>
                </b-form>
                <!-- link to forgot-password -->
                <div class="form-link">
                    <b-link to="/forgot-password">{{ languageData.label.lost_password }}</b-link>
                </div>
            </div>

            <b-button
                type="button"
                v-if="hasSSO"
                @click="handleSSO"
                class=" btn-borderprimary mt-3">
                {{ languageData.label.login_with_sso || 'Login with SSO' }}
            </b-button>

            <div v-if="customText != '' && customTextPosition == 'after_login_form'"
                class="custom-text-block">
                <p v-html="customText"></p>
            </div>

        </div>
        <ThePrimaryFooter ref="ThePrimaryFooter" v-if="isShowComponent" :key="componentKey" />
    </div>
</div>
</template>

<script>
  import TheSlider from '../../components/TheSlider';
  import ThePrimaryFooter from '../../components/Layouts/ThePrimaryFooter';
  import AppCustomDropdown from '../../components/AppCustomDropdown';
  import {
    required,
    email
  } from 'vuelidate/lib/validators';
  import store from '../../store';
  import {
    loadLocaleMessages,
    login,
    databaseConnection,
    tenantSetting,
    policy
  } from '../../services/service';
  import { setSiteTitle } from '../../utils';

  export default {
    components: {
      ThePrimaryFooter,
      AppCustomDropdown,
      TheSlider,
    },

    data() {
      return {
        flag: false,
        myValue: '',
        defautLang: '',
        langList: [],
        login: {
            email: '',
            password: '',
        },
        submitted: false,
        classVariant: 'danger',
        message: null,
        showDismissibleAlert: false,
        isShowComponent: false,
        languageData: [],
        isPageShown: false,
        componentKey: 0,
        customText: '',
        customTextPosition: 'before_logo'
      };
    },

    validations: {
      login: {
        email: {
          required,
          email
        },
        password: {
          required
        }
      }
    },

    methods: {
      async createConnection() {
        await databaseConnection(this.langList).then(() => {
          this.isShowComponent = true
          // Get langauage list from Local Storage
          this.langList = JSON.parse(store.state.listOfLanguage)
          const defaultLanguage = store.state.defaultLanguage;
          this.defautLang = defaultLanguage.toUpperCase();
          this.hasSSO = Boolean(store.state.samlSettings);
          this.setCustomText();

          // Get tenant setting
          tenantSetting();
          loadLocaleMessages(store.state.defaultLanguage).then(() => {
            this.languageData = JSON.parse(store.state.languageLabel);
            setSiteTitle();
            this.isPageShown = true
            setTimeout(() => {
              if (this.$refs.email) {
                this.$refs.email.focus();
              }
            }, 500)
          });
        })
      },

      async setLanguage(language){
        this.defautLang = language.selectedVal;
        store.commit('setDefaultLanguage',language);
        this.$i18n.locale = language.selectedVal.toLowerCase()
        await loadLocaleMessages(this.$i18n.locale);
        this.languageData = JSON.parse(store.state.languageLabel);
        this.$forceUpdate();
        this.$refs.ThePrimaryFooter.$forceUpdate()
        this.componentKey += 1;
        setSiteTitle();
        this.setCustomText();
      },

      handleSubmit() {
        this.submitted = true;
        this.$v.$touch();
        // stop here if form is invalid
        if (this.$v.$invalid) {
          return;
        }
        // Call to login service with params email address and password
        login(this.login).then( response => {
          if (response.error === true) {
            this.message = null;
            this.showDismissibleAlert = true
            this.classVariant = 'danger'
            //set error msg
            this.message = response.message
          } else {
            // redirect to landing page
            if (this.$route.query.returnUrl) {
              this.$router.back();
              return;
            }
            this.$router.replace({
              name: 'home'
            }).catch(() => {});
          }
        });
      },

      handleSSO() {
        window.location = store.state.samlSettings.sso_url;
      },

      setCustomText() {
        const customTextArray = JSON.parse(store.state.customLoginText)
        if (customTextArray.position !== '' && customTextArray.position != null) {
          this.customTextPosition = customTextArray.position;
        }
        const translations = customTextArray.translations;
        if (!translations || !Array.isArray(translations)) {
          return;
        }

        const translatedCustomText = translations.find((item) => {
          return item.lang.toLowerCase() === store.state.defaultLanguage.toLowerCase();
        });
        if (translatedCustomText && translatedCustomText.message) {
          this.customText = this.$sanitize(translatedCustomText.message);
          return;
        }

        // get custom text for default language if no translation is found
        const customTextInDefaultLang = translations.find((item) => {
          return item.lang.toLowerCase() === store.state.defaultTenantLanguage.toLowerCase();
        });
        if (customTextInDefaultLang && customTextInDefaultLang.message) {
          this.customText = this.$sanitize(customTextInDefaultLang.message);
        }
      }
    },

    created() {
      // Database connection and fetching tenant options api
      this.createConnection();
    },

    beforeCreate() {
      document.body.classList.remove('loader-enable');
    }
  };
</script>
