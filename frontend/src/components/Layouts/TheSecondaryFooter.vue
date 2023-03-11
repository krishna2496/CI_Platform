<template>
    <div class="primary-footer">
        <b-container>
            <div class="cookies-block" v-bind:class="{
                    'hidden' : isCookieHidden
                }">
                <div class="container">
                    <div class="text-wrap" v-html="cookiePolicyText">
                    </div>
                    <b-button class="btn-bordersecondary">
                        <span>{{ languageData.label.i_agree }}</span>
                    </b-button>
                </div>
                <i class="close" title="Close">
                    <img :src="$store.state.imagePath+'/assets/images/cross-ic-white.svg'" alt="cross-ic" />
                </i>
            </div>
            <b-row>
                <b-col md="6" class="footer-menu">
                    <b-list-group v-if="isDynamicFooterItemsSet">
                        <b-list-group-item v-for="(item, key) in footerItems" v-bind:key=key
                                           :to="{ path: '/'+item.slug}" :title="getTitle(item)" @click.native="clickHandler">
                            {{getTitle(item)}}
                        </b-list-group-item>
                        <b-list-group-item @click="showModal" href="javascript:void(0)" v-if="contactUsDisplay">
                            {{ languageData.label.contact_us }}
                        </b-list-group-item>
                    </b-list-group>
                </b-col>
                <b-col md="6" class="copyright-text">
                    <p>© {{year}} Optimy.com. {{ languageData.label.all_rights_reserved }}.</p>
                    <div class="lang-drodown-wrap">
                        <AppCustomDropdown :optionList="langList" :defaultText="defautLang.toUpperCase()"
                                           translationEnable="false" @updateCall="setLanguage" />
                    </div>
                </b-col>

            </b-row>

            <b-modal @hidden="hideModal" ref="contactModal" :modal-class="'contact-modal'" hide-footer centered>
                <template slot="modal-header" slot-scope="{ close }">
                    <i class="close" @click="close()" v-b-tooltip.hover :title="languageData.label.close"></i>
                    <h5 class="modal-title">{{ languageData.label.contact_us }}</h5>
                </template>
                <b-alert show :variant="classVariant" dismissible v-model="showDismissibleAlert">{{ message }}</b-alert>
                <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active ': isAjaxCall}">
                    <div class="content-loader"></div>
                </div>
                <b-form>
                    <b-form-group>
                        <label for>{{ languageData.label.name }}</label>
                        <b-form-input id type="text"
                                      v-model.trim="contactUs.name"
                                      maxLength="128"
                                      :placeholder="languageData.placeholder.name"
                                      class="disabled"
                        ></b-form-input>
                    </b-form-group>
                    <b-form-group>
                        <label for>{{ languageData.label.email_address }}</label>
                        <b-form-input id type="text" :placeholder="languageData.placeholder.email_address"
                                      v-model.trim="contactUs.email"
                                      maxLength="128"
                                      class="disabled"></b-form-input>
                    </b-form-group>
                    <b-form-group>
                        <label for>{{ languageData.label.subject }}</label>
                        <b-form-input id
                                      v-model.trim="contactUs.subject"
                                      maxLength="255"
                                      :class="{ 'is-invalid': submitted && $v.contactUs.subject.$error }"
                                      type="text" :placeholder="languageData.placeholder.subject">
                        </b-form-input>
                        <div v-if="submitted && !$v.contactUs.subject.required" class="invalid-feedback">
                            {{ languageData.errors.subject_required }}
                        </div>
                    </b-form-group>
                    <b-form-group>
                        <label for>{{ languageData.label.message }}</label>
                        <b-form-textarea id :placeholder="languageData.placeholder.message" size="lg" rows="5"
                            v-model.trim="contactUs.message"
                            :class="{ 'is-invalid': submitted && $v.contactUs.message.$error }"></b-form-textarea>
                        <div v-if="submitted && !$v.contactUs.message.required" class="invalid-feedback">
                            {{ languageData.errors.message_required }}
                        </div>
                    </b-form-group>
                    <div class="btn-wrap">
                        <b-button class="btn-borderprimary"  @click="$refs.contactModal.hide()">
                            {{ languageData.label.cancel }}
                        </b-button>
                        <b-button class="btn-bordersecondary" v-bind:class="{disabled : isAjaxCall}" @click="submitContact">
                            {{ languageData.label.send }}</b-button>
                    </div>
                </b-form>
            </b-modal>

        </b-container>
    </div>
</template>

