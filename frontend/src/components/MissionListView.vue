<template>
<div class="cards-wrapper" v-if="items.length > 0">
    <div class="card-listing">
        <div class="card-outer" :id="`listview-${index}`" v-for="(mission, index) in items" :key=index>
            <b-card no-body>
                <b-card-header>
                    <div class="header-img-block" v-bind:class="{'grayed-out' :getClosedStatus(mission),'no-img' : checkDefaultMediaFormat(mission.default_media_type) && getMediaPath(mission.default_media_path) == ''}">
                        <b-alert show class="alert card-alert alert-success" v-if="getAppliedStatus(mission)">
                            {{languageData.label.applied}}</b-alert>
                        <b-alert show class="alert card-alert alert-warning" v-if="getClosedStatus(mission)">
                            {{languageData.label.closed}}</b-alert>
                        <div v-if="checkDefaultMediaFormat(mission.default_media_type)" v-bind:class="{'d-none' : (checkDefaultMediaFormat(mission.default_media_type) && getMediaPath(mission.default_media_path) == '')}" class="group-img" :style="{backgroundImage: 'url('+getMediaPath(mission.default_media_path)+')'}">
                            <img :src="getMediaPath(mission.default_media_path)" alt="">
                        </div>
                        <div v-else class="group-img" :style="{backgroundImage: 'url('+youtubeThumbImage(mission.default_media_path)+')'}">
                        </div>
                        <template v-if="checkDefaultMediaFormat(mission.default_media_type) && getMediaPath(mission.default_media_path) == ''">
                            <i class="camera-icon">
                                <img src="../assets/images/camera-ic.svg" />
                            </i>
                            <p>{{languageData.label.no_image_available}}</p>
                        </template>
                        <div class="location">
                            <i>
                                <img :src="$store.state.imagePath+'/assets/images/location.svg'" :alt="languageData.label.location">
                            </i>{{mission.city_name}}
                        </div>
                    </div>
                </b-card-header>

                <b-card-body>
                    <div class="card-detail-column">
                        <div class="content-block">
                            <div class="mission-label-wrap">
                                <div class="group-category" v-if="mission.mission_theme != null && isThemeSet"><span class="category-text">{{getThemeTitle(mission.mission_theme.translations)}}</span></div>
                                <div class="mission-label volunteer-label" v-if="isDisplayMissionLabel && checkMissionTypeVolunteering(mission.mission_type)">
                                    <span :style="{ backgroundColor: volunteeringMissionTypeLabels.backgroundColor}"><i class="icon-wrap"><img :src="volunteeringMissionTypeLabels.icon" alt="volunteer icon"></i>{{volunteeringMissionTypeLabels.label}}</span>
                                </div>
                                <div class="mission-label virtual-label" v-if="mission.is_virtual == 1">
                                    <span>{{languageData.label.virtual_mission}}</span>
                                </div>
                                <div class="mission-label donation-label" v-if="isDisplayMissionLabel && checkMissionTypeDonation(mission.mission_type)">
                                    <span :style="{ backgroundColor: donationMissionTypeLabels.backgroundColor}"><i class="icon-wrap"><img :src="donationMissionTypeLabels.icon" alt="donation icon"></i>{{donationMissionTypeLabels.label}}</span>
                                </div>
                            </div>
                            <b-link target="_blank" :to="'/mission-detail/' + mission.mission_id" class="card-title" v-if="checkMissionTypeVolunteering(mission.mission_type)">
                                {{mission.title | substring(75)}}
                            </b-link>
                            <b-link target="_blank" :to="'/donation-mission-detail/' + mission.mission_id" class="card-title" v-if="checkMissionTypeDonation(mission.mission_type)">
                                {{mission.title | substring(75)}}
                            </b-link>
                            <template v-if="checkMissionTypeTime(mission.mission_type) || checkMissionTypeGoal(mission.mission_type)">
                                <div class="ratings" v-if="isStarRatingDisplay">
                                    <star-rating v-bind:increment="0.5" v-bind:max-rating="5" inactive-color="#dddddd" active-color="#F7D341" v-bind:star-size="18" :rating="mission.mission_rating_count" :read-only="true">
                                    </star-rating>
                                </div>
                            </template>

                            <template v-if="checkMissionTypeDonation(mission.mission_type)">
                                <div class="ratings" v-if="isDonationMissionRatingEnabled">
                                    <star-rating v-bind:increment="0.5" v-bind:max-rating="5" inactive-color="#dddddd" active-color="#F7D341" v-bind:star-size="18" :rating="mission.mission_rating_count" :read-only="true">
                                    </star-rating>
                                </div>
                            </template>
                            <b-card-text>
                                {{mission.short_description | substring(150)}}
                            </b-card-text>
                            <p class="event-name" v-if="mission.organization != null">{{ languageData.label.for }} <span>{{mission.organization.name}}</span></p>
                        </div>
                        <div class="group-details volunteer-progress" v-if="checkMissionTypeGoal(mission.mission_type) || checkMissionTypeTime(mission.mission_type)">
                            <div class="content-wrap">
                                <template v-if="mission.total_seats != 0 && mission.total_seats !== null">
                                    <div class="detail-column seat-info">
                                        <i class="icon-wrap">
                                            <img :src="$store.state.imagePath+'/assets/images/user-icon.svg'" alt="user">
                                        </i>
                                        <div class="text-wrap">
                                            <span class="title-text">{{mission.seats_left}}</span>
                                            <span class="subtitle-text">{{ languageData.label.seats_left }}</span>
                                        </div>
                                    </div>
                                </template>
                                <template v-if="mission.application_deadline != null ||
                                            checkMissionTypeTime(mission.mission_type)
                                            ">
                                    <div class="detail-column info-block" v-if="mission.application_deadline != null">
                                        <i class="icon-wrap">
                                            <img :src="$store.state.imagePath+'/assets/images/clock.svg'" alt="user">
                                        </i>
                                        <div class="text-wrap">
                                            <span class="title-text">{{mission.application_deadline | formatDate}}</span>
                                            <span class="subtitle-text">{{ languageData.label.deadline }}</span>
                                        </div>
                                    </div>
                                </template>
                                <div class="detail-column calendar-col" v-if="mission.end_date !== null">
                                    <i class="icon-wrap">
                                        <img :src="$store.state.imagePath+'/assets/images/calendar.svg'" alt="user">
                                    </i>
                                    <div class="text-wrap" v-if="mission.end_date !== null">
                                        <span class="title-text"><em>{{ languageData.label.from }}</em>
                                            {{mission.start_date | formatDate }}</span>
                                        <span class="title-text"><em>{{ languageData.label.until}}</em>
                                            {{ mission.end_date | formatDate }}</span>
                                    </div>
                                </div>
                                <div class="detail-column progress-block" v-if="!checkMissionTypeTime(mission.mission_type)">
                                    <i class="icon-wrap">
                                        <img :src="$store.state.imagePath+'/assets/images/target-ic.svg'" alt="user">
                                    </i>
                                    <div class="text-wrap">
                                        <b-progress :value="mission.achieved_goal | filterGoal" :max="mission.goal_objective"></b-progress>
                                        <span class="subtitle-text">{{mission.achieved_goal}}
                                            <span v-if="mission.label_goal_achieved != ''"> {{ mission.label_goal_achieved }}
                                            </span>
                                            <span v-else>{{ languageData.label.achieved }}</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-column skill-col" v-if="mission.skill && isSkillDisplay">
                                    <i class="icon-wrap">
                                        <img :src="$store.state.imagePath+'/assets/images/skill-icon.svg'" alt="skill icon">
                                    </i>

                                    <div class="text-wrap dropdown-outer" :id="`skillWrap_${mission.mission_id}`">
                                        <span class="title-text">
                                            {{ getFirstSkill(mission.skill) }}
                                            <template v-if="mission.skill.length > 1">
                                                <span> {{ languageData.label.and }} </span>
                                                <u>
                                                    <b-button :id="`skillPopover_${mission.mission_id}`" class="more-btn">
                                                        <span> {{ mission.skill.length - 1 }} </span>{{ languageData.label.more }}
                                                    </b-button>
                                                </u>
                                                <b-popover :target="`skillPopover_${mission.mission_id}`" triggers="hover focus" placement="top" custom-class="skill-popover" :container="`skillWrap_${mission.mission_id}`">
                                                    <b-list-group v-for="(skill, key) in getRemainingSkill(mission.skill)" :key=key>
                                                        <b-list-group-item>{{ skill.title }}</b-list-group-item>
                                                    </b-list-group>
                                                </b-popover>
                                            </template>
                                        </span>
                                        <span class="subtitle-text skill-text-wrap">{{ languageData.label.skills }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="group-details progress-details" v-else>
                            <div class="content-wrap">
                                <div class="detail-column progress-block">
                                    <b-progress v-if="mission.donation_attribute && mission.donation_attribute.show_donation_meter" :value="mission.donation_statistics.total_amount"  :max="mission.donation_attribute.goal_amount"></b-progress>
                                </div>
                                <div class="detail-column progress-info-column">
                                    <div class="text-wrap">
                                        <p v-if="mission.donation_attribute">
                                            <b class="donate-success" v-if="mission.donation_attribute.show_donation_count"><template v-if="mission.user_currency">{{mission.user_currency.symbol}}</template>{{mission.donation_statistics.total_amount}}</b>
                                            <span v-if="mission.donation_attribute.show_donation_count"> {{ languageData.label.raised_by}} </span>
                                            <span v-if="mission.donation_attribute.show_goal_amount && mission.donation_attribute.show_donation_count"> {{ languageData.label.of}} </span>
                                            <span v-if="mission.donation_attribute.show_goal_amount"><template v-if="mission.user_currency">{{mission.user_currency.symbol}}</template>{{mission.donation_attribute.goal_amount}} {{ languageData.label.goal}}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="detail-column achieved-column" v-if="mission.donation_attribute && mission.donation_attribute.show_donation_percentage">
                                    <i class="icon-wrap">
                                        <img :src="$store.state.imagePath+'/assets/images/target-ic.svg'" alt="target icon">
                                    </i>
                                    <div class="text-wrap" v-if="mission.donation_attribute.show_donation_percentage">
                                        <span class="title-text">{{countDonationPercentage(mission.donation_statistics.total_amount, mission.donation_attribute.goal_amount)}}%
                                        </span>
                                        <span class="subtitle-text">{{ languageData.label.achieved}}</span>
                                    </div>
                                </div>
                                <div class="detail-column info-block" v-if="mission.application_deadline != null">
                                    <i class="icon-wrap">
                                        <img :src="$store.state.imagePath+'/assets/images/clock.svg'" alt="user">
                                    </i>

                                    <div class="text-wrap" v-if="mission.application_deadline != null">
                                        <span class="title-text">{{mission.application_deadline | formatDate}}</span>
                                        <span class="subtitle-text">{{ languageData.label.deadline }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-action-block">
                        <div class="donate-btn-wrap" v-if="checkMissionTypeDonation(mission.mission_type)">
                            <b-form-group>
                                <label for="" v-if="mission.user_currency">{{mission.user_currency.symbol}}</label>
                                <b-form-input id="" type="text" class="form-control" value="20"></b-form-input>
                                <b-button class="btn-donate btn-fillsecondary">{{ languageData.label.donate }}</b-button>
                            </b-form-group>
                        </div>
                        <div class="btn-wrap">
                                <b-link :to="'/mission-detail/' + mission.mission_id" v-if="checkMissionTypeVolunteering(mission.mission_type)">
                                    <b-button class="btn-bordersecondary icon-btn">
                                        <span>{{ languageData.label.view_detail | substringWithOutDot(36) }}</span>
                                        <i class="icon-wrap">
											<svg width="18" height="9" viewBox="0 0 18 9" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M17.3571 4.54129C17.3571 4.63504 17.3237 4.7154 17.2567 4.78237L13.3996 8.33817C13.2924 8.43192 13.1752 8.45201 13.048 8.39844C12.9208 8.33817 12.8571 8.24107 12.8571 8.10714V5.85714H0.321429C0.227679 5.85714 0.15067 5.82701 0.0904018 5.76674C0.0301339 5.70647 0 5.62946 0 5.53571V3.60714C0 3.51339 0.0301339 3.43638 0.0904018 3.37612C0.15067 3.31585 0.227679 3.28571 0.321429 3.28571H12.8571V1.03571C12.8571 0.895089 12.9208 0.797991 13.048 0.744419C13.1752 0.690848 13.2924 0.707589 13.3996 0.794642L17.2567 4.31027C17.3237 4.37723 17.3571 4.45424 17.3571 4.54129Z"/>
											</svg>
										</i>
                                    </b-button>
                                </b-link>

								<b-link :to="'/donation-mission-detail/' + mission.mission_id" v-if="checkMissionTypeDonation(mission.mission_type)">
                                    <b-button class="btn-bordersecondary icon-btn gray-btn">
                                        <span>{{ languageData.label.view_detail | substringWithOutDot(36) }}</span>
                                        <i class="icon-wrap">
											<svg width="18" height="9" viewBox="0 0 18 9" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M17.3571 4.54129C17.3571 4.63504 17.3237 4.7154 17.2567 4.78237L13.3996 8.33817C13.2924 8.43192 13.1752 8.45201 13.048 8.39844C12.9208 8.33817 12.8571 8.24107 12.8571 8.10714V5.85714H0.321429C0.227679 5.85714 0.15067 5.82701 0.0904018 5.76674C0.0301339 5.70647 0 5.62946 0 5.53571V3.60714C0 3.51339 0.0301339 3.43638 0.0904018 3.37612C0.15067 3.31585 0.227679 3.28571 0.321429 3.28571H12.8571V1.03571C12.8571 0.895089 12.9208 0.797991 13.048 0.744419C13.1752 0.690848 13.2924 0.707589 13.3996 0.794642L17.2567 4.31027C17.3237 4.37723 17.3571 4.45424 17.3571 4.54129Z"/>
											</svg>
										</i>
                                    </b-button>
                                </b-link>
                            </div>
                        <div class="social-btn">
                            <b-button class="icon-btn" v-if="isInviteColleagueDisplay" v-b-tooltip.hover :title="languageData.label.recommend_to_co_worker" @click="handleModal(mission.mission_id)">
                                <img :src="$store.state.imagePath+'/assets/images/multi-user-icon.svg'" alt="multi user icon">
                            </b-button>

                            <b-button v-bind:class="{
                                'icon-btn' : true,

                                'fill-heart-btn' : mission.is_favourite == 1

                                }" v-b-tooltip.hover :title="mission.is_favourite == 1 ?  languageData.label.remove_from_favourite :languageData.label.add_to_favourite" @click="favoriteMission(mission.mission_id)">
                                <img v-if="mission.is_favourite == 0" :src="$store.state.imagePath+'/assets/images/heart-icon.svg'" alt="heart icon">
                                <img v-if="mission.is_favourite == 1" :src="$store.state.imagePath+'/assets/images/heart-fill-icon.svg'" alt="heart icon">
                            </b-button>
                        </div>

                    </div>
                </b-card-body>
            </b-card>
        </div>
    </div>
    <invite-co-worker ref="userDetailModal" entity-type="MISSION" :entity-id="currentMissionId"></invite-co-worker>

</div>
<div class="no-data-found" v-else>
    <h2 class="text-center">{{noRecordFound()}}</h2>
    <div class="btn-wrap" v-if="isSubmitNewMissionSet" @click="submitNewMission">
        <b-button class="btn-bordersecondary icon-btn">
            <span>{{ languageData.label.submit_new_mission }}</span>
            <i>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 16" width="19" height="15">
                    <g id="Main Content">
                        <g id="1">
                            <g id="Button">
                                <path id="Forma 1 copy 12" class="shp0" d="M16.49,1.22c-0.31,-0.3 -0.83,-0.3 -1.16,0c-0.31,0.29 -0.31,0.77 0,1.06l5.88,5.44h-19.39c-0.45,0 -0.81,0.33 -0.81,0.75c0,0.42 0.36,0.76 0.81,0.76h19.39l-5.88,5.43c-0.31,0.3 -0.31,0.78 0,1.07c0.32,0.3 0.85,0.3 1.16,0l7.27,-6.73c0.32,-0.29 0.32,-0.77 0,-1.06z" />
                            </g>
                        </g>
                    </g>
                    </g>
                </svg>
            </i>
        </b-button>
    </div>
</div>
</template>

<script>
import store from '../store';
import constants from '../constant';
import InviteCoWorker from '@/components/InviteCoWorker';
import StarRating from 'vue-star-rating';
import moment from 'moment';
import {
    favoriteMission,
    applyMission
} from "../services/service";

export default {
    name: "MissionListView",
    props: {
        items: Array
    },
    components: {
        StarRating,
        InviteCoWorker
    },
    data() {
        return {
            currentMissionId: 0,
            isInviteColleagueDisplay: true,
            isQuickAccessSet: true,
            isThemeSet: true,
            isStarRatingDisplay: true,
            isSubmitNewMissionSet: true,
            languageData: [],
            message: null,
            submitNewMissionUrl: '',
            isSkillDisplay: true,
            isDisplayMissionLabel: false,
            isVolunteeringSettingEnabled: true,
            isDonationSettingEnabled: true,
            missionTypeLabels: "",
            volunteeringMissionTypeLabels: {
                icon: "",
                label: "",
                backgroundColor: "",
            },
            donationMissionTypeLabels: {
                icon: "",
                label: "",
                backgroundColor: "",
            },
            donationPercentage: 0,
            isDonationMissionRatingEnabled: true
        };
    },
    methods: {
        onOver() {
            this.$refs.skillDropdown.visible = true;
        },
        onLeave() {
            this.$refs.skillDropdown.visible = false;
        },
        noRecordFound() {
            let defaultLang = store.state.defaultLanguage.toLowerCase();
            if (JSON.parse(store.state.missionNotFoundText) != "") {
                let missionNotFoundArray = JSON.parse(store.state.missionNotFoundText);
                let data = missionNotFoundArray.filter(item => {
                    if (item.lang == defaultLang) {
                        return item;
                    }
                });

                if (data[0] && data[0].message) {
                    return data[0].message;
                } else {
                    return this.languageData.label.no_record_found;
                }
            } else {
                return this.languageData.label.no_record_found;
            }
        },
        // Get theme title
        getThemeTitle(translations) {
            if (translations) {
                let filteredObj = translations.filter((item, i) => {
                    if (item.lang === store.state.defaultLanguage.toLowerCase()) {
                        return translations[i].title;
                    }
                });
                if (filteredObj[0]) {
                    return filteredObj[0].title;
                } else {
                    let filtereObj = translations.filter((item, i) => {
                        if (item.lang === store.state.defaultTenantLanguage.toLowerCase()) {
                            return translations[i].title;
                        }
                    });

                    if (filtereObj[0]) {
                        return filtereObj[0].title;
                    }
                }
            }
        },
        getMediaPath(mediaPath) {
            if (mediaPath != "") {
                return mediaPath;
            } else {
                return ''
            }
        },
        // Is default media is video or not
        checkDefaultMediaFormat(mediaType) {
            return mediaType != constants.YOUTUBE_VIDEO_FORMAT;
        },
        // Check mission type
        checkMissionTypeTime(missionType) {
            return missionType == constants.MISSION_TYPE_TIME;
        },
        // Get Youtube Thumb images
        youtubeThumbImage(videoPath) {
            let data = videoPath.split("=");
            return (
                "https://img.youtube.com/vi/" + data.slice(-1)[0] + "/mqdefault.jpg"
            );
        },
        // Add mission to favorite
        favoriteMission(missionId) {
            let missionData = {
                mission_id: ""
            };
            missionData.mission_id = missionId;
            favoriteMission(missionData).then(response => {
                this.items.map(mission => {
                    if (mission.mission_id === missionId) {
                        mission.is_favourite = (mission.is_favourite === 0) ? 1 : 0;
                    }
                });

                if (response.error == true) {
                    this.makeToast("danger", response.message);
                } else {
                    this.makeToast("success", response.message);
                }
            });
        },
        
        makeToast(variant = null, message) {
            this.$bvToast.toast(message, {
                variant: variant,
                solid: true,
                autoHideDelay: 1000
            });
        },
        getAppliedStatus(missionDetail) {
            let currentDate = moment().format("YYYY-MM-DD HH::mm:ss");
            let missionEndDate = moment(missionDetail.end_date).format(
                "YYYY-MM-DD HH::mm:ss"
            );
            let checkEndDateExist = true;
            if (missionDetail.end_date != "" && missionDetail.end_date != null) {
                if (currentDate > missionEndDate) {
                    checkEndDateExist = false;
                }
            }
            if (missionDetail.user_application_count == 1 && checkEndDateExist) {
                return true;
            }
        },
        getClosedStatus(missionDetail) {
            let currentDate = moment().format("YYYY-MM-DD HH::mm:ss");
            let missionEndDate = moment(missionDetail.end_date).format(
                "YYYY-MM-DD HH::mm:ss"
            );
            if (missionDetail.end_date != "" && missionDetail.end_date != null) {
                if (currentDate > missionEndDate) {
                    return true;
                }
            }
        },
        submitNewMission() {
            if (this.submitNewMissionUrl != "") {
                window.open(this.submitNewMissionUrl, "_self");
            }
        },
        getFirstSkill(skills) {
            if (skills && skills[0]) {
                return skills[0].title;
            }
        },
        getRemainingSkill(skills) {
            return skills.filter((skill, index) => index !== 0);
        },
        compareDate(endDates, startDates) {
            const endDate = moment(endDates).format("YYYY-MM-DD");
            const startDate = moment(startDates).format("YYYY-MM-DD");

            if (startDate == endDate) {
                return true;
            }

            return false;
        },
        /*
         * Opens Recommend to a co-worker modal
         */
        handleModal(missionId) {
            this.currentMissionId = missionId;
            this.$refs.userDetailModal.show();
        },
        checkMissionTypeDonation(missionType) {
            if (constants.MISSION_TYPE_DONATION == missionType) {
                return true;
            } else {
                return false;
            }
        },
        checkMissionTypeGoal(missionType) {
            if (constants.MISSION_TYPE_GOAL == missionType) {
                return true;
            } else {
                return false;
            }
        },
        countDonationPercentage(donationAmountRaised, goalAmount) {
            if (donationAmountRaised && goalAmount) {
                return Math.round((100 * donationAmountRaised) / goalAmount);
            }
            return 0;
        },
        checkMissionTypeVolunteering(missionType) {
            return [constants.MISSION_TYPE_TIME, constants.MISSION_TYPE_GOAL].includes(missionType);
        }
    },
    created() {
        this.languageData = JSON.parse(store.state.languageLabel);
        this.isInviteColleagueDisplay = this.settingEnabled(
            constants.INVITE_COLLEAGUE
        );
        this.isStarRatingDisplay = this.settingEnabled(constants.MISSION_RATINGS);
        this.isQuickAccessSet = this.settingEnabled(constants.QUICK_ACCESS_FILTERS);
        this.isSubmitNewMissionSet = this.settingEnabled(
            constants.USER_CAN_SUBMIT_MISSION
        );
        this.isThemeSet = this.settingEnabled(constants.THEMES_ENABLED);
        this.submitNewMissionUrl = store.state.submitNewMissionUrl;
        this.isSkillDisplay = this.settingEnabled(constants.SKILLS_ENABLED);
        this.isVolunteeringSettingEnabled = this.settingEnabled(constants.VOLUNTERRING_ENABLED);
        this.isDonationSettingEnabled = this.settingEnabled(constants.DONATION_ENABLED);
        if (this.isDonationSettingEnabled && this.isVolunteeringSettingEnabled) {
            this.isDisplayMissionLabel = true;
        }
        this.isDonationMissionRatingEnabled = this.settingEnabled(constants.DONATION_MISSION_RATINGS);
        this.missionTypeLabels = JSON.parse(store.state.missionTypeLabels);
        if (JSON.parse(store.state.missionTypeLabels) != "") {
            let defaultLang = store.state.defaultLanguage.toLowerCase();
            this.missionTypeLabels.filter((item, i) => {
                // volunteering mission label
                if (item.type == constants.VOLUNTERRING_ENABLED) {
                    this.volunteeringMissionTypeLabels.icon = item.icon;
                    this.volunteeringMissionTypeLabels.backgroundColor = item.background_color;
                    let data = item.translations.filter(translationsItem => {
                        if (translationsItem.language_code == defaultLang) {
                            this.volunteeringMissionTypeLabels.label = translationsItem.description;
                        }
                    });
                    if (this.volunteeringMissionTypeLabels.label == "" && data[0] && data[0].description) {
                        this.volunteeringMissionTypeLabels.label = data[0].description;
                    }
                }
                // Donation mission label
                if (item.type.toLowerCase() == constants.DONATION_ENABLED) {

                    this.donationMissionTypeLabels.icon = item.icon;
                    this.donationMissionTypeLabels.backgroundColor = item.background_color;
                    let data = item.translations.filter(translationsItem => {
                        if (translationsItem.language_code == defaultLang) {
                            this.donationMissionTypeLabels.label = translationsItem.description;
                        }
                    });
                    if (this.donationMissionTypeLabels.label == "" && data[0] && data[0].description) {
                        this.donationMissionTypeLabels.label = data[0].description;
                    }
                }

            });
        }
    }
};
</script>
