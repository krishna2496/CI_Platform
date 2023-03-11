<template>
<div class="profile-page inner-pages">
    <header>
        <ThePrimaryHeader v-if="isShownComponent"></ThePrimaryHeader>
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
                            <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active ': isPrefilLoaded || imageLoader}">
                                <div class="content-loader"></div>
                            </div>
                            <picture-input :title="changePhoto" ref="pictureInput" @change="changeImage" accept="image/jpeg,image/png" :prefill="newUrl" buttonClass="btn" :customStrings="{
                                        upload: '<h1>Bummer!</h1>',
                                        drag: 'Drag a 😺 GIF or GTFO'
                                    }">
                            </picture-input>
                        </div>
                        <h4>{{userData.first_name}} {{userData.last_name}}</h4>
                        <b-list-group class="social-nav">
                            <b-list-group-item v-if="userData.linked_in_url != null && userData.linked_in_url != ''  ">
                                <b-link :href="userData.linked_in_url" target="_blank" :title="languageData.label.linked_in" class="linkedin-link">
                                    <img :src="$store.state.imagePath+'/assets/images/linkedin-ic-blue.svg'" class="normal-img" alt="linkedin img" />
                                    <img :src="$store.state.imagePath+'/assets/images/linkedin-ic.svg'" class="hover-img" alt="linkedin img" />
                                </b-link>
                            </b-list-group-item>
                        </b-list-group>
                    </div>
                    <!-- dashboard breadcrum -->
                    <MyAccountDashboardBreadcrumb></MyAccountDashboardBreadcrumb>
                </b-col>
                <b-col xl="9" lg="8" md="12" class="profile-form-wrap">
                    <b-form class="profile-form">
                        <b-row class="row-form">
                            <b-col cols="12">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.basic_information}}</span>
                                </h2>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.first_name}}*</label>
                                    <b-form-input id type="text" v-model.trim="profile.firstName" :class="{ 'is-invalid': submitted && $v.profile.firstName.$error }" @keypress="alphaNumeric($event)" :placeholder="languageData.placeholder.name" maxlength="60"></b-form-input>
                                    <div v-if="submitted && !$v.profile.firstName.required" class="invalid-feedback">
                                        {{ languageData.errors.name_required }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.surname}}*</label>
                                    <b-form-input id type="text" v-model.trim="profile.lastName" :class="{ 'is-invalid': submitted && $v.profile.lastName.$error }" @keypress="alphaNumeric($event)" :placeholder="languageData.placeholder.surname" maxlength="60">
                                    </b-form-input>
                                    <div v-if="submitted && !$v.profile.lastName.required" class="invalid-feedback">
                                        {{ languageData.errors.last_name_required }}</div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.employee_id}}</label>
                                    <b-form-input id type="text" v-model.trim="profile.employeeId" maxlength="60" :placeholder="languageData.placeholder.employee_id">
                                    </b-form-input>
                                </b-form-group>
                            </b-col>
                            <!-- <b-col md="6">
                                    <b-form-group>
                                        <label for>{{languageData.label.manager}}</label>
                                        <b-form-input id type="text" v-model.trim="profile.managerName"
                                            :placeholder="languageData.placeholder.manager" maxlength="16">
                                        </b-form-input>
                                    </b-form-group>
                                </b-col> -->
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.title}}</label>
                                    <b-form-input id type="text" v-model.trim="profile.title" :placeholder="languageData.placeholder.title" maxlength="60">
                                    </b-form-input>
                                </b-form-group>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label>{{languageData.label.country}}*</label>
                                    <CustomFieldDropdown v-model="profile.country" :errorClass="submitted && $v.profile.country.$error" :defaultText="countryDefault" :optionList="countryList" @updateCall="updateCountry" translationEnable="false" />
                                    <div v-if="submitted && !$v.profile.country.required" class="invalid-feedback">
                                        {{ languageData.errors.country_required }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <!-- <b-col md="6">		                                <b-form-group>		                                    <label>{{languageData.label.city}}</label>
                                    <CustomFieldDropdown v-model="profile.city"
                                        :defaultText="cityDefault"
                                        :optionList="cityList"
                                        @updateCall="updateCity"
                                        translationEnable="false" />		                                </b-form-group>		                            </b-col>	 -->
                            <b-col md="6">
                                <b-form-group>
                                    <label for>{{languageData.label.department}}</label>
                                    <b-form-input id type="text" v-model.trim="profile.department" maxlength="60" :placeholder="languageData.placeholder.department"></b-form-input>
                                </b-form-group>
                            </b-col>
                            <b-col md="12">
                                <b-form-group>
                                    <label>{{languageData.label.my_profile}}</label>
                                    <b-form-textarea id :placeholder="languageData.placeholder.my_profile" size="lg" no-resize v-model.trim="profile.profileText" rows="5"></b-form-textarea>
                                </b-form-group>
                            </b-col>
                            <b-col md="12">
                                <b-form-group>
                                    <label>{{languageData.label.why_i_volunteer}}</label>
                                    <b-form-textarea id v-model.trim="profile.whyiVolunteer" :placeholder="languageData.placeholder.why_i_volunteer" size="lg" no-resize rows="5"></b-form-textarea>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row class="row-form">
                            <b-col cols="12">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.professional_information}}</span>
                                </h2>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label>{{languageData.label.availability}}</label>
                                    <CustomFieldDropdown v-model="profile.availability" :defaultText="availabilityDefault" :optionList="availabilityList" @updateCall="updateAvailability" translationEnable="false" />
                                </b-form-group>
                            </b-col>
                            <b-col md="6">
                                <b-form-group class="linked-in-url">
                                    <label>{{languageData.label.linked_in}}</label>
                                    <b-form-input id v-model.trim="profile.linkedInUrl" :class="{ 'is-invalid': submitted && $v.profile.linkedInUrl.$error }" :placeholder="languageData.placeholder.linked_in"></b-form-input>
                                    <div v-if="submitted && !$v.profile.linkedInUrl.validLinkedInUrl" class="invalid-feedback">
                                        {{ languageData.errors.valid_linked_in_url }}</div>
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row class="row-form">
                            <b-col cols="12" v-if="isSkillDisplay">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.my_skills}}</span>
                                </h2>
                            </b-col>
                            <b-col cols="12" v-if="isSkillDisplay">
                                <ul class="skill-list-wrapper" v-if="resetUserSkillList != null && resetUserSkillList.length > 0">
                                    <li v-for="(toitem, index) in resetUserSkillList" :key=index>{{toitem.name}}
                                    </li>
                                </ul>
                                <ul v-else class="skill-list-wrapper">
                                    <li>{{languageData.label.no_skill_found}}</li>
                                </ul>
                                <MultiSelect v-if="isShownComponent" :fromList="skillListing" :toList="userSkillList" @resetData="resetSkillListingData" @saveSkillData="saveSkillData" @resetPreviousData="resetPreviousData" />
                            </b-col>
                        </b-row>
                        <b-row class="row-form" v-if="isDonationSettingEnable">
                            <b-col cols="12">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.donations}}</span>
                                </h2>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label for class="has-help-text">{{languageData.label.personal_donation_goal}}*
                                    </label>
                                    <b-form-input id type="text" v-model.trim="profile.amount" :class="{ 'is-invalid': submitted && $v.profile.amount.$error }" :placeholder="languageData.label.amount"></b-form-input>
                                    <div v-if="submitted && !$v.profile.amount.required" class="invalid-feedback">
                                        {{ languageData.errors.donation_goal_required }}
                                    </div>
                                    <div v-if="submitted && $v.profile.amount.required && !$v.profile.amount.numeric" class="invalid-feedback">
                                        {{ languageData.errors.valid_donation_goal }}
                                    </div>
                                </b-form-group>
                            </b-col>
                            <b-col md="6">
                                <b-form-group>
                                    <label>{{languageData.label.year}}</label>
                                    <CustomFieldDropdown v-model="profile.year" :defaultText="yearDefault" :optionList="yearList" @updateCall="updateYear" translationEnable="false" />
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row class="row-form" v-if="isShownComponent && CustomFieldList.length > 0">
                            <b-col cols="12">
                                <h2 class="title-with-border">
                                    <span>{{languageData.label.custom_field}}</span>
                                </h2>
                            </b-col>
                            <b-col cols="12">
                                <CustomField :optionList="CustomFieldList" :optionListValue="CustomFieldValue" :isSubmit="isCustomFieldSubmit" @detectChangeInCustomFeild="detectChangeInCustomFeild" />
                            </b-col>
                        </b-row>
                        <b-row class="row-form">
                            <b-col cols="12">
                                <div class="btn-wrapper">
                                    <b-button class="btn-bordersecondary btn-save" @click="handleSubmit">
                                        {{languageData.label.save}}
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
import PictureInput from '../components/vue-picture-input';
import { isEmpty } from 'lodash';
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
    policy
} from "../services/service";
import {
    required,
    maxLength,
    sameAs,
    minLength,
    numeric
} from 'vuelidate/lib/validators';
import constants from '../constant';
import moment from 'moment';
import {
    setSiteTitle
} from '../utils';
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
            isSkillDisplay: true,
            userIcon: require("@/assets/images/user-img-large.png"),
            countryList: [],
            countryDefault: '',
            availabilityList: [],
            passwordSubmit: false,
            isCustomFieldSubmit: false,
            availabilityDefault: "",
            file: "null",
            languageData: [],
            skillListing: [],
            resetSkillList: [],
            newUrl: "",
            isPrefilLoaded: true,
            prefilImageType: {
                mediaType: ''
            },
            userData: [],
            isShownComponent: false,
            cityList: [],
            cityDefault: "",
            showErrorDiv: false,
            message: null,
            classletiant: "success",
            profile: {
                firstName: "",
                lastName: "",
                employeeId: "",
                profileText: "",
                title: "",
                whyiVolunteer: "",
                linkedInUrl: "",
                department: "",
                country: "",
                city: "",
                availability: 0,
                userSkills: [],
                amount: "",
                year: ""
            },
            submitted: false,
            language: '',
            languageCode: '',
            CustomFieldList: [],
            CustomFieldValue: [],
            returnCustomFeildData: [],
            userSkillList: [],
            resetUserSkillList: [],
            imageLoader: true,
            changePhoto: "",
            showPage: true,
            saveProfileData: {
                first_name: "",
                last_name: "",
                availability_id: "",
                why_i_volunteer: "",
                employee_id: "",
                department: "",
                manager_name: "",
                city_id: "",
                country_id: "",
                profile_text: "",
                linked_in_url: "",
                custom_fields: [],
                donation_goal: "",
                donation_goal_year: ""
            },
            yearDefault: "",
            yearList: [],
            isDonationSettingEnable: false
        };
    },
    validations() {
        const rules = {
            profile: {
                firstName: {
                    required
                },
                lastName: {
                    required
                },
                linkedInUrl: {
                    validLinkedInUrl(linkedInUrl) {
                        if (linkedInUrl == '') {
                            return true
                        }
                        // Be sure to match this with the validation in the PHP class CustomValidationRules
                        const regexp = /^https:\/\/(|[a-z]{2,3}\.)linkedin\.com\/(in|company)\/[-a-z0-9]+(|[\/#\?][^\n]*)$/s;
                        return (regexp.test(linkedInUrl));
                    }
                },
                country: {
                    required
                }
            }
        }

        if (this.isDonationSettingEnable) {
            rules.profile.amount = {
                required,
                numeric
            }
        }

        return rules;
    },
    methods: {
        updateCity(value) {
            this.cityDefault = value.selectedVal;
            this.profile.city = value.selectedId;
        },
        updateYear(value) {
            this.yearDefault = value.selectedVal;
            this.profile.year = value.selectedVal.replace(/[\s\/]/g, '')

            let usergoaldata = this.userData.user_donation_goal;
            let datagoalAmount = usergoaldata.filter((data, index) => {
                if (parseInt(this.profile.year) == parseInt(data.donation_goal_year)) {
                    return parseInt(data.donation_goal)
                }
            })
            if (datagoalAmount.length > 0 && datagoalAmount[0].donation_goal) {
                this.profile.amount = String(datagoalAmount[0].donation_goal)
                this.profile.year = parseInt(datagoalAmount[0].donation_goal_year)
                this.yearDefault = parseInt(datagoalAmount[0].donation_goal_year)
            } else {
                this.profile.amount = ''
            }
        },
        updateCountry(value) {
            this.countryDefault = value.selectedVal;
            this.profile.country = value.selectedId;
            this.changeCityData(value.selectedId);
        },
        updateAvailability(value) {
            this.availabilityDefault = value.selectedVal;
            this.profile.availability = value.selectedId;
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
                    if (response.data && response.data.avatar) {
                        const img = new Image();
                        this.newUrl = response.data.avatar;
                        img.src = this.newUrl;
                        img.onload = () => {
                            this.imageLoader = false;
                        }
                    }
                }
            })
        },
        saveSkillData() {
            let data = JSON.parse(localStorage.getItem('currentSkill'));
            this.resetUserSkillList = data
        },
        // Get user detail
        async getUserProfileDetail() {
            await getUserDetail().then(response => {
                this.pageLoaded = true;
                if (response.error == true) {
                    this.isShownComponent = true
                    this.errorPage = true
                    this.errorPageMessage = response.message
                } else {
                    this.errorPage = false
                    this.userData = response.data;
                    this.newUrl = this.userData.avatar;
                    const img = new Image();
                    if (this.newUrl != '' && this.newUrl != null) {
                        img.src = this.newUrl;
                        img.onload = () => {
                            this.isPrefilLoaded = false
                        }
                    } else {
                        this.isPrefilLoaded = false
                    }
                    store.commit("changeAvatar", this.userData)
                    this.cityList = Object.keys(this.userData.city_list).map((key) => {
                        return [Number(key), this.userData.city_list[key]];
                    });
                    this.cityList.sort((a, b) => {
                        let cityOne = a[1].toLowerCase(),
                            cityTwo = b[1].toLowerCase();
                        if (cityOne < cityTwo) //sort string ascending
                            return -1;
                        if (cityOne > cityTwo)
                            return 1;
                        return 0; //default return value (no sorting)
                    });
                    this.availabilityList = Object.keys(this.userData.availability_list).map((key) => {
                        return [Number(key), this.userData.availability_list[key]];
                    });
                    this.CustomFieldList = this.userData.custom_fields
                    if (this.userData.user_custom_field_value) {
                        this.CustomFieldValue = Object.keys(this.userData.user_custom_field_value).map((
                            key) => {
                            return [
                                Number(this.userData.user_custom_field_value[key]
                                    .field_id),
                                this.userData.user_custom_field_value[key].value
                            ];
                        });
                    }
                    this.profile.firstName = this.userData.first_name,
                        this.profile.lastName = this.userData.last_name,
                        this.profile.employeeId = this.userData.employee_id,
                        this.profile.profileText = this.userData.profile_text,
                        this.profile.title = this.userData.title,
                        this.profile.whyiVolunteer = this.userData.why_i_volunteer
                    if (this.userData.user_donation_goal) {
                        let usergoaldata = this.userData.user_donation_goal;
                        this.yearList = [];
                        let datagoalAmount = usergoaldata.filter((data, index) => {
                            this.yearList.push([data.donation_goal_year, data.donation_goal_year]);
                            if (parseInt(this.profile.year) == parseInt(data.donation_goal_year)) {
                                return parseInt(data.donation_goal)
                            }
                        })
                        if (datagoalAmount.length > 0 && datagoalAmount[0].donation_goal) {
                            this.profile.amount = String(datagoalAmount[0].donation_goal)
                            this.profile.year = parseInt(datagoalAmount[0].donation_goal_year)
                            this.yearDefault = (datagoalAmount[0].donation_goal_year).toString();
                        } else {
                            this.profile.amount = ''
                        }

                    }
                    if (this.userData.linked_in_url != null) {
                        this.profile.linkedInUrl = this.userData.linked_in_url
                    }
                    this.profile.department = this.userData.department,
                        // this.profile.availability = this.userData.availability_id,
                        this.profile.userSkills = this.userData.user_skills
                    if (this.userData.country_id != 0) {
                        this.profile.country = this.userData.country_id
                    }
                    if (this.userData.city_id != 0) {
                        this.profile.city = this.userData.city_id
                    }
                    if (this.userData.availability_id != 0 && this.userData.availability_id != null) {
                        this.profile.availability = this.userData.availability_id
                    }
                    if (this.userData.city_list != '' && this.userData.city_list != null) {
                        this.cityDefault = this.userData.city_list[this.userData.city_id]
                    }
                    if (this.userData.availability && this.userData.availability.type != '' && this.userData.availability.type !=
                        null) {
                        const translatedAvailability = this.userData.availability.translations
                            .find(translation => translation.lang === this.languageCode.toLowerCase());
                        this.availabilityDefault = translatedAvailability ?
                            translatedAvailability.title : this.userData.availability.type;
                    } else {
                        this.availabilityDefault = this.languageData.placeholder.availability;
                    }
                    this.skillListing = [];
                    this.userSkillList = [];
                    this.resetUserSkillList = [];
                    store.commit("saveCurrentSkill", null)
                    store.commit("saveCurrentFromSkill", null)
                    country().then(responseData => {
                        if (responseData.error == false) {
                            this.countryList = responseData.data
                            if (this.countryList) {
                                this.countryList.filter((data, index) => {
                                    if (this.userData.country_id == data[0]) {
                                        this.countryDefault = data[1]
                                    }
                                })
                            }
                            this.countryList.sort((countryA, countryB) => {
                                let countryOne = countryA[1].toLowerCase(),
                                    countryTwo = countryB[1].toLowerCase();
                                if (countryOne < countryTwo) //sort string ascending
                                    return -1;
                                if (countryOne > countryTwo)
                                    return 1;
                                return 0; //default return value (no sorting)
                            });
                        }
                        timezone().then(responseData => {
                            if (responseData.error == false) {
                                var array = [];
                                responseData.data.filter((data, index) => {
                                    array.push({
                                        'text': data[1],
                                        'value': data[0]
                                    })
                                })
                                this.timeList = array
                            }
                            skill().then(responseData => {
                                if (responseData.error == false) {
                                    this.userData.skill_list = responseData.data
                                    Object.keys(this.userData.skill_list).map(
                                        (key) => {
                                            if (this.userData.skill_list[
                                                    key]) {
                                                this.skillListing.push({
                                                    name: this
                                                        .userData
                                                        .skill_list[
                                                            key],
                                                    id: key
                                                });
                                                this.skillListing.sort(function (first, next) {
                                                    first = first.name;
                                                    next = next.name;
                                                    return first < next ? -1 : (first > next ? 1 : 0);
                                                });
                                            }
                                        });
                                }
                                this.isShownComponent = true;
                            })
                        })
                    })
                    if (this.userData.user_skills) {
                        Object.keys(this.userData.user_skills).map((key) => {
                            if (this.userData.user_skills[key].translations) {
                                this.userSkillList.push({
                                    name: this.userData.user_skills[key].translations,
                                    id: this.userData.user_skills[key].skill_id
                                });
                                this.resetUserSkillList.push({
                                    name: this.userData.user_skills[key].translations,
                                    id: this.userData.user_skills[key].skill_id
                                });
                            }
                        });
                    }
                }
                this.imageLoader = false;
            })
        },
        resetSkillListingData() {
            this.skillListing = [];
            this.userSkillList = [];
            if (this.userData.skill_list) {
                Object.keys(this.userData.skill_list).map((key) => {
                    if (this.userData.skill_list[key]) {
                        this.skillListing.push({
                            name: this.userData.skill_list[key],
                            id: key
                        });
                    }
                });
            }
            if (this.userData.user_skills) {
                Object.keys(this.userData.user_skills).map((key) => {
                    if (this.userData.user_skills[key].translations) {
                        this.userSkillList.push({
                            name: this.userData.user_skills[key].translations,
                            id: this.userData.user_skills[key].skill_id
                        });
                    }
                });
            }
            this.userSkillList.filter((toItem) => {
                this.skillListing.filter((fromItem, fromIndex) => {
                    if (toItem.id == fromItem.id) {
                        this.skillListing.splice(fromIndex, 1);
                    }
                });
            });
        },
        resetPreviousData() {
            let currentSkill = JSON.parse(localStorage.getItem('currentSkill'));
            this.userSkillList = currentSkill
            let currentFromSkill = JSON.parse(localStorage.getItem('currentFromSkill'));
            this.skillListing = currentFromSkill
        },
        detectChangeInCustomFeild(data) {
            this.returnCustomFeildData = data;
        },
        //submit form
        handleSubmit() {
            this.submitted = true;
            this.$v.$touch();
            let isCustomFieldInvalid = false;
            let isNormalFieldInvalid = false;
            let validationData = document.querySelectorAll('[validstate="true"]');
            this.isCustomFieldSubmit = true;
            validationData.forEach((validateData) => {
                validateData.classList.add("is-invalid");
                isCustomFieldInvalid = true;
            });
            if (this.$v.profile.$invalid) {
                isNormalFieldInvalid = true;
            }
            if (isNormalFieldInvalid == true || isCustomFieldInvalid == true) {
                return
            }
            this.saveProfileData.first_name = this.profile.firstName;
            this.saveProfileData.last_name = this.profile.lastName;
            this.saveProfileData.title = this.profile.title;
            if (this.profile.availability != 0) {
                this.saveProfileData.availability_id = this.profile.availability
            } else {
                delete this.saveProfileData['availability_id'];
            }
            this.saveProfileData.why_i_volunteer = this.profile.whyiVolunteer;
            this.saveProfileData.employee_id = this.profile.employeeId;
            this.saveProfileData.department = this.profile.department;
            if (!isEmpty(this.profile.city_id)) {
                this.saveProfileData.city_id = this.profile.city;
            } else {
                delete this.saveProfileData.city_id;
            }
            this.saveProfileData.country_id = this.profile.country;
            this.saveProfileData.profile_text = this.profile.profileText;
            this.saveProfileData.linked_in_url = this.profile.linkedInUrl;
            this.saveProfileData.custom_fields = [];
            this.saveProfileData.skills = [];

            if (this.isDonationSettingEnable) {
                this.saveProfileData.donation_goal = this.profile.amount;
                this.saveProfileData.donation_goal_year = this.profile.year;
            }

            Object.keys(this.returnCustomFeildData).map((key) => {
                let customValue = this.returnCustomFeildData[key];
                if (Array.isArray(customValue)) {
                    customValue = customValue.join();
                }
                this.saveProfileData.custom_fields.push({
                    field_id: key,
                    value: customValue
                });
            });
            if (this.userSkillList.length > 0 && this.isSkillDisplay) {
                Object.keys(this.userSkillList).map((key) => {
                    this.saveProfileData['skills'].push({
                        skill_id: this.userSkillList[key].id,
                    });
                });
            }
            // Call to save profile service
            saveUserProfile(this.saveProfileData).then(response => {
                if (response.error == true) {
                    this.makeToast("danger", response.message);
                } else {
                    const redirect = !this.isUserProfileComplete && response.data.is_profile_complete;
                    this.isUserProfileComplete = response.data.is_profile_complete;
                    store.commit('changeProfileSetFlag', response.data.is_profile_complete);
                    this.showPage = false;
                    this.setPolicyPage();
                    this.isShownComponent = false;
                    this.getUserProfileDetail().then(() => {
                        this.showPage = true;
                        setSiteTitle();
                        this.makeToast("success", response.message);
                        store.commit("changeUserDetail", this.profile)
                        if (redirect) {
                            this.$router.push('/home');
                        }
                    });

                }
            });
        },
        changeCityData(countryId) {
            if (countryId) {
                changeCity(countryId).then(response => {
                    if (response.error === true) {
                        this.cityList = []
                    } else {
                        this.cityList = response.data
                        this.cityList.sort((a, b) => {
                            let cityOne = a[1].toLowerCase(),
                                cityTwo = b[1].toLowerCase();
                            if (cityOne < cityTwo) //sort string ascending
                                return -1;
                            if (cityOne > cityTwo)
                                return 1;
                            return 0; //default return value (no sorting)
                        });
                    }
                    this.cityDefault = this.languageData.placeholder.city
                    this.profile.city = '';
                });
            }
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
        setPolicyPage() {
            policy().then(response => {
                if (response.error == false) {
                    if (response.data.length > 0) {
                        store.commit('policyPage', response.data);
                        return;
                    }
                }
                store.commit('policyPage', null);
            });
        }
    },
    created() {
        this.languageData = JSON.parse(store.state.languageLabel);
        this.countryDefault = this.languageData.placeholder.country
        this.cityDefault = this.languageData.placeholder.city
        this.availabilityDefault = this.languageData.placeholder.availablity
        this.changePhoto = this.languageData.label.edit
        this.isQuickAccessFilterDisplay = this.settingEnabled(constants.QUICK_ACCESS_FILTERS);
        this.isSkillDisplay = this.settingEnabled(constants.SKILLS_ENABLED);
        this.isDonationSettingEnable = this.settingEnabled(constants.DONATION);
        this.languageCode = store.state.defaultLanguage.toLowerCase();
        this.profile.year = this.yearDefault = moment().format('Y')
        this.yearList.push([this.yearDefault, this.yearDefault]);
        this.getUserProfileDetail();
        if (store.state.isProfileComplete != 1) {
            this.isUserProfileComplete = 0;
        }
    }
};
</script>
