<template>
    <div class="platform-page inner-pages">
        <header>
            <ThePrimaryHeader v-if="isShownComponent"></ThePrimaryHeader>
        </header>

        <main>
            <b-container>
                <div class="slider-banner-block">
                    <b-row>
                        <b-col lg="6" class="slider-col">
                            <MissionCarousel v-if="isShownMediaComponent"
                                             @defaultMediaPathDetail="defaultMediaPathDetail"></MissionCarousel>
                        </b-col>
                        <b-col lg="6" class="ml-auto banner-content-wrap">
                            <div class="banner-content-block">
                                <h1>{{missionDetail.title}}</h1>
                                <div
                                        v-bind:class="{'rating-with-btn' : true , 'justify-content-end' : !isStarDisplay }">
                                    <div class="rating-block" v-if="isStarDisplay">
                                        <star-rating
                                                :read-only="isStarRatingDisable"
                                                v-bind:increment="0.5" v-bind:max-rating="5"
                                                inactive-color="#dddddd" active-color="#F7D341" v-bind:star-size="23"
                                                :rating="missionDetail.rating" @rating-selected="setRating">
                                        </star-rating>
                                    </div>
                                    <div class="btn-outer">
                                        <b-button v-bind:class="{
											'btn-borderprimary': true,
											'icon-btn': true,
											'added-fav' : missionAddedToFavoriteByUser
										}" @click="favoriteMission(missionId)">
                                            <i class="normal-img">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 21" width="24"
                                                     height="21">
                                                    <g id="Main Content">
                                                        <g id="1">
                                                            <g id="Image content">
                                                                <path id="Forma 1"
                                                                      d="M22.1 2.86C20.9 1.66 19.3 1 17.59 1C15.89 1 14.29 1.66 13.08 2.86L12.49 3.45L11.89 2.86C10.69 1.66 9.08 1 7.38 1C5.67 1 4.07 1.66 2.87 2.86C0.38 5.34 0.38 9.36 2.87 11.84L11.78 20.71C11.93 20.86 12.11 20.95 12.3 20.98C12.36 20.99 12.43 21 12.49 21C12.74 21 13 20.9 13.19 20.71L22.1 11.84C24.59 9.36 24.59 5.34 22.1 2.86ZM20.71 10.45L12.49 18.64L4.26 10.45C2.54 8.74 2.54 5.96 4.26 4.25C5.09 3.42 6.2 2.96 7.38 2.96C8.56 2.96 9.66 3.42 10.5 4.25L11.79 5.53C12.16 5.9 12.81 5.9 13.18 5.53L14.47 4.25C15.31 3.42 16.41 2.96 17.59 2.96C18.77 2.96 19.88 3.42 20.71 4.25C22.43 5.96 22.43 8.74 20.71 10.45Z" />
                                                            </g>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </i>
                                            <i class="hover-img">
                                                <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                                                     xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                     viewBox="0 0 492.7 426.8"
                                                     style="enable-background:new 0 0 492.7 426.8;" xml:space="preserve">
                                                    <g>
                                                        <g id="Icons_18_">
                                                            <path d="M492.7,133.1C492.7,59.6,433.1,0,359.7,0c-48,0-89.9,25.5-113.3,63.6C222.9,25.5,181,0,133,0
													C59.6,0,0,59.6,0,133.1c0,40,17.7,75.8,45.7,100.2l188.5,188.6c3.2,3.2,7.6,5,12.1,5s8.9-1.8,12.1-5L447,233.2
													C475,208.9,492.7,173.1,492.7,133.1z" />
                                                        </g>
                                                    </g>
                                                </svg>
                                            </i>
                                            <span v-if="missionAddedToFavoriteByUser">
                                                {{ languageData.label.remove_from_favourite }}
                                            </span>

                                            <span v-else>
                                                {{ languageData.label.add_to_favourite }}
                                            </span>

                                        </b-button>
                                        <!-- <b-button class="btn-borderprimary icon-btn btn-add-entry" v-if="allowAddEntry"
									@click="addEntry">
									Add entry
								</b-button> -->
                                    </div>
                                </div>

                                <p>{{missionDetail.short_description}}</p>
                                <div class="share-block">
                                    <social-sharing v-bind:url="socialSharingUrl" :title="missionDetail.title"
                                                    :description="missionDetail.short_description" inline-template>
                                        <div class="social-block">
                                            <network network="facebook" v-if="$store.state.isFacebookDisplay"
                                                     class="social-icon">
                                                <img :src="$store.state.imagePath+'/assets/images/facebook-ic-gray.svg'"
                                                     :alt="`${JSON.parse(this.$store.state.languageLabel).label.facebook}`"
                                                     :title="`${JSON.parse(this.$store.state.languageLabel).label.facebook}`"
                                                     class="normal-img" />
                                                <img :src="$store.state.imagePath+'/assets/images/facebook-ic-gray-h.svg'"
                                                     :alt="`${JSON.parse(this.$store.state.languageLabel).label.facebook}`"
                                                     :title="`${JSON.parse(this.$store.state.languageLabel).label.facebook}`"
                                                     class="hover-img" />

                                            </network>
                                            <network network="twitter" v-if="$store.state.isTwitterDisplay"
                                                     class="social-icon">
                                                <img :src="$store.state.imagePath+'/assets/images/twitter-ic-gray.svg'"
                                                     :alt="`${JSON.parse(this.$store.state.languageLabel).label.twitter}`"
                                                     :title="`${JSON.parse(this.$store.state.languageLabel).label.twitter}`"
                                                     class="normal-img" />
                                                <img :src="$store.state.imagePath+'/assets/images/twitter-ic-gray-h.svg'"
                                                     :alt="`${JSON.parse(this.$store.state.languageLabel).label.twitter}`"
                                                     :title="`${JSON.parse(this.$store.state.languageLabel).label.twitter}`"
                                                     class="hover-img" />
                                            </network>
                                        </div>
                                    </social-sharing>
                                </div>
                                <div class="group-details">
                                    <div class="top-strip">
                                        <span>
                                            <!-- Mission type time -->
                                            <template v-if="checkMissionTypeTime(missionDetail.mission_type)">
                                                <template v-if="missionDetail.end_date !== null">
                                                    {{ languageData.label.from }}
                                                    {{missionDetail.start_date | formatDate }}
                                                    {{ languageData.label.until}}
                                                    {{ missionDetail.end_date | formatDate }}
                                                </template>
                                                <template v-else>
                                                    {{ languageData.label.ongoing }}
                                                </template>
                                            </template>
                                            <!-- Mission type goal -->
                                            <template v-else>
                                                {{missionDetail.objective}}
                                            </template>
                                        </span>
                                    </div>
                                    <template v-if="checkMissionTypeTime(missionDetail.mission_type)">
                                        <div class="group-details-inner">
                                            <template
                                                    v-if="missionDetail.total_seats != 0 && missionDetail.total_seats !== null">
                                                <div class="detail-column info-block">
                                                    <i class="icon-wrap">
                                                        <img :src="$store.state.imagePath+'/assets/images/user-icon.svg'"
                                                             alt="user">

                                                    </i>
                                                    <div class="text-wrap">
                                                        <span
                                                                class="title-text mb-1">{{missionDetail.seats_left}}</span>
                                                        <span
                                                                class="subtitle-text">{{ languageData.label.seats_left }}</span>
                                                    </div>
                                                </div>
                                            </template>

                                            <template>
                                                <div class="detail-column info-block" v-if="(missionDetail.application_deadline != '' && missionDetail.application_deadline != null) || (missionDetail.application_start_date != null && missionDetail.application_end_date != null )">
                                                    <i class="icon-wrap">
                                                        <img :src="$store.state.imagePath+'/assets/images/clock.svg'"
                                                             alt="user">
                                                    </i>
                                                    <div class="text-wrap"
                                                         v-if="missionDetail.application_deadline != '' && missionDetail.application_deadline != null">
                                                        <span
                                                                class="title-text mb-1">{{missionDetail.application_deadline | formatDate}}
                                                        </span>
                                                        <span class="subtitle-text">{{ languageData.label.deadline }}
                                                        </span>
                                                    </div>
                                                    <div v-else class="text-wrap">
                                                        <span class="title-text mb-1"
                                                              v-if="missionDetail.application_start_date != '' && missionDetail.application_start_date != null && missionDetail.application_end_date != '' && missionDetail.application_end_date != null">
                                                            <span
                                                                v-if="missionDetail.application_start_date != '' && missionDetail.application_start_date != null">
                                                                {{missionDetail.application_start_date | formatDate}}
                                                                {{missionDetail.application_start_time | formatTime}}
                                                            </span>
                                                            <span
                                                                    v-if="missionDetail.application_end_date != '' && missionDetail.application_end_date != null">
                                                                {{ languageData.label.until }}
                                                                {{missionDetail.application_end_date | formatDate}}
                                                                {{missionDetail.application_end_time | formatTime}}
                                                            </span>
                                                        </span>
                                                        <span class="subtitle-text"
                                                            v-if="missionDetail.application_start_date != '' && missionDetail.application_start_date != null && missionDetail.application_end_date != '' && missionDetail.application_end_date != null">
                                                            {{ languageData.label.registration_period }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="group-details-inner has-progress">
                                            <div class="detail-column info-block" v-if="missionDetail.total_seats != 0 && missionDetail.total_seats !== null">
                                                <template>
                                                    <i class="icon-wrap">
                                                        <img :src="$store.state.imagePath+'/assets/images/user-icon.svg'"
                                                             alt="user">
                                                    </i>
                                                    <div class="text-wrap">
                                                        <span
                                                                class="title-text mb-1">{{missionDetail.seats_left}}</span>
                                                        <span
                                                                class="subtitle-text">{{ languageData.label.seats_left }}</span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div
                                              v-bind:class="{
                                                'progress-bar-block': (missionDetail.total_seats == 0 || missionDetail.total_seats === null),
                                                'detail-column' : true,
                                                'progress-block' :true
                                              }"
                                            >
                                                <i class="icon-wrap">
                                                    <img :src="$store.state.imagePath+'/assets/images/target-ic.svg'"
                                                         alt="user">
                                                </i>
                                                <div class="text-wrap">
                                                    <b-progress :value="parseInt(missionDetail.achieved_goal)"
                                                        :max="missionDetail.goal_objective" class="mb-2"></b-progress>
                                                        <span class="subtitle-text">
                                                        {{missionDetail.achieved_goal}}
                                                        <span
                                                            v-if="missionDetail.label_goal_achieved != ''">
                                                            {{ missionDetail.label_goal_achieved }}
                                                        </span>
                                                        <span v-else>{{ languageData.label.achieved }}</span>
                                                        </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <b-list-group class="info-box">
                                    <b-list-group-item>
                                        <div>
                                            <i class="img-wrap">
                                                <img :src="$store.state.imagePath+'/assets/images/location-black.svg'"
                                                     alt="" />
                                            </i>
                                            <span class="label">{{ languageData.label.city}}</span>
                                            <p class="text-wrap">{{missionDetail.city_name}}</p>
                                        </div>
                                    </b-list-group-item>
                                    <b-list-group-item v-if="isThemeDisplay && getThemeTitle(missionDetail.mission_theme)">
                                        <div>
                                            <i class="img-wrap">
                                                <img :src="$store.state.imagePath+'/assets/images/earth-ic.svg'"
                                                     alt="" />
                                            </i>
                                            <span class="label">{{ languageData.label.theme}}</span>
                                            <p class="text-wrap">{{getThemeTitle(missionDetail.mission_theme)}}</p>
                                        </div>
                                    </b-list-group-item>
                                    <b-list-group-item>

                                        <div>
                                            <i class="img-wrap">
                                                <img :src="$store.state.imagePath+'/assets/images/calendar.svg'"
                                                     alt="" />
                                            </i>
                                            <span class="label">{{ languageData.label.start_date}}</span>
                                            <template
                                                    v-if="missionDetail.start_date != '' && missionDetail.start_date != null && missionDetail.end_date != '' && missionDetail.end_date != null">
                                                <p class="text-wrap">{{missionDetail.start_date | formatDate}}</p>
                                            </template>
                                            <template v-else>
                                                <p class="text-wrap">{{ languageData.label.ongoing }}</p>
                                            </template>

                                        </div>
                                    </b-list-group-item>
                                    <b-list-group-item>
                                        <div>
                                            <i class="img-wrap">
                                                <img :src="$store.state.imagePath+'/assets/images/group-ic.svg'"
                                                     alt="" />
                                            </i>
                                            <span class="label">{{ languageData.label.organisation}}</span>
                                            <p class="text-wrap">{{missionDetail.organization ? missionDetail.organization.name : ''}}</p>
                                        </div>
                                    </b-list-group-item>
                                </b-list-group>

                                <div class="btn-wrap group-btns">
                                    <b-button class="btn-borderprimary icon-btn" @click="handleModal(missionId)"
                                              v-if="isInviteCollegueDisplay">
                                        <i>
                                            <svg height="512pt" viewBox="0 0 512 512" width="512pt"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                        d="m512 428h-84v84h-40v-84h-84v-40h84v-84h40v84h84zm-212.695312-204.5625c1.757812 7.910156 2.695312 16.128906 2.695312 24.5625 0 34.550781-15.59375 65.527344-40.105469 86.269531.699219.277344 1.40625.546875 2.105469.832031v44.199219c-21.414062-11.667969-45.945312-18.300781-72-18.300781v-.039062c-.332031.007812-.667969.007812-1 .015624v.023438c-83.261719 0-151 67.738281-151 151h-40c0-79.371094 48.671875-147.582031 117.730469-176.378906-25.449219-20.734375-41.730469-52.3125-41.730469-87.621094 0-62.308594 50.691406-113 113-113 7.40625 0 14.644531.722656 21.65625 2.089844-1.734375-7.84375-2.65625-15.988282-2.65625-24.34375 0-62.167969 50.578125-112.746094 112.746094-112.746094 62.167968 0 112.746094 50.578125 112.746094 112.746094 0 34.894531-15.9375 66.136718-40.910157 86.832031 33.011719 13.109375 61.464844 35.117187 82.304688 63.421875h-53.847657c-24.847656-22.023438-56.976562-36-92.273437-37.796875-2.652344.1875-5.324219.289063-8.019531.289063-7.332032 0-14.5-.710938-21.441406-2.054688zm-51.304688-110.691406c0 40.113281 32.632812 72.746094 72.746094 72.746094 40.109375 0 72.746094-32.632813 72.746094-72.746094 0-40.113282-32.636719-72.746094-72.746094-72.746094-40.113282 0-72.746094 32.632812-72.746094 72.746094zm14 135.253906c0-40.253906-32.746094-73-73-73s-73 32.746094-73 73 32.746094 73 73 73 73-32.746094 73-73zm0 0" />
                                            </svg>
                                        </i>
                                        <span>{{ languageData.label.recommend_to_co_worker }}</span>
                                    </b-button>
                                    <b-button class="btn-bordersecondary" v-if="missionDetail.user_application_status == 'AUTOMATICALLY_APPROVED' ||
									missionDetail.user_application_status == 'PENDING'" :disabled="true">
                                        <span>
                                            {{ languageData.label.applied }}
                                        </span>

                                    </b-button>

                                    <div v-else>
                                        <b-button class="btn-bordersecondary icon-btn" v-if="!hideApply"
                                                  :disabled="disableApply" @click="applyForMission(missionDetail.mission_id)">
                                            <span>
                                                {{ applyButton }}
                                            </span>
                                            <i v-if="!disableApply">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 16" width="19"
                                                     height="15">
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
                            </div>
                        </b-col>
                    </b-row>
                </div>
                <div class="platform-details-wrap">
                    <b-row>
                        <b-col xl="8" lg="7" class="platform-details-left">
                            <div class="platform-details-tab tabs">
                                <ul class="nav-tabs nav">
                                    <li><a href="javascript:void(0)" data-id="mission" class="tablinks active">
                                        {{ languageData.label.mission }}</a></li>
                                    <li v-show="isOrganizationDisplay"><a href="javascript:void(0)" data-id="organization" class="tablinks">
                                        {{ languageData.label.organisation }}</a></li>
                                    <li @click="missionComments('0')"><a href="javascript:void(0)" data-id="comments"
                                                                         class="tablinks" v-if="isCommentDisplay">{{ languageData.label.comments }}
                                    </a></li>
                                </ul>
                                <div class="tab-content-wrap">
                                    <div class="tabs">
                                        <div class="tab-title">
                                            <h3 v-b-toggle.mission>{{ languageData.label.mission }}</h3>
                                        </div>
                                        <b-collapse id="mission" visible accordion="my-accordion" role="tabpanel"
                                                    class="tab-content">

                                            <div class="mission-tab-block row"
                                                 v-if="!checkMissionTypeTime(missionDetail.mission_type)">
                                                <div class="col-sm-4 mission-tab-col" v-if="isMissionGoalDisplay">
                                                    <div class="mission-tab-inner">
                                                        <p v-if="missionDetail.goal_objective">
                                                            {{missionDetail.goal_objective}}
                                                            <span v-if="missionDetail.label_goal_objective != ''">
                                                                {{missionDetail.label_goal_objective}}
                                                            </span>
                                                            <span v-else>{{ languageData.label.goal_objective }}
                                                            </span>
                                                        </p>
                                                        <p v-else>
                                                            0 <span v-if="missionDetail.label_goal_objective != ''">
                                                                {{missionDetail.label_goal_objective}}
                                                            </span>
                                                            <span v-else>{{ languageData.label.goal_objective }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 mission-tab-col" v-if="isCurrentStatusDisplay">
                                                    <div class="mission-tab-inner">
                                                        <p v-if="missionDetail.achieved_goal">
                                                            {{missionDetail.achieved_goal}}
                                                            <span
                                                                v-if="missionDetail.label_goal_achieved != ''">
                                                                {{ missionDetail.label_goal_achieved }}
                                                            </span>
                                                            <span v-else>{{ languageData.label.achieved }}</span>
                                                        </p>
                                                        <p v-else>
                                                            0
                                                            <span
                                                                v-if="missionDetail.label_goal_achieved != ''">
                                                                {{ missionDetail.label_goal_achieved }}
                                                            </span>
                                                            <span v-else>{{ languageData.label.achieved }}</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4 mission-tab-col" v-if="isRemainingGoalDisplay">
                                                    <div class="mission-tab-inner">
                                                        <p>{{pendingGoal(missionDetail)}}<span>
                                                                {{languageData.label.remaining}}
                                                            </span></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="missionDetail.description && missionDetail.description.length > 0">
                                                <div v-for="(section, index) in missionDetail.description" :key=index>
                                                    <h2>{{section.title}}</h2>
                                                    <p class="mission-description-content text-break" v-html="section.description"></p>
                                                </div>
                                            </div>
                                            <div v-if="missionDetail.mission_document && missionDetail.mission_document.length > 0">
                                                <h2>{{ languageData.label.documents }}</h2>
                                                <div class="document-list-wrap">
                                                    <div class="document-list-block"
                                                         v-for="(document ,index) in missionDetail.mission_document"
                                                         :key=index>
                                                        <!-- pdf -->
                                                        <template v-if="document.document_type =='pdf'">
                                                            <b-link :href="document.document_path" target="_blank"
                                                                    :title="document.document_name">
                                                                <AppCustomChip :textVal="document.document_name"
                                                                               class="has-img no-close" :url="bgImage[0]" />
                                                            </b-link>
                                                        </template>
                                                        <!-- doc -->
                                                        <template
                                                                v-if="document.document_type =='doc' || document.document_type =='docx' ">
                                                            <b-link :href="document.document_path" target="_blank"
                                                                    :title="document.document_name">
                                                                <AppCustomChip :textVal="document.document_name"
                                                                               class="has-img no-close" :url="bgImage[1]" />
                                                            </b-link>
                                                        </template>
                                                        <!-- xls  xlsx-->
                                                        <template
                                                            v-if="document.document_type === 'xls' || document.document_type === 'xlsx'">
                                                            <b-link :href="document.document_path" target="_blank"
                                                                    :title="document.document_name">
                                                                <AppCustomChip :textVal="document.document_name"
                                                                               class="has-img no-close" :url="bgImage[2]" />
                                                            </b-link>
                                                        </template>
                                                        <!-- txt -->
                                                        <template v-if="document.document_type === 'txt'">
                                                            <b-link :href="document.document_path" target="_blank"
                                                                    :title="document.document_name">
                                                                <AppCustomChip :textVal="document.document_name"
                                                                               class="has-img no-close" :url="bgImage[3]" />
                                                            </b-link>
                                                        </template>
                                                    </div>

                                                </div>
                                            </div>
                                        </b-collapse>
                                    </div>
                                    <div class="tabs" v-show="isOrganizationDisplay">
                                        <div class="tab-title">
                                            <h3 v-b-toggle.organization>{{ languageData.label.organisation }}</h3>
                                        </div>
                                        <b-collapse id="organization" accordion="my-accordion" role="tabpanel"
                                                    class="tab-content">
                                            <div class="organization-detail text-break" v-html="missionDetail.organisation_detail"></div>
                                        </b-collapse>
                                    </div>
                                    <div class="tabs" v-if="isCommentDisplay">
                                        <div class="tab-title" @click="missionComments('0')">
                                            <h3 v-b-toggle.comments>{{ languageData.label.comment }}</h3>
                                        </div>
                                        <b-collapse id="comments" accordion="my-accordion" role="tabpanel"
                                                    class="tab-content comment-block">
                                            <b-form class="comment-form">
                                                <b-form-textarea id="" :placeholder="languageData.placeholder.comment"
                                                                 maxLength="600" v-model="comment"
                                                                 :class="{ 'is-invalid': $v.comment.$error }" rows="4" size="lg"
                                                                 no-resize>
                                                </b-form-textarea>

                                                <div v-if="submitted && !$v.comment.required" class="invalid-feedback">
                                                    {{ languageData.errors.comment_required }}
                                                </div>
                                                <div v-if="submitted && !$v.comment.maxLength" class="invalid-feedback">
                                                    {{ languageData.errors.comment_max_length }}
                                                </div>
                                                <div class="btn-with-loader">
                                                    <b-button class="btn-bordersecondary" @click="handleSubmit"
                                                              v-bind:disabled="postComment">
                                                        {{ languageData.label.post_comment }}
                                                    </b-button>
                                                    <div class="spinner btn-loader" v-if="postComment">
                                                        <div class="bounce1"></div>
                                                        <div class="bounce2"></div>
                                                        <div class="bounce3"></div>
                                                    </div>
                                                </div>
                                            </b-form>

                                            <div class="comment-list"
                                                 v-if="missionComment && missionComment.length > 0">
                                                <div class="comment-list-inner" data-simplebar>
                                                    <div class="more-inner-list">
                                                        <div class="comment-list-item"
                                                             v-for="(comments, index) in missionComment" :key=index>
                                                            <b-media class="comment-media">
                                                                <i slot="aside" class="user-profile-icon"
                                                                   :style="{backgroundImage: 'url(' + comments.user.avatar + ')'}">
                                                                </i>
                                                                <div class="comment-title">
                                                                    <h5 v-if="comments.user.user_id != null">
                                                                      {{comments.user.first_name}} {{comments.user.last_name}}</h5>
                                                                    <h5 v-else>{{ languageData.label.deleted_user }}</h5>
                                                                    <p>{{ getCommentDate(comments.created_at) }}</p>
                                                                </div>
                                                                <div class="comment-content">
                                                                    <p>
                                                                        {{comments.comment}}
                                                                    </p>
                                                                </div>
                                                            </b-media>
                                                        </div>
                                                    </div>

                                                    <div class="more-comment-list" v-if="nextUrl != null">
                                                        <b-button v-if="loadMoreComment" class="comment-btn">
                                                            <span>{{ languageData.label.loading }}</span>
                                                        </b-button>
                                                        <b-button v-else @click="showMoreComment" class="comment-btn">
                                                            <span>{{ languageData.label.read_more_comment }}</span>
                                                        </b-button>
                                                    </div>

                                                </div>

                                            </div>

                                        </b-collapse>
                                    </div>
                                </div>
                            </div>
                        </b-col>
                        <b-col xl="4" lg="5" class="platform-details-right">
                            <div class="info-block">
                                <h2 class="title-with-border"><span>{{ languageData.label.information }}</span></h2>

                                <div class="table-wrap">
                                    <div class="table-row" v-if="isSkillDispaly">
                                        <span class="label-col">{{languageData.label.skills}}</span>
                                        <span class="detail-col">{{getSkills(missionDetail)}}</span>
                                    </div>
                                    <div class="table-row">
                                        <span class="label-col">{{languageData.label.days}}</span>
                                        <span class="detail-col"
                                              v-if="missionDetail.availability_type != ''">{{missionDetail.availability_type}}</span>
                                        <span class="detail-col" v-else>-</span>
                                    </div>
                                    <div class="table-row" v-if="isStarDisplay">
                                        <span class="label-col">{{languageData.label.rating}}</span>
                                        <span class="detail-col">
                                            <star-rating :rating="missionDetail.mission_rating_count" :read-only="true"
                                                         :increment="0.01" v-bind:max-rating="5" inactive-color="#dddddd"
                                                         active-color="#F7D341" v-bind:star-size="23">
                                            </star-rating>
                                            <em>(
                                                {{ languageData.label.by}}
                                                {{missionDetail.mission_rating_total_volunteers}}
                                            </em>
                                            <em v-if="missionDetail.mission_rating_total_volunteers <=1"
                                                class="volunteery-counter"> {{ languageData.label.volunteer}} )</em>
                                            <em v-else class="volunteery-counter"> {{ languageData.label.volunteers}}
                                                )</em>
                                        </span>
                                    </div>
                                    <div v-if="customInformation.length > 0">
                                        <div class="table-row" v-for="(data,index) in customInformation" :key=index>
                                            <span class="label-col">{{data.title}}</span>
                                            <span class="detail-col">{{data.description}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <RecentVolunteers v-if="isShownComponent && isRecentVolunteerDispaly"></RecentVolunteers>
                        </b-col>
                    </b-row>
                </div>
            </b-container>
            <div class="mission-block" v-if="missionListing && missionListing.length > 0 && relatedMissionsDisplay">
                <b-container class="card-grid">
                    <h2>{{languageData.label.related_missions}}</h2>
                    <div>
                        <div v-bind:class="{ 'content-loader-wrap': true, 'mission-loader': relatedMissionlLoader}">
                            <div class="content-loader"></div>
                        </div>
                        <GridView
                          id="gridView"
                          :items="missionListing"
                          v-if="isShownComponent"
                          :relatedMission=relatedMission
                          @getMissions="getRelatedMissions"
                          small
                        />
                    </div>
                </b-container>
            </div>
          <invite-co-worker ref="userDetailModal" entity-type="MISSION" :entity-id="currentMissionId"></invite-co-worker>
        </main>
        <footer v-if="isShownComponent">
            <TheSecondaryFooter v-if="isShownComponent"></TheSecondaryFooter>
        </footer>
    </div>
</template>

<script>
  import AppCustomChip from "../components/AppCustomChip";
  import StarRating from 'vue-star-rating';
  import constants from '../constant';
  import {
    favoriteMission,
    applyMission,
    storeMissionRating,
    missionDetail,
    relatedMissions,
    missionComments,
    storeMissionComments
  } from "../services/service";
  import store from "../store";
  import moment from 'moment';
  import {
    required,
    maxLength
  } from 'vuelidate/lib/validators';
  import SocialSharing from 'vue-social-sharing';
  import InviteCoWorker from "@/components/InviteCoWorker";

  export default {
    components: {
      AppCustomChip,
      StarRating,
      ThePrimaryHeader: () => import("../components/Layouts/ThePrimaryHeader"),
      TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
      GridView: () => import("../components/MissionGridView"),
      RecentVolunteers: () => import("../components/RecentVolunteers"),
      MissionCarousel: () => import("../components/MissionCarousel"),
      SocialSharing,
      InviteCoWorker
    },
    data() {
      return {
        relatedMission: true,
        sharingUrl: "",
        isShownComponent: false,
        missionId: this.$route.params.misisonId,
        timeSheetId: '',
        missionAddedToFavoriteByUser: false,
        search: "",
        currentMissionId: 0,
        message: null,
        recentVolunterLoader: true,
        missionDetail: [],
        disableApply: false,
        hideApply: false,
        missionDocument: [],
        relatedMissionlLoader: true,
        isShownMediaComponent: false,
        bgImage: [
          require("@/assets/images/pdf.svg"),
          require("@/assets/images/doc.svg"),
          require("@/assets/images/xlsx.svg"),
          require("@/assets/images/txt.svg"),
        ],
        orgLogo: require("@/assets/images/ces-logo.png"),
        currentPage: 1,
        max: 100,
        value: 70,
        missionListing: [],
        missionComment: [],
        defaultMedia: '',
        isShareComponentShown: false,
        languageData: [],
        applyButton: '',
        submitted: false,
        comment: '',
        nextUrl: null,
        page: 1,
        postComment: false,
        loadMoreComment: false,
        domainName: '',
        socialSharingUrl: '',
        isFacebookSharingDisplay: false,
        isTwitterSharingDisplay: false,
        isStarDisplay: false,
        isThemeDisplay: false,
        isInviteCollegueDisplay: false,
        isOrganizationDisplay: false,
        isCommentDisplay: false,
        isRecentVolunteerDispaly: false,
        isMissionGoalDisplay: false,
        isCurrentStatusDisplay: false,
        isRemainingGoalDisplay: false,
        isSkillDispaly: false,
        isQuickAccessFilterDisplay: false,
        relatedMissionsDisplay: false,
        allowAddEntry: false,
        currentTimeData: {
          missionId: '',
          hours: '',
          minutes: '',
          dateVolunteered: '',
          workDay: '',
          notes: '',
          day: '',
          timeSheetId: '',
          documents: [],
          disabledPastDates: '',
          disabledFutureDates: '',
          missionName: '',
          action: ''
        },
        customInformation: [],
        missionRatingSetting:true,
        isStarRatingDisable: false
      };
    },
    mounted() {
      setTimeout(() => {
         let tabItem = document.querySelectorAll(".platform-details-tab .nav-tabs li a")
          tabItem.forEach(function (tabItemEvent) {
            tabItemEvent.addEventListener("click", tabsHandle);
          });

      function tabsHandle(tabsEvent) {

        let i, tabContent, tabLinks;
        tabContent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabContent.length; i++) {
          tabContent[i].style.display = "none";
          if (tabsEvent.currentTarget.getAttribute("data-id") === tabContent[i].getAttribute('id')) {
            tabContent[i].style.display = "block";
          }
        }
        tabLinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tabLinks.length; i++) {
          tabLinks[i].className = tabLinks[i].className.replace(" active", "");
        }
        tabsEvent.currentTarget.className += " active";
      }
      }, 1000);


      if (!window.location.origin) {
        window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location
          .port ? ':' + window.location.port : '');
      }

      let currentUrl = (((window.location.origin).split('.')));

      if (currentUrl[0]) {
        if (process.env.NODE_ENV == 'production') {
          this.domainName = process.env.VUE_APP_DEFAULT_TENANT
        } else {
          this.domainName = ((currentUrl[0]).split('//'))[1];
        }
      }

      this.socialSharingUrl = process.env.VUE_APP_API_ENDPOINT + "social-sharing/" + this.domainName + "/" + this
        .missionId + "/" + store.state.defaultLanguageId;
    },
    validations: {
      comment: {
        required,
        maxLength: maxLength(600)
      },
    },
    methods: {
      addEntry() {
        let missionData = {
          "missionId": '',
          "missionType": ''
        }
        missionData.missionId = this.$route.params.misisonId
        missionData.missionType = this.missionDetail.mission_type
        store.commit('timeSheetEntryDetail', missionData);
        this.$router.push('/volunteering-timesheet');
      },

      // Get comment create date format
      getCommentDate(commentDate) {
        if (commentDate != null) {
          let day = moment(commentDate, "YYYY-MM-DD HH:mm:ss").format('dddd');
          let date = moment(String(commentDate)).format('MMMM DD, YYYY, h:mm A')
          return day + ', ' + date;
        } else {
          return '';
        }
      },

      // Check mission type
      checkMissionTypeTime(missionType) {
        return missionType == constants.MISSION_TYPE_TIME
      },

      setRating: function (rating) {
        let missionData = {
          mission_id: '',
          rating: ''
        };
        missionData.mission_id = this.missionId;
        missionData.rating = rating;
        storeMissionRating(missionData).then(response => {
          if (response.error == true) {
            this.makeToast("danger", response.message);
          } else {
            this.makeToast("success", response.message);
          }
        });
      },

      // Add mission to favorite
      favoriteMission(missionId) {
        let missionData = {
          mission_id: ''
        };
        missionData.mission_id = missionId;
        favoriteMission(missionData).then(response => {
          if (response.error == true) {
            this.makeToast("danger", response.message);
          } else {
            this.makeToast("success", response.message);
            this.missionAddedToFavoriteByUser = !this.missionAddedToFavoriteByUser;
          }
        });

      },

      /*
			 * Sets display value of suggestion in Invite Co-worker modal
			 */
      getSuggestionValue(suggestion) {
        let firstName = suggestion.item.first_name;
        let lastName = suggestion.item.last_name;
        return firstName + " " + lastName;
      },
      /*
       * Opens Recommend to a co-worker modal
       */
      handleModal(missionId) {
        this.currentMissionId = missionId;
        this.$refs.userDetailModal.show();
      },

      defaultMediaPathDetail(defaultImage) {
        this.defaultMedia = defaultImage;
        this.isShareComponentShown = true;
      },

      // Apply for mission
      applyForMission(missionId) {
        let missionData = {};
        missionData.mission_id = missionId;
        missionData.availability_id = this.missionDetail.availability_id;

        applyMission(missionData).then(response => {
          if (response.error == true) {
            this.makeToast("danger", response.message);
          } else {
            this.disableApply = true;
            this.applyButton = this.languageData.label.applied
            this.makeToast("success", response.message);
            this.$emit("getMissions");
          }
        })
      },
      getRelatedMissions() {
        if (this.$route.params.misisonId) {
          this.relatedMissionlLoader = true;
          relatedMissions(this.$route.params.misisonId).then(response => {
            if (response.error == false) {
              this.missionListing = response.data;
            }
            this.relatedMissionlLoader = false;
            this.isShownComponent = true;
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

      pendingGoal(missionDetail) {
        if (missionDetail.goal_objective) {
          if((missionDetail.goal_objective - missionDetail.achieved_goal) < 0) {
            return 0;
          } else {
            return missionDetail.goal_objective - missionDetail.achieved_goal;
          }
        } else {
          return 0;
        }
      },

      getMissionDetail() {
        if (this.$route.params.misisonId) {
          const requestParams = {
            'mission_id': this.$route.params.misisonId,
            'donation_mission': false
          }
          missionDetail(requestParams).then(response => {
            this.isShownMediaComponent = true;
            if (response.error == false) {
              if (response.data[0]) {
                this.missionDetail = response.data[0];
                if (response.data[0].user_application_status ==
                  constants.AUTOMATICALLY_APPROVED && response.data[0]
                    .user_application_count > 0
                ) {
                  this.allowAddEntry = true
                }
                if (response.data[0].is_favourite == 1) {
                  this.missionAddedToFavoriteByUser = true;
                }
                if(this.missionRatingSetting) {
                  if (response.data[0].user_application_status != constants.AUTOMATICALLY_APPROVED) {
                    this.isStarRatingDisable = true;
                  }
                }

                let currentDate = moment().format("YYYY-MM-DD HH::mm:ss");

                if (response.data[0].end_date != '' && response.data[0].end_date != null) {
                  let missionEndDate = moment(response.data[0].end_date).format(
                    "YYYY-MM-DD HH::mm:ss");
                  if (currentDate > missionEndDate && response.data[0].set_view_detail == 1) {
                    this.hideApply = true
                  }
                }
                if (response.data[0].application_deadline != '' && response.data[0]
                  .application_deadline != null) {
                  let missionDeadline = moment(response.data[0].application_deadline).format(
                    "YYYY-MM-DD HH::mm:ss");
                  if (currentDate > missionDeadline && response.data[0].set_view_detail == 1) {
                    this.hideApply = true
                  }
                }

                if (response.data[0].user_application_status ==
                  constants.AUTOMATICALLY_APPROVED || response.data[0]
                    .user_application_status ==
                  constants.PENDING) {
                  this.disableApply = true;

                } else {
                  if (response.data[0].set_view_detail == 1) {
                    this.disableApply = true
                  } else {
                    this.disableApply = false
                  }
                }

                this.missionDocument = response.data[0].mission_document
                if (response.data[0].custom_information != null) {
                  this.customInformation = response.data[0].custom_information;
                }
              } else {
                this.$router.push('/404');
              }

            } else {
              this.$router.push('/404');
            }

            this.getRelatedMissions();

            /*
             * If this.missionDetail.organisation_detail is a string it means that the details are not empty
             * so we can display the organization tab.
             * Otherwise this.missionDetail.organisation_detail is an array if the details are empty.
             */
            this.isOrganizationDisplay = typeof this.missionDetail.organisation_detail === 'string';
            this.formatDescription();
          })
        } else {
          this.$router.push('/404');
        }
      },
      //get theme title
      getThemeTitle(missionTheme) {
        if (missionTheme) {
          let translations = missionTheme.translations
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
        }
      },
      getSkills(missionDetail) {
        let skills = '';
        if (missionDetail.skill) {
          (missionDetail.skill).filter((item) => {
            if (skills == '') {
              skills = item.title;
            } else {
              skills = skills + ", " + item.title;
            }
          });
        } else {
          skills = '-';
        }
        return skills;
      },

      missionComments(commentStatus) {
        this.loadMoreComment = true;
        let commentData = {};
        if (commentStatus == '0') {
          this.missionComment = []
          this.page = 1
        }
        commentData.missionId = this.$route.params.misisonId;
        commentData.page = this.page;
        missionComments(commentData).then(response => {
          if (response.error == false) {
            if (this.missionComment.length) {
              response.data.map((value) => {
                this.missionComment.push(value);
              });
            } else {
              this.missionComment = response.data;
            }
            if (response.pagination) {
              this.nextUrl = response.pagination.next_url;
            }
          }
          setTimeout(() => {
            this.loadMoreComment = false;
          }, 100)
        });
      },

      handleSubmit() {
        this.submitted = true;
        this.$v.$touch();
        // stop here if form is invalid
        if (this.$v.$invalid) {
          return;
        }
        this.postComment = true;
        let commentData = {
          mission_id: '',
          comment: ''
        }
        commentData.mission_id = this.$route.params.misisonId;
        commentData.comment = this.comment;
        // Call to store mission service with params mission_id and comments
        storeMissionComments(commentData).then(response => {
          if (response.error == true) {
            this.makeToast("danger", response.message);
          } else {
            this.comment = '';
            this.disableApply = true;
            this.applyButton = this.languageData.label.applied
            this.makeToast("success", response.message);
            this.missionComment = []
            this.nextUrl = null,
              this.postComment = false,
              this.loadMoreComment = false,
              this.page = 1;
            this.missionComments('0');
            this.$v.$reset();
          }
          this.postComment = false;
        });
      },

      showMoreComment() {
        this.page++;
        let simplebarContent = document.querySelector(".comment-list-inner .simplebar-content");
        let simplebarHeight = simplebarContent.offsetHeight
        setTimeout(() => {
          let simplebarWrapper = document.querySelector(".comment-list .simplebar-content-wrapper");
          simplebarWrapper.scrollTop = simplebarHeight;
        }, 100);
        this.missionComments('1');
      },

      tabingHandle(tabsEvent) {
        let i, tabContent, tabLinks;
        tabContent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabContent.length; i++) {
          tabContent[i].style.display = "none";
          if (tabsEvent.currentTarget.getAttribute("data-id") === tabContent[i].getAttribute('id')) {
            tabContent[i].style.display = "block";
          }
        }
        tabLinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tabLinks.length; i++) {
          tabLinks[i].className = tabLinks[i].className.replace("active", "");
        }
        tabsEvent.currentTarget.className += " active";
      },

      formatDescription() {
        if (this.missionDetail.description && this.missionDetail.description.length > 0) {
            this.missionDetail.description.map(detail => {
                detail.description = detail.description.replaceAll('&nbsp;', ' ');
            });
        }

        if (this.isOrganizationDisplay) {
            this.missionDetail.organisation_detail = this.missionDetail.organisation_detail.replaceAll('&nbsp;', ' ');
        }
      }
    },
    created() {
      this.sharingUrl = document.URL
      // Get mission detail
      this.getMissionDetail();
      if (store.state.search != null) {
        this.search = store.state.search;
      } else {
        this.search = '';
      }
      this.languageData = JSON.parse(store.state.languageLabel);
      this.applyButton = this.languageData.label.apply_now

      this.isFacebookSharingDisplay = this.settingEnabled(constants.SHARE_MISSION_FACEBOOK)
      store.state.isFacebookDisplay = this.isFacebookSharingDisplay
      this.isTwitterSharingDisplay = this.settingEnabled(constants.SHARE_MISSION_TWITTER)
      store.state.isTwitterDisplay = this.isTwitterSharingDisplay
      this.isStarDisplay = this.settingEnabled(constants.MISSION_RATINGS)
      this.isThemeDisplay = this.settingEnabled(constants.THEMES_ENABLED)
      this.isInviteCollegueDisplay = this.settingEnabled(constants.INVITE_COLLEAGUE)
      this.isCommentDisplay = this.settingEnabled(constants.MISSION_COMMENTS)
      this.isRecentVolunteerDispaly = this.settingEnabled(constants.RECENT_VOLUNTEERES)
      this.isMissionGoalDisplay = this.settingEnabled(constants.SHOW_GOAL_OF_MISSION)
      this.isCurrentStatusDisplay = this.settingEnabled(constants.SHOW_CURRENT_STATUS_OF_MISSION)
      this.isRemainingGoalDisplay = this.settingEnabled(constants.SHOW_REMAINING_DATA_TO_ACHIEVE_GOAL)
      this.isSkillDispaly = this.settingEnabled(constants.SKILLS_ENABLED)
      this.isQuickAccessFilterDisplay = this.settingEnabled(constants.QUICK_ACCESS_FILTERS)
      this.missionRatingSetting = this.settingEnabled(constants.MISSION_RATING_VOLUNTEER)
      this.relatedMissionsDisplay = this.settingEnabled(constants.RELATED_MISSIONS)
    },
    updated() {

    },
    watch: {
      $route(to, from) {
        this.sharingUrl = document.URL
        this.isShownComponent = false
        this.missionId = this.$route.params.misisonId
        this.missionAddedToFavoriteByUser = false
        this.rating = 3.5
        this.search = ""
        this.currentMissionId = 0
        this.message = null
        this.recentVolunterLoader = true
        this.missionDetail = []
        this.disableApply = false
        this.missionDocument = []
        this.relatedMissionlLoader = true
        this.isShownMediaComponent = false
        this.max = 100,
          this.value = 70,
          this.missionListing = [],
          this.missionComment = [],
          this.submitted = false,
          this.nextUrl = null,
          this.postComment = false,
          this.loadMoreComment = false,
          this.languageData = JSON.parse(store.state.languageLabel);
        this.applyButton = this.languageData.label.apply_now;
        this.page = 1;
        this.isFacebookSharingDisplay = false
        this.isTwitterSharingDisplay = false
        this.isStarDisplay = false
        this.isThemeDisplay = false
        this.isInviteCollegueDisplay = false
        this.isCommentDisplay = false
        this.isRecentVolunteerDispaly = false
        this.isSkillDispaly = false
        this.isMissionGoalDisplay = false
        this.isCurrentStatusDisplay = false
        this.isRemainingGoalDisplay = false
        this.isQuickAccessFilterDisplay = false
        this.relatedMissionsDisplay = false
        this.timeSheetId = false
        this.hideApply = false,
          this.customInformation = []
        this.getMissionDetail();
        this.languageData = JSON.parse(store.state.languageLabel);
        this.applyButton = this.languageData.label.apply_now
        this.isFacebookSharingDisplay = this.settingEnabled(constants.SHARE_MISSION_FACEBOOK)
        store.state.isFacebookDisplay = this.isFacebookSharingDisplay
        this.isTwitterSharingDisplay = this.settingEnabled(constants.SHARE_MISSION_TWITTER)
        store.state.isTwitterDisplay = this.isTwitterSharingDisplay
        this.isStarDisplay = this.settingEnabled(constants.MISSION_RATINGS)
        this.isThemeDisplay = this.settingEnabled(constants.THEMES_ENABLED)
        this.isInviteCollegueDisplay = this.settingEnabled(constants.INVITE_COLLEAGUE)
        this.isCommentDisplay = this.settingEnabled(constants.MISSION_COMMENTS)
        this.isRecentVolunteerDispaly = this.settingEnabled(constants.RECENT_VOLUNTEERES)
        this.isMissionGoalDisplay = this.settingEnabled(constants.SHOW_GOAL_OF_MISSION)
        this.isCurrentStatusDisplay = this.settingEnabled(constants.SHOW_CURRENT_STATUS_OF_MISSION)
        this.isRemainingGoalDisplay = this.settingEnabled(constants.SHOW_REMAINING_DATA_TO_ACHIEVE_GOAL)
        this.isSkillDispaly = this.settingEnabled(constants.SKILLS_ENABLED)
        this.isQuickAccessFilterDisplay = this.settingEnabled(constants.QUICK_ACCESS_FILTERS)
        this.relatedMissionsDisplay = this.settingEnabled(constants.RELATED_MISSIONS)
        this.socialSharingUrl = process.env.VUE_APP_API_ENDPOINT + "social-sharing/" + this.domainName + "/" +
          this.missionId + "/" + store.state.defaultLanguageId;
        this.missionRatingSetting = true
        this.isStarRatingDisable = false
        let tabItem = document.querySelectorAll(".platform-details-tab .nav-tabs li a")
        tabItem.forEach(function (tabItemEvent) {
          tabItemEvent.classList.remove('active')
        });
        tabItem[0].classList.add('active')
        let i, tabContent, tabLinks;
        tabContent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabContent.length; i++) {
          tabContent[i].style.display = "none";
          if (tabItem[0].getAttribute("data-id") === tabContent[i].getAttribute('id')) {
            tabContent[i].style.display = "block";
          }
        }
        tabLinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tabLinks.length; i++) {
          tabLinks[i].className = tabLinks[i].className.replace(" active", "");
        }
        tabItem[0].className += " active";

      }
    }
  };

</script>
