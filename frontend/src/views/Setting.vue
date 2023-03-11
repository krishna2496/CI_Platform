<template>
<div class="profile-page inner-pages donation-profile">
    <header>
        <ThePrimaryHeader v-if="isShownComponent" :key="componentKey"></ThePrimaryHeader>
    </header>
    <main>
        <b-container>
            <b-row class="dashboard-tab-content" v-if="errorPage && pageLoaded">
                <b-col xl="12" lg="12" md="12">
                    <b-alert show variant="danger">
                        {{errorPageMessage}}
                    </b-alert>
                </b-col>
            </b-row>
            <b-row class="is-profile-complete" v-if="isUserProfileComplete != 1">
                <b-col xl="12" lg="12" md="12">
                    <b-alert show variant="warning">
                        {{languageData.label.fill_up_mandatory_fields_to_access_platform}}
                    </b-alert>
                </b-col>
            </b-row>
            <b-row class="profile-content" v-if="showPage && (!errorPage) && pageLoaded">
                <b-col xl="3" lg="4" md="12" class="profile-left-col">
                    <div class="profile-details">
                        <div class="profile-block">
                            <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active ': isPrefilLoaded}">
                                <div class="content-loader"></div>
                            </div>
                            <picture-input :title="changePhoto" ref="pictureInput" @change="changeImage" accept="image/jpeg,image/png" :prefill="newUrl" buttonClass="btn" :customStrings="{
                                        upload: '<h1>Bummer!</h1>',
                                        drag: 'Drag a 😺 GIF or GTFO'
                                    }">
                            </picture-input>
                        </div>
                        <h4>{{$store.state.firstName}} {{$store.state.lastName}}</h4>
                        <b-list-group class="social-nav">

                            <b-list-group-item v-if="linkedInUrl != null && linkedInUrl != ''  ">
                                <b-link :href="linkedInUrl" target="_blank" :title="languageData.label.linked_in" class="linkedin-link">
                                    <img :src="`${$store.state.imagePath}/assets/images/linkedin-ic-blue.svg`" class="normal-img" alt="linkedin img" />
                                    <img :src="`${$store.state.imagePath}/assets/images/linkedin-ic.svg`" class="hover-img" alt="linkedin hover img" />
                                </b-link>
                            </b-list-group-item>
                        </b-list-group>
                    </div>
                    <!-- my account breadcrumb -->
                    <MyAccountDashboardBreadcrumb :key="componentKey"></MyAccountDashboardBreadcrumb>
                </b-col>
                <b-col xl="9" lg="8" md="12" class="profile-form-wrap">
                    <b-form class="profile-form">
                        <b-row class="row-form">
                            <b-col cols="12">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.change_password}}</span>
                                </h2>
                            </b-col>
                            <b-alert show :variant="classletiant" dismissible v-model="showErrorDiv">
                                {{ message }}
                            </b-alert>
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.current_password}}</label>
                                    <b-form-input id
                                        type="password"
                                        ref="oldPassword"
                                        :class="{ 'is-invalid': $v.oldPassword.$error }"
                                        v-model.trim="oldPassword"
                                        :placeholder="languageData.placeholder.old_password">
                                    </b-form-input>
                                    <div v-if="!$v.oldPassword.required" class="invalid-feedback">
                                        {{ languageData.errors.field_is_required }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6"></b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.new_password}}
                                    </label>
                                    <b-form-input id
                                        type="password"
                                        v-model.trim="newPassword"
                                        :class="{ 'is-invalid': $v.newPassword.$error }"
                                        :placeholder="languageData.placeholder.new_password">
                                    </b-form-input>
                                    <div v-if="$v.newPassword.$error" class="invalid-feedback">
                                        <template v-if="!$v.newPassword.required">
                                            {{ languageData.errors.field_is_required }}
                                        </template>
                                        <template v-else-if="!$v.newPassword.minLength">
                                            {{ languageData.errors.invalid_password }}
                                        </template>
                                        <template v-else-if="!$v.newPassword.containsUpperCase">
                                            {{ languageData.errors.password_should_contain_uppercase }}
                                        </template>
                                        <template v-else-if="!$v.newPassword.containsLowerCase">
                                            {{ languageData.errors.password_should_contain_lowercase }}
                                        </template>
                                        <template v-else-if="!$v.newPassword.containsNumber">
                                            {{ languageData.errors.password_should_contain_numbers }}
                                        </template>
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6"></b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label for>
                                        {{ languageData.label.confirm_new_password }}
                                    </label>
                                    <b-form-input id v-model.trim="confirmPassword" :class="{ 'is-invalid': $v.confirmPassword.$error }" :placeholder="languageData.placeholder.confirm_password" @keypress.enter.prevent="changePassword" type="password">
                                    </b-form-input>
                                    <div v-if="!$v.confirmPassword.sameAsPassword" class="invalid-feedback">
                                        {{ languageData.errors.identical_password }}</div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6"></b-col>
                        </b-row>
                        <b-row class="row-form">
                            <b-col cols="12">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.preferences}}</span>
                                </h2>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label>{{languageData.label.language}}*</label>
                                    <CustomFieldDropdown v-model="language" :errorClass="submitted && $v.language.$error" :defaultText="languageDefault" :optionList="languageList" @updateCall="updateLang" translationEnable="false" />
                                    <div v-if="submitted && !$v.language.required" class="invalid-feedback">
                                        {{ languageData.errors.language_required }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6"></b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label>{{languageData.label.timezone}}*</label>
                                    <model-select class="search-dropdown" v-bind:class="{'is-invalid' :submitted && $v.time.$error}" :options="timeList" v-model="time" :placeholder="timeDefault" @input="updateTime">
                                    </model-select>
                                    <div v-if="submitted && !$v.time.required" class="invalid-feedback">
                                        {{ languageData.errors.timezone_required }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6"></b-col>
                            <b-col md="6" v-if="isDonationSettingEnable">
                                <b-form-group>
                                    <label>{{languageData.label.currency}}*</label>
                                    <model-select class="search-dropdown" v-bind:class="{'is-invalid' :submitted && $v.currency.$error}" :options="currencyList" v-model="currency" :placeholder="currencyDefault" @input="updateCurrency">
                                    </model-select>
                                    <div v-if="submitted && !$v.currency.required" class="invalid-feedback">
                                        {{ languageData.errors.currency_required }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6"></b-col>
                        </b-row>
                        <b-row class="row-form">
                            <b-col cols="12">
                                <div class="btn-wrapper">
                                    <b-button class="btn-bordersecondary btn-save" :disabled="isSubmitBtnClick" :title="languageData.label.save" @click="handleSubmit()">{{ languageData.label.save }}
                                    </b-button>
                                </div>
                            </b-col>
                        </b-row>
                    </b-form>
                </b-col>
            </b-row>
        </b-container>
    </main>
    <footer>
        <TheSecondaryFooter v-if="isShownComponent"></TheSecondaryFooter>
    </footer>
</div>
</template>

<script>
import CustomFieldDropdown from "../components/CustomFieldDropdown";
import MultiSelect from "../components/MultiSelect";
import CustomField from "../components/CustomField";
import store from "../store";
import PictureInput from '../components/vue-picture-input'
import {
    ModelSelect
} from 'vue-search-select'
import {
    getUserDetail,
    changeUserPassword,
    changeProfilePicture,
    changeCity,
    saveUserProfile,
    loadLocaleMessages,
    country,
    skill,
    timezone,
    settingListing,
    submitSetting
} from "../services/service";
import {
    required,
    maxLength,
    sameAs,
    minLength,
    requiredIf
} from 'vuelidate/lib/validators';
import constants from '../constant';

export default {
    components: {
        ThePrimaryHeader: () => import("../components/Layouts/ThePrimaryHeader"),
        TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
        CustomFieldDropdown,
        MultiSelect,
        PictureInput,
        CustomField,
        ModelSelect,
        CustomFieldDropdown,
        MyAccountDashboardBreadcrumb: () => import("../components/MyAccountDashboardBreadcrumb")
    },
    data() {
        return {
            isUserProfileComplete: 1,
            languageList: [],
            errorPage: false,
            pageLoaded: false,
            errorPageMessage: false,
            isQuickAccessFilterDisplay: true,
            languageDefault: '',
            timeList: [],
            timeDefault: '',
            countryList: [],
            countryDefault: '',
            isCustomFieldSubmit: false,
            file: "null",
            languageData: [],
            newUrl: '',
            isPrefilLoaded: true,
            prefilImageType: {
                mediaType: ''
            },
            userData: [],
            isShownComponent: false,
            cityList: [],
            cityDefault: '',
            showErrorDiv: false,
            message: null,
            classletiant: "success",
            language: '',
            time: '',
            languageCode: '',
            oldPassword: '',
            newPassword: '',
            confirmPassword: '',
            currency: '',
            is_profile_visible: false,
            public_avatar_and_linkedin: false,
            currency: '',
            time: '',
            submitted: false,
            language: '',
            languageCode: null,
            imageLoader: true,
            changePhoto: '',
            showPage: true,
            saveProfileData: {
                password: '',
                confirm_password: '',
                is_profile_visible: 0,
                public_avatar_and_linkedin: 0,
                language_id: 0,
                timezone_id: 0,
                currency: 0,
                old_password: ''
            },
            currencyList: [],
            currencyDefault: '',
            languageListing : [],
            componentKey : 0,
            linkedInUrl : '',
            isSubmitBtnClick : false,
            isDonationSettingEnable : false
        };
    },
    validations() {
        const rules = {
            oldPassword: {
                required : requiredIf(function(model) {
                    return model.newPassword != ''
                }),
            },
            newPassword: {
                required : requiredIf(function(model) {
                    return model.oldPassword != ''
                }),
                minLength: minLength(constants.PASSWORD_MIN_LENGTH),
                containsUpperCase: function(value) {
                    return value === '' || /(?=.*[A-Z])/.test(value);
                },
                containsLowerCase: function(value) {
                    return value === '' || /(?=.*[a-z])/.test(value);
                },
                containsNumber: function(value) {
                    return value === '' || /(?=.*[0-9])/.test(value);
                }
            },
            confirmPassword: {
                sameAsPassword: sameAs('newPassword')
            },
            language: {
                required
            },
            time: {
                required
            },
        };

        if (this.isDonationSettingEnable) {
            rules.currency = {
                required
            };
        }

        return rules;
    },
    methods: {
        updateLang(value) {
            this.languageDefault = value.selectedVal;
            this.languageCode = value.selectedId;
            this.language = this.languageListing[value.selectedId];
        },
        updateCurrency(value) {
            this.currency = value
        },
        updateTime(value) {
            this.time = value;
        },
        updateCity(value) {
            this.cityDefault = value.selectedVal;
            this.city = value.selectedId;

        },
        updateCountry(value) {
            this.countryDefault = value.selectedVal;
            this.country = value.selectedId;

            this.changeCityData(value.selectedId);
        },

        changeImage(image) {
            this.imageLoader = true;
            let imageData = {}

            imageData.avatar = image;
            changeProfilePicture(imageData).then(response => {
                if (response.error == true) {
                    this.makeToast("danger", response.message);
                } else {
                    this.makeToast("success", response.message);
                    store.commit("changeAvatar", response.data)
                }
                this.imageLoader = false;

            })
        },

        //submit form
        handleSubmit() {

            this.submitted = true;
            this.$v.$touch();
            if (this.$v.$invalid) {
                return;
            }
            this.isSubmitBtnClick = true;
            if (this.oldPassword && this.newPassword && this.oldPassword) {
                this.saveProfileData.password = this.newPassword;
                this.saveProfileData.confirm_password = this.confirmPassword;
                this.saveProfileData.old_password = this.oldPassword;
            }

            this.saveProfileData.is_profile_visible = this.is_profile_visible;
            this.saveProfileData.public_avatar_and_linkedin = this.public_avatar_and_linkedin;
            this.saveProfileData.language_id = this.language;
            this.saveProfileData.timezone_id = this.time;

            if (this.isDonationSettingEnable) {
                this.saveProfileData.currency = this.currency;
            } else {
                delete this.saveProfileData.currency;
            }

            // Call to save profile service
            submitSetting(this.saveProfileData).then(response => {
                if (response.error == true) {
                    this.makeToast('danger', response.message);
                } else {
                    store.commit('setDefaultLanguageCode', this.languageCode)
                    this.showPage = false;
                    this.getSettingListing().then(() => {
                        this.showPage = true;
                        this.oldPassword = '';
                        this.newPassword = '';
                        this.confirmPassword = '';
                        this.saveProfileData.password = '';
                        this.saveProfileData.confirm_password = '';
                        this.saveProfileData.old_password = '';
                        this.$v.$reset();

                        loadLocaleMessages(this.languageCode).then(() => {
                            this.languageData = JSON.parse(store.state.languageLabel);
                            this.makeToast('success', this.languageData.messages.setting_update_success);
                            this.componentKey += 1;
                            this.isShownComponent = true;
                        });
                    });
                }
                this.isSubmitBtnClick = false
            });
        },

        makeToast(variant = null, message) {
            this.$bvToast.toast(message, {
                variant: variant,
                solid: true,
                autoHideDelay: 3000
            })
        },
        alphaNumeric(evt) {
            evt = (evt) ? evt : window.event;
            let keyCode = (evt.which) ? evt.which : evt.keyCode;
            if (!((keyCode >= 48 && keyCode <= 57) ||
                    (keyCode >= 65 && keyCode <= 90) ||
                    (keyCode >= 97 && keyCode <= 122)) &&
                keyCode != 8 && keyCode != 32) {
                evt.preventDefault();
            }
        },
        async getSettingListing() {
            await settingListing().then(response => {
                this.pageLoaded = true;
                if (response.error == true) {
                    this.isShownComponent = true
                    this.errorPage = true
                    this.errorPageMessage = response.message
                } else {
                    this.time = response.data.preference.timezone_id
                    this.language = response.data.preference.language_id
                    this.currency = response.data.preference.currency
                    this.linkedInUrl = response.data.linked_in_url
                    if (response.data.timezone) {
                        var timezoneArray = [];
                        let timeZone = Object.entries(response.data.timezone);

                        timeZone.filter((data, index) => {
                            if (data[0] == response.data.preference.timezone_id) {
                                this.timeDefault = data[1]
                            }

                            timezoneArray.push({
                                'text': data[1],
                                'value': data[0]
                            })
                        })
                        this.timeList = timezoneArray
                    }

                    if (response.data.languages) {
                        var languagesArray = [];
                        let languages = response.data.languages;
                        this.languageList = Object.keys(languages).map((key) => {
                            if (response.data.preference.language_id == languages[key]['language_id']) {
                                this.languageDefault = languages[key]['name']
                            }
                            let languageCode = languages[key]['code']
                            this.languageListing[languageCode] = languages[key]['language_id']
                            return [languages[key]['code'], languages[key]['name']];
                        });
                    }

                    if (response.data.currencies) {
                        var currenciesArray = [];
                        let currencies = Object.entries(response.data.currencies);
                        currencies.filter((data, index) => {
                            currenciesArray.push({
                                'text': data[1].code,
                                'value': data[1].code
                            })
                        })
                        this.currencyList = currenciesArray
                    }
                    if (response.data.user_privacy && response.data.user_privacy.is_profile_visible == 1) {
                        this.is_profile_visible = true
                    }
                    if (response.data.user_privacy && response.data.user_privacy.public_avatar_and_linkedin == 1) {
                        this.public_avatar_and_linkedin = true
                    }

                    this.isShownComponent = true;

                }
            })
        }
    },
    created() {
        this.languageData = JSON.parse(store.state.languageLabel);
        this.countryDefault = this.languageData.placeholder.country
        this.cityDefault = this.languageData.placeholder.city
        this.languageDefault = this.languageData.placeholder.language
        this.timeDefault = this.languageData.placeholder.timezone
        this.changePhoto = this.languageData.label.edit
        this.languageCode = store.state.defaultLanguage
        this.currencyDefault = this.languageData.placeholder.currency
        this.isQuickAccessFilterDisplay = this.settingEnabled(constants.QUICK_ACCESS_FILTERS);
        this.imageLoader = false;
        this.isPrefilLoaded = true;
        this.isDonationSettingEnable = this.settingEnabled(constants.DONATION);
        const img = new Image();
        if (store.state.avatar != '' && store.state.avatar != null) {
            img.src = store.state.avatar;
            img.onload = () => {
                this.isPrefilLoaded = false
            }
            this.newUrl = store.state.avatar
        }
        this.getSettingListing();
    }
};
</script>
