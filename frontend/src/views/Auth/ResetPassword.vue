<template>
    <div class="signin-page-wrapper">
        <TheSlider v-if="isShowSlider" />
        <div class="signin-form-wrapper">
            <div class="lang-drodown-wrap">
                <AppCustomDropdown :optionList="langList" :defaultText="defautLang" translationEnable="false"
                                   @updateCall="setLanguage" />
            </div>
            <div class="signin-form-block">
                <router-link :to="{ name: 'login' }" class="logo-wrap" v-if="this.$store.state.logo">
                    <img :src="this.$store.state.logo">
                </router-link>
                <div class="form-title-block">
                    <h1>{{ languageData.label.new_password }}</h1>
                    <p>{{ languageData.label.new_password_message }}</p>
                </div>
                <!-- success or error msg -->
                <b-alert show :variant="classVariant" dismissible v-model="showDismissibleAlert"> {{ message }}
                </b-alert>
                <!-- reset password form start -->
                <b-form class="signin-form">
                    <b-form-group>
                        <label>{{ languageData.label.new_password }}</label>
                        <b-form-input id="" type="password" v-model="resetPassword.password"
                                      :class="{ 'is-invalid': $v.resetPassword.password.$error }" value="Password" maxlength="120"
                                      v-bind:placeholder='languageData.placeholder.password' autofocus @keydown.space.prevent autocomplete="password">
                        </b-form-input>
                        <div v-if="submitted && !$v.resetPassword.password.required" class="invalid-feedback">
                            {{ languageData.errors.password_required }}
                        </div>
                        <div v-else-if="submitted && !$v.resetPassword.password.minLength" class="invalid-feedback">
                            {{ languageData.errors.invalid_password }}
                        </div>
                        <div v-else-if="submitted && !$v.resetPassword.password.containsUpperCase" class="invalid-feedback">
                            {{ languageData.errors.password_should_contain_uppercase }}
                        </div>
                        <div v-else-if="submitted && !$v.resetPassword.password.containsLowerCase" class="invalid-feedback">
                            {{ languageData.errors.password_should_contain_lowercase }}
                        </div>
                        <div v-else-if="submitted && !$v.resetPassword.password.containsNumber" class="invalid-feedback">
                            {{ languageData.errors.password_should_contain_numbers }}
                        </div>
                    </b-form-group>
                    <b-form-group>
                        <label>{{ languageData.label.confirm_new_password }}</label>
                        <b-form-input id="" type="password" v-model="resetPassword.confirmPassword"
                                      :class="{ 'is-invalid': $v.resetPassword.confirmPassword.$error }" maxlength="120"
                                      v-bind:placeholder='languageData.placeholder.password'
                                      @keypress.enter.prevent="handleSubmit" value="Password" @keydown.space.prevent autocomplete="password">
                        </b-form-input>
                        <div v-if="submitted && !$v.resetPassword.confirmPassword.required" class="invalid-feedback">
                            {{ languageData.errors.password_required }}
                        </div>
                        <div v-if="submitted && !$v.resetPassword.confirmPassword.minLength" class="invalid-feedback">
                            {{ languageData.errors.invalid_password }}
                        </div>
                        <div v-if="submitted && $v.resetPassword.confirmPassword.required && $v.resetPassword.confirmPassword.minLength && !$v.resetPassword.confirmPassword.sameAsPassword"
                             class="invalid-feedback">
                            {{ languageData.errors.identical_password }}
                        </div>
                    </b-form-group>
                    <b-button type="button" @click="handleSubmit" class="btn btn-bordersecondary">
                        {{ languageData.label.change_password }}
                    </b-button>
                </b-form>
                <div class="form-link">
                    <b-link to="/">{{ languageData.label.login }}</b-link>
                </div>
            </div>
            <ThePrimaryFooter ref="ThePrimaryFooter" />
        </div>
    </div>
</template>

