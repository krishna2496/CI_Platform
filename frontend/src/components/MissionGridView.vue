<template>
    <div class="cards-wrapper" v-if="items.length > 0">
        <div v-bind:class="{ 'card-grid': !relatedMission }">
            <b-row>
                <b-col lg="4" sm="6" class="card-outer" :id="`gridview-${key}`" data-aos="fade-up"
                    v-for="(mission, key) in items" :key="key">
                    <div class="card-inner">
                        <b-card no-body>
                            <b-link target="_self" :to="getRedirectUrl(mission.mission_id, mission.mission_type)" class="location">
                                <i>
                                    <img :src="
                                    $store.state.imagePath + '/assets/images/location.svg'
                                    " :alt="languageData.label.location" />
                                </i>
                                {{ mission.city_name }}
                            </b-link>
                            <b-card-header>
                                <b-link target="_self" :to="getRedirectUrl(mission.mission_id, mission.mission_type)">
                                    <div class="header-img-block" v-bind:class="{

                                        'grayed-out': getClosedStatus(mission),

                                        'no-img':

                                        checkDefaultMediaFormat(mission.default_media_type) &&

                                        getMediaPath(mission.default_media_path) == '',

                                        }">
                                        <b-alert show class="alert card-alert alert-success"
                                            v-if="getAppliedStatus(mission)">
                                            {{ languageData.label.applied }}</b-alert>
                                        <b-alert show class="alert card-alert alert-warning"
                                            v-if="getClosedStatus(mission)">
                                            {{ languageData.label.closed }}</b-alert>
                                        <div v-if="checkDefaultMediaFormat(mission.default_media_type)"
                                            class="group-img" v-bind:class="{

                                            'd-none':

                                            checkDefaultMediaFormat(mission.default_media_type) &&

                                            getMediaPath(mission.default_media_path) == '',

                                            }" :style="{

                                            backgroundImage:

                                            'url(' +

                                            getMediaPath(mission.default_media_path) +

                                            ')',

                                            }">
                                            <img :src="getMediaPath(mission.default_media_path)"
                                                alt="mission.default_media_path" />
                                        </div>
                                        <div v-else class="group-img" :style="{
                                            backgroundImage:
                                            'url(' +
                                            youtubeThumbImage(mission.default_media_path) +
                                            ')',
                                            }"></div>
                                            <template v-if="
                                            checkDefaultMediaFormat(mission.default_media_type) &&
                                            getMediaPath(mission.default_media_path) == ''
                                            ">
                                            <i class="camera-icon">
                                                <img src="../assets/images/camera-ic.svg" />
                                            </i>
                                            <p>{{ languageData.label.no_image_available }}</p>
                                        </template>
                                    </div>
                                    <div class="group-category" v-if="
                                        mission.mission_theme != null &&
                                        isThemeSet &&
                                        getThemeTitle(mission.mission_theme.translations) != ''
                                        ">
                                        <span class="category-text">{{
                                        getThemeTitle(mission.mission_theme.translations)
                                        }}</span>
                                        </div>
                                </b-link>
                            </b-card-header>

                            <b-card-body>
                                <b-link target="_self" :to="getRedirectUrl(mission.mission_id, mission.mission_type)"
                                    class="content-block">
                                    <div class="content-inner-block">
                                        <div class="mission-label-wrap">
                                            <div class="mission-label volunteer-label" v-if="
                                                isDisplayMissionLabel &&
                                                checkMissionTypeVolunteering(mission.mission_type)
                                                ">
                                                <span :style="{
                                                    backgroundColor:
                                                    volunteeringMissionTypeLabels.backgroundColor,
                                                    }">
                                                    <i class="icon-wrap"><img :src="volunteeringMissionTypeLabels.icon"
                                                            alt="volunteer icon" /></i>{{ volunteeringMissionTypeLabels.label }}</span>
                                            </div>
                                            <div class="mission-label virtual-label" v-if="mission.is_virtual == 1">
                                                <span>{{ languageData.label.virtual_mission }}</span>
                                            </div>
                                            <div class="mission-label donation-label" v-if="
                                                isDisplayMissionLabel &&
                                                checkMissionTypeDonation(mission.mission_type)
                                                ">
                                                <span :style="{
                                                    backgroundColor:
                                                    donationMissionTypeLabels.backgroundColor,
                                                    }"><i class="icon-wrap"><img :src="donationMissionTypeLabels.icon"
                                                    alt="donation icon" /></i>{{ donationMissionTypeLabels.label }}</span>
                                            </div>
                                        </div>
                                        <div class="card-title mb-2"
                                            v-if="checkMissionTypeVolunteering(mission.mission_type)">
                                            {{ mission.title | substring(60) }}
                                        </div>

                                        <div class="group-ratings" v-if="
                                            checkMissionTypeTime(mission.mission_type) ||
                                            checkMissionTypeGoal(mission.mission_type)
                                        ">
                                            <star-rating v-if="isStarRatingDisplay" v-bind:increment="0.5"
                                                v-bind:max-rating="5" inactive-color="#dddddd" active-color="#F7D341"
                                                v-bind:star-size="18" :rating="mission.mission_rating_count"
                                                :read-only="true">
                                            </star-rating>
                                        </div>
                                        <div class="group-ratings"
                                            v-if="checkMissionTypeDonation(mission.mission_type)">
                                            <star-rating v-if="isDonationMissionRatingEnabled" v-bind:increment="0.5"
                                                v-bind:max-rating="5" inactive-color="#dddddd" active-color="#F7D341"
                                                v-bind:star-size="18" :rating="mission.mission_rating_count"
                                                :read-only="true">
                                            </star-rating>
                                        </div>

                                        <b-card-text>
                                            {{ mission.short_description | substring(105) }}
                                        </b-card-text>
                                    </div>
                                    <div class="event-block has-progress">
                                        <p class="event-name" v-if="mission.organization != null">
                                            {{ languageData.label.for }}
                                            <span>{{ mission.organization.name }}</span>
                                        </p>
                                        <!-- added -->
                                        <!-- donation -->
                                        <div class="progress-block detail-column " v-if="
                                            mission.donation_attribute && 
                                            checkMissionTypeDonation(mission.mission_type) &&

                                            mission.donation_attribute.show_donation_meter

                                            ">
                                            <div class="text-wrap">
                                                <b-progress :value="

                                                mission.donation_statistics.total_amount

                                                " :max="mission.donation_attribute.goal_amount"></b-progress>
                                                <div class="progress-info">
                                                    <span class="subtitle-text">
                                                        <em>
                                                            {{

                                                                countDonationPercentage(

                                                                mission.donation_statistics

                                                                .total_amount,

                                                                mission.donation_attribute.goal_amount

                                                                )

                                                                }}%
                                                        </em>
                                                        <em>{{ languageData.label.achieved }}</em>
                                                    </span>
                                                    <span class="subtitle-text">
                                                        <em v-if="mission.user_currency"><b>{{mission.user_currency.symbol}}{{

                                                        mission.donation_attribute.goal_amount

                                                        }}</b></em>
                                                        <em>{{ languageData.label.goal }}</em>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="progress-block detail-column success-donate" v-if="

                                            checkMissionTypeDonation(mission.mission_type) &&
                                            mission.donation_attribute && 
                                            !mission.donation_attribute.show_donation_meter

                                            ">
                                            <div class="text-wrap">
                                                <p>
                                                    <b class="donate-success" v-if="
                                                    mission.donation_attribute.show_donation_count
                                                    ">
                                                    <template v-if="mission.user_currency">{{mission.user_currency.symbol}}</template>{{
                                                    mission.donation_statistics.total_amount
                                                    }}</b>
                                                    <span v-if="
                                                        mission.donation_attribute.show_donation_count
                                                    ">
                                                        {{ languageData.label.raised_by }}</span>
                                                    <span v-if="
                                                        mission.donation_statistics.donors &&
                                                        mission.donation_attribute.show_donation_count
                                                        ">
                                                        {{ languageData.label.by }}
                                                    </span>
                                                    <span
                                                        v-if="mission.donation_statistics.donors">{{ mission.donation_attribute.donor_count }}
                                                        {{ languageData.label.donors }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <b-button class="like-btn">
                                            <img v-if="mission.is_favourite == 1" :src="

                                            $store.state.imagePath +

                                            '/assets/images/heart-fill-icon.svg'

                                            " alt="Heart Icon" />
                                        </b-button>
                                        <!-- added end -->
                                    </div>
                                </b-link>
                                <div class="init-hidden">
                                    <div class="group-details" v-bind:class="{
                                        'mb-3': !isContentBlockDisplay(mission),
                                        }">
                                        <div class="top-strip">
                                            <span>
                                                <!-- Mission type time -->
                                                <template v-if="checkMissionTypeTime(mission.mission_type)">
                                                    <template v-if="mission.end_date !== null">
                                                        <template v-if="
                                                                !compareDate(
                                                                mission.end_date,
                                                                mission.start_date
                                                                )
                                                            ">
                                                            {{ languageData.label.from }}
                                                            {{ mission.start_date | formatDate }}
                                                            {{ languageData.label.until }}
                                                            {{ mission.end_date | formatDate }}
                                                        </template>
                                                        <template v-else>
                                                            {{ languageData.label.on }}
                                                            {{ mission.start_date | formatDate }}
                                                        </template>
                                                    </template>
                                                    <template v-else>
                                                        {{ languageData.label.ongoing }}
                                                    </template>
                                                </template>
                                                <!-- Mission type goal -->
                                                <template v-else>
                                                    <template v-if="mission.objective != ''">
                                                        {{ mission.objective }}
                                                    </template>
                                                </template>
                                            </span>
                                        </div>
                                        <div class="content-wrap" v-if="isContentBlockDisplay(mission)">
                                            <template v-if="checkMissionTypeTime(mission.mission_type)">
                                                <div class="group-details-inner">
                                                    <template v-if="
                                                        mission.seats_left &&
                                                        mission.seats_left != 0 &&
                                                        mission.seats_left !== null
                                                        ">
                                                        <div class="detail-column info-block">
                                                            <i class="icon-wrap">
                                                                <img :src="
                                                                    $store.state.imagePath +
                                                                    '/assets/images/user-icon.svg'
                                                                " alt="user" />
                                                            </i>
                                                            <div class="text-wrap">
                                                                <span class="title-text mb-1">{{
                                                                mission.seats_left
                                                                }}</span>
                                                                <span class="subtitle-text">{{
                                                                languageData.label.seats_left
                                                                }}</span>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <template v-if="mission.application_deadline != null">
                                                        <div class="detail-column info-block">
                                                            <i class="icon-wrap">
                                                                <img :src="
                                                                    $store.state.imagePath +
                                                                    '/assets/images/clock.svg'
                                                                " alt="user" />
                                                            </i>
                                                            <div class="text-wrap">
                                                                <span class="title-text mb-1">{{
                                  mission.application_deadline | formatDate
                                }}</span>
                                                                <span class="subtitle-text">{{
                                  languageData.label.deadline
                                }}</span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template v-if="checkMissionTypeGoal(mission.mission_type)">
                                                <div class="group-details-inner volunteer-progress">
                                                    <div class="detail-column info-block" v-if="
                              mission.seats_left &&
                              mission.seats_left != '' &&
                              mission.seats_left != 0
                            ">
                                                        <i class="icon-wrap">
                                                            <img :src="
                                  $store.state.imagePath +
                                  '/assets/images/user-icon.svg'
                                " alt="user" />
                                                        </i>
                                                        <div class="text-wrap">
                                                            <span class="title-text mb-1">{{
                                mission.seats_left
                              }}</span>
                                                            <span class="subtitle-text">{{
                                languageData.label.seats_left
                              }}</span>
                                                        </div>
                                                    </div>
                                                    <div v-bind:class="{
                              'progress-bar-block': !(
                                mission.seats_left && mission.seats_left != ''
                              ),
                              'detail-column': true,
                              'progress-block': true,
                            }">
                                                        <i class="icon-wrap">
                                                            <img :src="
                                  $store.state.imagePath +
                                  '/assets/images/target-ic.svg'
                                " alt="user" />
                                                        </i>
                                                        <div class="text-wrap">
                                                            <b-progress :value="mission.achieved_goal | filterGoal"
                                                                :max="mission.goal_objective">
                                                            </b-progress>
                                                            <span class="subtitle-text">
                                                                {{ mission.achieved_goal }}
                                                                <em v-if="mission.label_goal_achieved != ''">
                                                                    {{ mission.label_goal_achieved }}
                                                                </em>
                                                                <em v-else>{{
                                  languageData.label.achieved
                                }}</em>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <div class="group-details-inner has-progress">
                                                <div class="progress-block detail-column progress-bar-block" v-if="
                          checkMissionTypeDonation(mission.mission_type) && mission.donation_attribute &&
                            mission.donation_attribute && mission.donation_attribute.show_donation_meter
                        ">
                                                    <div class="text-wrap">
                                                        <b-progress :value="
                              mission.donation_statistics.total_amount
                            " :max="mission.donation_attribute.goal_amount"></b-progress>
                                                        <div class="progress-info">
                                                            <span class="subtitle-text">
                                                                <em>
                                                                    {{
                                  countDonationPercentage(
                                    mission.donation_statistics
                                      .total_amount,
                                    mission.donation_attribute.goal_amount
                                  )
                                }}%
                                                                </em>
                                                                <em>{{ languageData.label.achieved }}</em>
                                                            </span>
                                                            <span class="subtitle-text">
                                                                <em><b v-if="mission.user_currency">{{mission.user_currency.symbol}}{{
                                    mission.donation_attribute.goal_amount
                                  }}</b></em>
                                                                <em>{{ languageData.label.goal }}</em>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="progress-block detail-column success-donate" v-if="
                          checkMissionTypeDonation(mission.mission_type) && mission.donation_attribute &&
                            !mission.donation_attribute.show_donation_meter
                        ">
                                                    <div class="text-wrap">
                                                        <p>
                                                            <b class="donate-success" v-if="
                                mission.donation_attribute.show_donation_count
                              ">${{
                                mission.donation_statistics.total_amount
                              }}</b>
                                                            <span v-if="
                                mission.donation_attribute.show_donation_count
                              ">
                                                                {{ languageData.label.raised_by }}</span>
                                                            <span v-if="
                                mission.donation_statistics.donors &&
                                  mission.donation_attribute.show_donation_count
                              ">
                                                                {{ languageData.label.by }}
                                                            </span>
                                                            <span
                                                                v-if="mission.donation_statistics.donors">{{ mission.donation_attribute.donor_count }}
                                                                {{ languageData.label.donors }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-action-block">
                                        <div class="left-btn">
                                            <b-link :to="getRedirectUrl(mission.mission_id, mission.mission_type)"
                                                class="btn-bordersecondary icon-btn" v-bind:class="{
                                                'btn-lg': languageData.label.view_detail.length > 12,
                                                }">
                                                <span>{{
                                                languageData.label.view_detail
                                                    | substringWithOutDot(36)
                                                }}</span>
                                                <i class="icon-wrap">
                                                    <svg width="18" height="9" viewBox="0 0 18 9" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M17.3571 4.54129C17.3571 4.63504 17.3237 4.7154 17.2567 4.78237L13.3996 8.33817C13.2924 8.43192 13.1752 8.45201 13.048 8.39844C12.9208 8.33817 12.8571 8.24107 12.8571 8.10714V5.85714H0.321429C0.227679 5.85714 0.15067 5.82701 0.0904018 5.76674C0.0301339 5.70647 0 5.62946 0 5.53571V3.60714C0 3.51339 0.0301339 3.43638 0.0904018 3.37612C0.15067 3.31585 0.227679 3.28571 0.321429 3.28571H12.8571V1.03571C12.8571 0.895089 12.9208 0.797991 13.048 0.744419C13.1752 0.690848 13.2924 0.707589 13.3996 0.794642L17.2567 4.31027C17.3237 4.37723 17.3571 4.45424 17.3571 4.54129Z"
                                                            fill="#ffffff" />
                                                    </svg>
                                                </i>
                                            </b-link>
                                        </div>
                                        <div class="social-btn">
                                            <b-button class="icon-btn" v-if="isInviteColleagueDisplay" v-b-tooltip.hover
                                                :title="languageData.label.recommend_to_co_worker"
                                                @click="handleModal(mission.mission_id)">
                                                <img :src="
                            $store.state.imagePath +
                              '/assets/images/multi-user-icon.svg'
                          " alt="multi user icon" />
                                            </b-button>

                                            <b-button v-bind:class="{
                          'icon-btn': true,

                          'fill-heart-btn': mission.is_favourite == 1,
                        }" :title="
                          mission.is_favourite == 1
                            ? languageData.label.remove_from_favourite
                            : languageData.label.add_to_favourite
                        " @click="favoriteMission(mission.mission_id)">
                                                <img v-if="mission.is_favourite == 0" :src="
                            $store.state.imagePath +
                              '/assets/images/heart-icon.svg'
                          " alt="heart icon" />
                                                <img v-if="mission.is_favourite == 1" :src="
                            $store.state.imagePath +
                              '/assets/images/heart-fill-icon.svg'
                          " alt="heart icon" />
                                            </b-button>
                                        </div>
                                    </div>
                                </div>
                            </b-card-body>
                        </b-card>
                    </div>
                </b-col>
            </b-row>
        </div>
        <invite-co-worker ref="userDetailModal" entity-type="MISSION" :entity-id="currentMissionId"></invite-co-worker>
    </div>
    <div class="no-data-found" v-else>
        <h2 class="text-center">{{ noRecordFound() }}</h2>
        <div class="btn-wrap" v-if="isSubmitNewMissionSet" @click="submitNewMission">
            <b-button class="btn-bordersecondary icon-btn">
                <span>{{ languageData.label.submit_new_mission }}</span>
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 16" width="19" height="15">
                        <g id="Main Content">
                            <g id="1">
                                <g id="Button">
                                    <path id="Forma 1 copy 12" class="shp0"
                                        d="M16.49,1.22c-0.31,-0.3 -0.83,-0.3 -1.16,0c-0.31,0.29 -0.31,0.77 0,1.06l5.88,5.44h-19.39c-0.45,0 -0.81,0.33 -0.81,0.75c0,0.42 0.36,0.76 0.81,0.76h19.39l-5.88,5.43c-0.31,0.3 -0.31,0.78 0,1.07c0.32,0.3 0.85,0.3 1.16,0l7.27,-6.73c0.32,-0.29 0.32,-0.77 0,-1.06z" />
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
    import StarRating from 'vue-star-rating';
    import {
        favoriteMission,
        applyMission,
    } from "../services/service";
    import moment from "moment";
    import InviteCoWorker from "@/components/InviteCoWorker";

    export default {
        name: "MissionGridView",
        components: {
            StarRating,
            InviteCoWorker,
        },
        props: {
            items: Array,
            relatedMission: Boolean,
        },
        data() {
            return {
                currentMissionId: 0,
                isInviteColleagueDisplay: true,
                isStarRatingDisplay: true,
                isSubmitNewMissionSet: true,
                isThemeSet: true,
                languageData: [],
                message: null,
                submitNewMissionUrl: "",
                cardHeightAdjIntervalId: null,
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
            getAppliedStatus(missionDetail) {
                const currentDate = moment().format("YYYY-MM-DD");
                const missionEndDate = moment(missionDetail.end_date).format(
                    "YYYY-MM-DD"
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
                const currentDate = moment().format("YYYY-MM-DD");
                const missionEndDate = moment(missionDetail.end_date).format(
                    "YYYY-MM-DD"
                );
                if (missionDetail.end_date != "" && missionDetail.end_date != null) {
                    if (currentDate > missionEndDate) {
                        return true;
                    }
                }
            },
            // No record found
            noRecordFound() {
                const defaultLang = store.state.defaultLanguage.toLowerCase();
                if (JSON.parse(store.state.missionNotFoundText) != "") {
                    const missionNotFoundArray = JSON.parse(
                        store.state.missionNotFoundText
                    );
                    const data = missionNotFoundArray.filter((item) => {
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
            handleFav() {
                const btn_active = document.querySelector(".favourite-icon");
                btn_active.classList.toggle("active");
            },
            // get theme title
            getThemeTitle(translations) {
                if (translations) {
                    const filteredObj = translations.filter((item, i) => {
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
            // Is default media is video or not
            checkDefaultMediaFormat(mediaType) {
                return mediaType != constants.YOUTUBE_VIDEO_FORMAT;
            },
            // Check mission type
            checkMissionTypeTime(missionType) {
                return missionType == constants.MISSION_TYPE_TIME;
            },
            // Check mission type
            checkMissionTypeVolunteering(missionType) {
                if (
                    missionType == constants.MISSION_TYPE_TIME ||
                    missionType == constants.MISSION_TYPE_GOAL
                ) {
                    return true;
                }
                return false;
            },
            // Get Youtube Thumb images
            youtubeThumbImage(videoPath) {
                let data = videoPath.split("=");
                return `https://img.youtube.com/vi/${data.slice(-1)[0]}/mqdefault.jpg`;
            },
            // Add mission to favorite
            favoriteMission(missionId) {
                const bodyTag = document.querySelector("body");
                bodyTag.classList.add("has-favourite");
                const missionData = {
                    mission_id: "",
                };
                missionData.mission_id = missionId;
                favoriteMission(missionData).then((response) => {
                    this.items.map(mission => {
                        if (mission.mission_id === missionId) {
                            mission.is_favourite = (mission.is_favourite === 0) ? 1 : 0;
                        }
                    });

                    if (response.error == true) {
                        this.makeToast('danger', response.message);
                    } else {
                        this.makeToast('success', response.message);
                    }
                });
            },
            getMediaPath(mediaPath) {
                if (mediaPath != "") {
                    return mediaPath;
                } else {
                    return "";
                }
            },
            
            makeToast(variant = null, message) {
                this.$bvToast.toast(message, {
                    variant: variant,
                    solid: true,
                    autoHideDelay: 1000,
                });
            },
            submitNewMission() {
                if (this.submitNewMissionUrl != "") {
                    window.open(this.submitNewMissionUrl, "_self");
                }
            },
            cardHeightAdj() {
                const cardBodyList = document.querySelectorAll('.card-grid .card-body');
                // check if card content is already visible in the DOM
                if (cardBodyList.length > 0) {
                    if (!cardBodyList[0].children[0].offsetHeight) {
                        return;
                    }

                    cardBodyList.forEach((cardBody) => {
                        const card = cardBody.parentNode;
                        const cardHeight =
                            cardBody.children[0].offsetHeight + card.children[1].clientHeight;
                        const cardHeaderHeight = card.querySelector(".card-header")
                            .offsetHeight;
                        const contentBlock = cardBody.querySelector(".content-block");
                        card.style.height = `${cardHeight}px`;

                        if (screen.width > 1024) {
                            cardBody.parentNode.addEventListener("mouseover", function (
                                mouseEvent
                            ) {
                                this.children[2].children[1].style.display = "block";
                                if (!this.parentNode.classList.contains("active")) {
                                    const eventProgessBlock = this.querySelector(".event-block .progress-block");
                                    const eventProgessBlockH = eventProgessBlock ? eventProgessBlock.offsetHeight : 0;
                                    const cardBodyH =
                                        this.children[2].children[1].offsetHeight +
                                        this.children[2].children[0].offsetHeight +
                                        this.children[1].offsetHeight;
                                    const cardTotalHeight = cardBodyH - this.offsetHeight;
                                    this.parentNode.classList.add("active");
                                    const ratingBlock = this.querySelector(".group-ratings");
                                    const ratingBlockH = ratingBlock ? 18 : 0;
                                    this.children[1].style.transform = `translateY(-${cardTotalHeight + ratingBlockH - eventProgessBlockH}px)`;
                                    this.children[2].style.transform = `translateY(-${cardTotalHeight + ratingBlockH - eventProgessBlockH}px)`;
                                }
                            });

                            cardBody.parentNode.addEventListener("mouseleave", function () {
                                if (!document.body.classList.contains("modal-open")) {
                                    this.children[1].style.transform = "translateY(0)";
                                    this.children[2].style.transform = "translateY(0)";
                                    this.parentNode.classList.remove("active");
                                }
                            });
                        }
                    });

                    if (this.cardHeightAdjIntervalId) {
                        clearInterval(this.cardHeightAdjIntervalId);
                    }
                }
            },

            checkMissionTypeGoal(missionType) {
                if (constants.MISSION_TYPE_GOAL == missionType) {
                    return true;
                } else {
                    return false;
                }
            },

            compareDate(endDates, startDates) {
                const endDate = moment(endDates).format("YYYY-MM-DD");
                const startDate = moment(startDates).format("YYYY-MM-DD");

                if (startDate == endDate) {
                    return true;
                }

                return false;
            },

            isContentBlockDisplay(mission) {
                if (mission.mission_type == constants.MISSION_TYPE_TIME) {
                    if (
                        (mission.seats_left &&
                            mission.seats_left != 0 &&
                            mission.seats_left !== null) ||
                        mission.application_deadline != null
                    ) {
                        return true;
                    }
                    return false;
                } else {
                    return true;
                }
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
            getRedirectUrl(missionId, missionType) {
                if (
                    missionType == constants.MISSION_TYPE_TIME ||
                    missionType == constants.MISSION_TYPE_GOAL
                ) {
                    return "/mission-detail/" + missionId;
                } else {
                    return "/donation-mission-detail/" + missionId;
                }
            }
        },
        created() {
            this.languageData = JSON.parse(store.state.languageLabel);
            this.isInviteColleagueDisplay = this.settingEnabled(
                constants.INVITE_COLLEAGUE
            );
            this.isStarRatingDisplay = this.settingEnabled(constants.MISSION_RATINGS);
            this.isSubmitNewMissionSet = this.settingEnabled(
                constants.USER_CAN_SUBMIT_MISSION
            );
            this.isThemeSet = this.settingEnabled(constants.THEMES_ENABLED);
            this.submitNewMissionUrl = store.state.submitNewMissionUrl;
            this.isVolunteeringSettingEnabled = this.settingEnabled(
                constants.VOLUNTERRING_ENABLED
            );
            this.isDonationSettingEnabled = this.settingEnabled(
                constants.DONATION_ENABLED
            );

            this.isDonationMissionRatingEnabled = this.settingEnabled(
                constants.DONATION_MISSION_RATINGS
            );
            if (this.isDonationSettingEnabled && this.isVolunteeringSettingEnabled) {
                this.isDisplayMissionLabel = true;
            }

            this.missionTypeLabels = JSON.parse(store.state.missionTypeLabels);
            if (JSON.parse(store.state.missionTypeLabels) != "") {
                let defaultLang = store.state.defaultLanguage.toLowerCase();
                this.missionTypeLabels.filter((item, i) => {
                    // volunteering mission label
                    if (item.type.toLowerCase() == constants.VOLUNTERRING_ENABLED) {
                        this.volunteeringMissionTypeLabels.icon = item.icon;
                        this.volunteeringMissionTypeLabels.backgroundColor =
                            item.background_color;
                        let data = item.translations.filter((translationsItem) => {
                            if (translationsItem.language_code == defaultLang) {
                                this.volunteeringMissionTypeLabels.label =
                                    translationsItem.description;
                            }
                        });
                        if (
                            this.volunteeringMissionTypeLabels.label == "" &&
                            data[0] &&
                            data[0].description
                        ) {
                            this.volunteeringMissionTypeLabels.label = data[0].description;
                        }
                    }

                    if (item.type.toLowerCase() == constants.DONATION_ENABLED) {
                        this.donationMissionTypeLabels.icon = item.icon;
                        this.donationMissionTypeLabels.backgroundColor =
                            item.background_color;
                        let data = item.translations.filter((translationsItem) => {
                            if (translationsItem.language_code == defaultLang) {
                                this.donationMissionTypeLabels.label =
                                    translationsItem.description;
                            }
                        });
                        if (
                            this.donationMissionTypeLabels.label == "" &&
                            data[0] &&
                            data[0].description
                        ) {
                            this.donationMissionTypeLabels.label = data[0].description;
                        }
                    }
                });
            }
        },
        mounted() {
            this.cardHeightAdjIntervalId = setInterval(this.cardHeightAdj, 500);
        },
    };
</script>