<script>
  import store from '../../store';
  import {
    cmsPages,
    cookieAgreement,
    contactUs,
    loadLocaleMessages,
    policy
  } from "../../services/service";
  import constants from '../../constant';
  import AppCustomDropdown from '../../components/AppCustomDropdown';
  import {
    required,
    email,
    numeric,
    minLength
  } from 'vuelidate/lib/validators';
  export default {
    components: {
      AppCustomDropdown
    },
    name: "TheSecondaryFooter",
    data() {
      return {
        footerItems: [],
        isDynamicFooterItemsSet: false,
        year: new Date().getFullYear(),
        languageData: [],
        isCookieHidden: true,
        cookiePolicyText: '',
        submitted: false,
        message : '',
        contactUs: {
          'name': '',
          'email': '',
          'subject': '',
          'message': ''
        },
        classVariant : '',
        showDismissibleAlert : false,
        contactUsDisplay:true,
        isAjaxCall : false,
        langList: [],
        defautLang: '',
      };
    },
    validations: {
      contactUs: {
        message: {
          required
        },
        subject: {
          required
        }
      }
    },
    created() {
      this.languageData = JSON.parse(store.state.languageLabel);
      // Fetching footer CMS pages
      this.getPageListing();
      this.footerAdj();
      this.contactUsDisplay = this.settingEnabled(constants.CONTACT_US);
      if(!this.contactUsDisplay) {
        this.contactUsDisplay = false
      }
      if (store.state.cookieAgreementDate == '' || store.state.cookieAgreementDate == null) {
        this.isCookieHidden = false;
      }
      if(!store.state.isLoggedIn) {
        this.isCookieHidden = true;
        this.contactUsDisplay = false
      }
      this.langList = JSON.parse(store.state.listOfLanguage)
      this.defautLang = store.state.defaultLanguage
      setTimeout(() => {
        let closeCookies = document.querySelector('.cookies-block .close');
        let agreeBtn = document.querySelector('.cookies-block .btn');
        let cookiesBlock = document.querySelector('.cookies-block');

        agreeBtn.addEventListener('click', () => {
          cookiesBlock.classList.add('hidden')
          this.agreeCookie();
        })

        closeCookies.addEventListener('click', () => {
          cookiesBlock.classList.add('hidden')
          this.hideCookieBlock();
        })
      })

      let cookiePolicyTextArray = JSON.parse(store.state.cookiePolicyText)
      if (cookiePolicyTextArray) {
        cookiePolicyTextArray.filter((data, index) => {
          if (data.lang == store.state.defaultLanguage.toLowerCase()) {
            this.cookiePolicyText = data.message
          }
        })
      }

      if(store.state.isLoggedIn == true) {
        this.contactUs.name = store.state.firstName+' '+store.state.lastName
        this.contactUs.email = store.state.email
      }
      window.addEventListener("resize", this.footerAdj);
    },
    methods: {
      async getPageListing() {
        await cmsPages().then(response => {
          this.footerItems = response;
          this.isDynamicFooterItemsSet = true;
        })
      },

      getTitle(items) {
        //Get title according to language
        items = items.pages;
        if (items) {
          let filteredObj = items.filter((item) => {
            if (item.language_id == store.state.defaultLanguageId) {
              return item;
            }
          });
          if (filteredObj[0]) {
            return filteredObj[0].title
          }
        }
      },

      getUrl(items) {
        if (items) {
          return items.slug
        }
      },

      clickHandler() {
        this.$emit('cmsListing', this.$route.params.slug);
      },

      footerAdj() {
        if (document.querySelector("footer") != null) {
          let footerH = document.querySelector("footer").offsetHeight;
          document.querySelector("footer").style.marginTop = -footerH + "px";
          document.querySelector(".inner-pages").style.paddingBottom =
            footerH + "px";
        }
      },
      async setLanguage(language) {
        this.defautLang = language.selectedVal;
        store.commit('setDefaultLanguage', language);
        this.$i18n.locale = language.selectedVal.toLowerCase()
        await loadLocaleMessages(this.$i18n.locale);
        if (store.state.userId) {
          // only call policy page listing when user is logged in
          this.setPolicyPage();
        } else {
          location.reload();
        }
      },
      setPolicyPage() {
        policy().then(response => {
          if (response.error == false) {
            if(response.data.length > 0) {
              store.commit('policyPage', response.data);
              location.reload();
              return;
            }
          }
          store.commit('policyPage', null);
          location.reload();
        });
      },
      agreeCookie() {
        let data = {
          "agreement": true
        }
        cookieAgreement(data).then(response => {
          this.hideCookieBlock();
        })
      },

      hideCookieBlock() {
        this.$store.commit('removeCookieBlock');
      },
      showModal() {
        this.$refs.contactModal.show()
      },

      submitContact() {
        this.submitted = true;
        this.$v.$touch();
        if (this.$v.$invalid) {
          return
        }
        this.isAjaxCall = true;
        let contactData = {
          'subject' : '',
          'message' : '',
          'admin' : null
        }
        contactData.subject = this.contactUs.subject;
        contactData.message = this.contactUs.message;
        contactUs(contactData).then(response => {
          this.showDismissibleAlert = true
          this.isAjaxCall = false;
          if(response.error == false) {
            this.classVariant = 'success';
            this.message = response.message
            setTimeout(() => {
              this.$refs.contactModal.hide()
            }, 800);
          } else {
            this.classVariant = 'danger';
            this.message = response.message
            this.contactUs.subject =  ''
            this.contactUs.message =  ''
          }
        })
      },
      hideModal() {
        this.showDismissibleAlert = false
        this.submitted = false;
        this.$v.$reset();
        this.contactUs.message = '';
        this.contactUs.subject = '';
      },
    },
    updated() {
      this.footerAdj();
    }
  };
</script>