<script>
  import TheSlider from '../../components/TheSlider';
  import ThePrimaryFooter from "../../components/Layouts/ThePrimaryFooter";
  import AppCustomDropdown from '../../components/AppCustomDropdown';
  import store from '../../store';
  import {
    required,
    sameAs,
    minLength
  } from 'vuelidate/lib/validators';
  import {
    loadLocaleMessages,
    resetPassword,
    getUserLanguage,
    databaseConnection,
    tenantSetting
  } from '../../services/service';

  import constants from '../../constant';

  export default {
    components: {
      TheSlider,
      ThePrimaryFooter,
      AppCustomDropdown,
    },

    data() {
      return {
        isShowSlider: false,
        myValue: '',
        defautLang: "",
        langList: [],
        resetPassword: {
          email: '',
          password: '',
          confirmPassword: '',
          token: '',
        },
        submitted: false,
        classVariant: 'danger',
        message: null,
        showDismissibleAlert: false,
        languageData: [],
      };
    },

    validations: {
      resetPassword: {
        password: {
          required,
          minLength: minLength(constants.PASSWORD_MIN_LENGTH),
          containsUpperCase: function(value) {
            return /(?=.*[A-Z])/.test(value);
          },
          containsLowerCase: function(value) {
            return /(?=.*[a-z])/.test(value);
          },
          containsNumber: function(value) {
            return /(?=.*[0-9])/.test(value);
          }
        },
        confirmPassword: {
          required,
          minLength: minLength(constants.PASSWORD_MIN_LENGTH),
          sameAsPassword: sameAs('password')
        }
      }
    },

    methods: {
      async setLanguage(language) {
        this.defautLang = language.selectedVal;
        store.commit('setDefaultLanguage', language);
        this.$i18n.locale = language.selectedVal.toLowerCase()
        await loadLocaleMessages(this.$i18n.locale);
        this.languageData = JSON.parse(store.state.languageLabel);
        this.$forceUpdate();
        this.$refs.ThePrimaryFooter.$forceUpdate()
      },
      async createConnection() {
        await databaseConnection(this.langList).then(() => {
          this.isShowComponent = true
          //Get langauage list from Local Storage
          this.langList = JSON.parse(store.state.listOfLanguage)
          this.defautLang = store.state.defaultLanguage.toUpperCase();

          // Get tenant setting
          tenantSetting();

          this.fetchUserLanguage(this.$route.query.email);
          this.isShowSlider = true;
          loadLocaleMessages(store.state.defaultLanguage).then(() => {
            this.languageData = JSON.parse(store.state.languageLabel);
          });
        })
      },
      async fetchUserLanguage(email) {

        let defaultLanguageData = [];
        let response = await getUserLanguage(email);
        let languageCode = '';

        if (typeof response.error === "undefined") {

          languageCode = this.langList.filter( (language) => {
            if (language['0'] == response.data.default_language_id) {
              return language;
            }
          });

          defaultLanguageData["selectedVal"] = languageCode[0][1];
          defaultLanguageData["selectedId"] = response.data.default_language_id;

          this.defautLang = languageCode[0][1];

          store.commit('setDefaultLanguage', defaultLanguageData)

          this.$i18n.locale = languageCode[0][1].toLowerCase()
          await loadLocaleMessages(this.$i18n.locale);
        }

      },
      handleSubmit() {
        this.submitted = true;
        this.$v.$touch();
        // stop here if form is invalid
        if (this.$v.$invalid) {
          return;
        }

        let resetPasswordData = {};
        resetPasswordData.reset_password_token = this.resetPassword.token;
        resetPasswordData.email = this.resetPassword.email;
        resetPasswordData.password = this.resetPassword.password;
        resetPasswordData.password_confirmation = this.resetPassword.confirmPassword;

        // Call to Reset Password service with params token,email,password,password_conformation
        resetPassword(resetPasswordData).then(response => {
          if (response.error === true) {
            this.message = null;
            this.showDismissibleAlert = true
            this.classVariant = 'danger'
            //set error msg
            this.message = response.message
          } else {
            this.message = null;
            this.showDismissibleAlert = true
            this.classVariant = 'success'
            //set success msg
            this.message = response.message
            //Reset to blank
            this.submitted = false;
            this.resetPassword.password = ''
            this.resetPassword.confirmPassword = ''
            this.$v.$reset();
            setTimeout( () => this.$router.replace({name: "login"}), 3000);
          }
        });
      },
    },
    created() {
      this.createConnection();
      this.languageData = JSON.parse(store.state.languageLabel);
      //get token and email from url
      let tokenData = this.$route.path.split('/');

      this.resetPassword.token = tokenData[tokenData.length - 1]
      this.resetPassword.email = this.$route.query.email

      // set language list and default language fetching from local storage
      this.langList = (store.state.listOfLanguage !== null) ? JSON.parse(store.state.listOfLanguage) : []
      this.defautLang = store.state.defaultLanguage;
    }


  }
</script>