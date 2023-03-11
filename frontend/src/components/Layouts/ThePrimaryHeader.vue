    <template>
        <div class="top-header">
            <b-navbar toggleable="lg">
                <b-container>
                    <div class="navbar-toggler" @click.stop v-if="this.$store.state.isLoggedIn">
                        <b-link title="Menu" @click="openMenu" class="toggler-icon">
                            <img :src="$store.state.imagePath+'/assets/images/menu-ic.svg'" alt />
                        </b-link>
                    </div>
                    <b-navbar-brand
                        :href="hostUrl+'home'"
                        :style="{backgroundImage: 'url('+this.$store.state.logo+')'}"
                        v-if="this.$store.state.isLoggedIn && this.$store.state.logoRedirectUrl === 'home'">
                    </b-navbar-brand>
                    <b-navbar-brand
                        target="_blank"
                        :href="this.$store.state.logoRedirectUrl"
                        :style="{backgroundImage: 'url('+this.$store.state.logo+')'}"
                        v-if="this.$store.state.isLoggedIn && this.$store.state.logoRedirectUrl !== 'home'">
                    </b-navbar-brand>
                    <b-navbar-brand :to="{ name: 'login' }"
                        :style="{backgroundImage: 'url('+this.$store.state.logo+')'}"
                        v-if="!this.$store.state.isLoggedIn"
                    >
                    </b-navbar-brand>

                    <div class="menu-wrap" @click.stop>
                        <b-button class="btn-cross" @click="closeMenu">
                            <img :src="$store.state.imagePath+'/assets/images/cross-ic.svg'" alt>
                        </b-button>
                        <ul v-if="this.$store.state.isLoggedIn">
                            <li v-if="this.$store.state.logoRedirectUrl !== 'home'" class="has-menu no-dropdown home-link">
                                <router-link :to="{ path: '/home'}" class="home-icon">
                                    <img class="home-icon"
                                        :src="$store.state.imagePath+'/assets/images/home-ic.svg'"
                                    />
                                </router-link>
                            </li>

                            <li class="has-menu">
                                <a href="Javascript:void(0)">{{ languageData.label.explore}}</a>
                                <i class="collapse-toggle"></i>
                                <ul class="dropdown-menu sub-dropdown">
                                    <li v-if="isThemeDisplay" v-bind:class="topThemeClass">
                                        <a href="Javascript:void(0)">{{ languageData.label.top_themes}}</a>
                                        <i class="collapse-toggle"></i>
                                        <ul class="subdropdown-menu" v-if="topTheme != null && topTheme.length > 0">
                                            <li v-for="(items, key) in topTheme" v-bind:key=key class="no-dropdown">
                                                <router-link :to="{ path: '/home/themes/'+items.id}"
                                                    @click.native="menuBarclickHandler">
                                                    {{ items.title}}
                                                </router-link>
                                            </li>
                                        </ul>
                                    </li>
                                    <li v-bind:class="topCountryClass">
                                        <a href="Javascript:void(0)">{{languageData.label.top_country}}</a>
                                        <i class="collapse-toggle"></i>
                                        <ul class="subdropdown-menu" v-if="topCountry != null && topCountry.length > 0">
                                            <li v-for="(items, key) in topCountry" v-bind:key=key class="no-dropdown">
                                                <router-link
                                                    :to="{ path: '/home/country/'+items.id}"
                                                    @click.native="menuBarclickHandler">
                                                    {{ items.title}}
                                                </router-link>
                                            </li>
                                        </ul>
                                    </li>
                                    <li v-bind:class="topOrganizationClass">
                                        <a href="Javascript:void(0)">{{ languageData.label.top_organisation}}</a>
                                        <i class="collapse-toggle"></i>
                                        <ul class="subdropdown-menu"
                                            v-if="topOrganization != null && topOrganization.length > 0">
                                            <li v-for="(items, key) in topOrganization" v-bind:key=key
                                                class="no-dropdown">
                                                <router-link :to="{ path: '/home/organization/'+items.id}"
                                                    @click.native="menuBarclickHandler">
                                                    {{ items.title}}
                                                </router-link>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="no-dropdown">
                                        <router-link :to="{ path: '/home/most-ranked-missions'}"
                                            @click.native="menuBarclickHandler">
                                            {{languageData.label.most_ranked}}
                                        </router-link>
                                    </li>
                                    <li class="no-dropdown">
                                        <router-link :to="{ path: '/home/favourite-missions'}"
                                            @click.native="menuBarclickHandler">
                                            {{languageData.label.top_favourite}}
                                        </router-link>
                                    </li>
                                    <li class="no-dropdown">
                                        <router-link :to="{ path: '/home/recommended-missions'}"
                                            @click.native="menuBarclickHandler">
                                            {{languageData.label.recommended}}
                                        </router-link>
                                    </li>
                                    <li class="no-dropdown">
                                        <router-link :to="{ path: '/home/random-missions'}"
                                            @click.native="menuBarclickHandler">
                                            {{languageData.label.random}}
                                        </router-link>
                                    </li>
                                    <li v-if="isVolunteeringSettingEnabled" class="no-dropdown">
                                        <router-link :to="{ path: '/home/virtual-missions'}"
                                            @click.native="menuBarclickHandler">
                                            {{languageData.label.virtual_missions}}
                                        </router-link>
                                    </li>
                                </ul>
                            </li>
                            <li class="has-menu no-dropdown" v-if="isStoryDisplay">
                                <router-link :to="{ path: '/stories'}">
                                    {{languageData.label.stories}}
                                </router-link>
                            </li>
                            <li class="has-menu no-dropdown" v-if="isNewsDisplay">
                                <router-link :to="{ path: '/news'}">
                                    {{languageData.label.news}}
                                </router-link>
                            </li>

                            <li class="has-menu" v-show="isPolicyDisplay && policyPage.length > 0">
                                <a href="Javascript:void(0)">{{ languageData.label.policy}}
                                </a>
                                <i class="collapse-toggle"></i>
                                <ul class="dropdown-menu" v-show="policyPage.length > 0">
                                    <li v-for="(item, key) in policyPage" v-bind:key=key class="no-dropdown">
                                        <router-link :to="{ path: '/policy/'+item.slug}" v-if="item.pages[0]"
                                            @click.native="menuBarclickHandler">
                                            {{item.pages[0].title}}
                                        </router-link>
                                    </li>
                                </ul>
                            </li>
                            <li class="btn-save-outer">
                                <b-button class="btn-bordersecondary btn-save" v-if="isSubmitNewMissionSet"
                                    @click="submitNewMission">{{languageData.label.submit_new_mission}}</b-button>
                            </li>
                        </ul>
                    </div>
                    <div class="header-right ml-auto">
                        <b-nav>
                            <b-nav-item right class="search-menu" @click="searchMenu">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/search-ic.svg'" alt>
                                </i>
                            </b-nav-item>
                            <b-nav-item right class="btn-save-menu" v-if="isSubmitNewMissionSet"
                                @click="submitNewMission">
                                <b-button class="btn-bordersecondary btn-save">
                                    {{languageData.label.submit_new_mission}}
                                </b-button>
                            </b-nav-item>
                            <b-nav-item right class="notification-menu" id="notifyPopoverWrap"
                                v-if="this.$store.state.isLoggedIn">
                                <button id="notificationPopover" class="btn-notification"
                                    @click="getNotificationSettingListing">
                                    <i>
                                        <img :src="$store.state.imagePath+'/assets/images/bell-ic.svg'"
                                            alt="Notification Icon" />
                                    </i>
                                    <b-badge v-show="notificationCount != 0">{{notificationCount}}</b-badge>
                                </button>
                            </b-nav-item>
                            <b-nav-item-dropdown right class="profile-menu" v-if="this.$store.state.isLoggedIn">
                                <template slot="button-content">
                                    <i :style="{backgroundImage: 'url('+this.$store.state.avatar+')'}"></i>

                                </template>
                                 <b-dropdown-item class="profile-menu-user-name"> <em>{{this.$store.state.firstName+' '+this.$store.state.lastName}}</em>
                                </b-dropdown-item>
                                <b-dropdown-item :to="{ name: 'dashboard' }">{{ languageData.label.dashboard}}
                                </b-dropdown-item>
                                <b-dropdown-item :to="{ name: 'myAccount' }">{{ languageData.label.my_account}}
                                </b-dropdown-item>
                                <b-dropdown-item v-on:click.native="logout()" replace
                                    v-if="this.$store.state.isLoggedIn">
                                    {{ languageData.label.logout}}
                                </b-dropdown-item>
                            </b-nav-item-dropdown>
                        </b-nav>
                        <b-popover target="notificationPopover" placement="topleft" container="notifyPopoverWrap"
                            ref="notficationPopover" triggers="click" custom-class="notification-popover">
                            <template slot="title">
                                <div>
                                    <b-button class="btn-setting" :title="languageData.label.notification_settings"
                                        @click="showsetting">
                                        <img :src="$store.state.imagePath+'/assets/images/settings-ic.svg'"
                                            alt="Setting icon">

                                    </b-button>
                                    <span class="title">{{languageData.label.notification}}</span>
                                    <b-button class="btn-clear" @click="showclearitem" v-if="totalNotificationCount != 0">
                                        {{languageData.label.clear_all}}
                                    </b-button>
                                </div>
                            </template>
                            <div class="notification-details" data-simplebar>
                                <b-list-group>
                                    <b-list-group-item v-if="notificationListing.today.length > 0" v-bind:class="{
            'read-item':item.is_read == 1 ,
            'unread-item' : item.is_read == 0
        }" v-for="(item,index) in notificationListing.today" :key=index>
                                        <div v-on:click="readItem($event,item.is_read, item.notification_id,item.link)">
                                            <i
                                                v-bind:class="{'message-profile-icon' : item.is_avatar && item.is_avatar ==1}">
                                                <img :src="item.icon" alt />
                                            </i>
                                            <p>
                                                {{item.notification_string}}
                                            </p>

                                        </div>
                                        <span v-b-tooltip.hover :title="getTooltipTitle(item.is_read)" class="status"
                                            v-on:click="readUnreadItem($event, item.is_read, item.notification_id)">
                                        </span>

                                    </b-list-group-item>
                                </b-list-group>
                                <div class="slot-title" v-show="notificationListing.yesterday.length">
                                    <span>{{languageData.label.yesterday}}</span>
                                </div>
                                <b-list-group v-show="notificationListing.yesterday.length > 0">
                                    <b-list-group-item v-bind:class="{
    'read-item':item.is_read == 1 ,
    'unread-item' : item.is_read == 0
}" v-for="(item,index) in notificationListing.yesterday" :key=index>
                                        <div v-on:click="readItem($event,item.is_read, item.notification_id,item.link)">
                                            <i
                                                v-bind:class="{'message-profile-icon' : item.is_avatar && item.is_avatar ==1}">
                                                <img :src="item.icon" alt />
                                            </i>
                                            <p>
                                                {{item.notification_string}}
                                            </p>
                                        </div>
                                        <span class="status" v-b-tooltip.hover :title="getTooltipTitle(item.is_read)"
                                            v-on:click="readUnreadItem($event,item.is_read, item.notification_id)"></span>
                                    </b-list-group-item>
                                </b-list-group>
                                <div class="slot-title" v-show="notificationListing.older.length > 0">
                                    <span>{{languageData.label.older}}</span>
                                </div>
                                <b-list-group v-show="notificationListing.older">
                                    <b-list-group-item v-bind:class="{
    'read-item':item.is_read == 1 ,
    'unread-item' : item.is_read == 0
}" v-for="(item,index) in notificationListing.older" :key=index>
                                        <div v-on:click="readItem($event,item.is_read, item.notification_id,item.link)">
                                            <i
                                                v-bind:class="{'message-profile-icon' : item.is_avatar && item.is_avatar ==1}">
                                                <img :src="item.icon" alt />
                                            </i>
                                            <p>
                                                {{item.notification_string}}
                                            </p>
                                        </div>
                                        <span class="status" v-b-tooltip.hover :title="getTooltipTitle(item.is_read)"
                                            v-on:click="readUnreadItem($event,item.is_read, item.notification_id)"></span>
                                    </b-list-group-item>
                                </b-list-group>
                            </div>
                            <div class="notification-clear">
                                <div class="clear-content">
                                    <i>
                                        <img :src="$store.state.imagePath+'/assets/images/gray-bell-ic.svg'" alt />
                                    </i>
                                    <p>{{languageData.label.no_new_notifications}}</p>
                                </div>
                            </div>
                            <div class="notification-setting">
                                <h3 class="setting-header">{{languageData.label.notification_settings}}</h3>
                                <div class="setting-body" v-if="notificationSettingList.length > 0">
                                    <div class="setting-bar">
                                        <span>{{languageData.label.get_notification_for}}</span>
                                    </div>
                                    <b-list-group data-simplebar>
                                        <b-form-checkbox-group id="checkbox-group-2" v-model="selectedNotification"
                                            name="flavour-2">
                                            <b-list-group-item v-for="(data, index) in notificationSettingList"
                                                :key="index">
                                                <b-form-checkbox :value="data.notification_type_id">
                                                    {{data.notification_type}} </b-form-checkbox>
                                            </b-list-group-item>
                                        </b-form-checkbox-group>
                                        <b-form-checkbox-group id="checkbox-group-1" v-model="getEmailNotificationSelected"
                                            name="flavour-1">

                                            <b-list-group-item>
                                                <b-form-checkbox v-bind:value="getEmailNotification">
                                                    {{languageData.label.receive_email_notification}} </b-form-checkbox>
                                            </b-list-group-item>

                                        </b-form-checkbox-group>
                                    </b-list-group>


                                </div>
                                <div class="setting-footer">
                                    <b-button class="btn-bordersecondary" @click="saveNotificationSetting">
                                        {{languageData.label.save}}</b-button>
                                    <b-button class="btn-borderprimary" @click="cancelsetting">
                                        {{languageData.label.cancel}}
                                    </b-button>
                                </div>
                            </div>
                        </b-popover>
                    </div>
                </b-container>
            </b-navbar>
        </div>
    </template>

    <script>
        import store from '../../store';
        import {
            exploreMission,
            policy,
            loadLocaleMessages,
            logout,
            notificationSettingListing,
            updateNotificationSetting,
            clearNotification,
            readNotification,
            notificationListing
        } from '../../services/service';
        import {
            eventBus
        } from "../../main";
        import constants from '../../constant';
        import moment from 'moment'
        import {
            setTimeout
        } from 'timers';
        import AppCustomDropdown from '../../components/AppCustomDropdown';
        export default {
            components: {
                AppCustomDropdown
            },
            name: "PrimaryHeader",
            data() {
                return {
                    popoverShow: false,
                    topTheme: [],
                    topCountry: [],
                    topCountryClass: 'no-dropdown',
                    topThemeClass: 'no-dropdown',
                    topOrganizationClass: 'no-dropdown',
                    filterData: [],
                    topOrganization: [],
                    languageData: [],
                    policyPage: [],
                    isThemeDisplay: true,
                    isStoryDisplay: true,
                    isNewsDisplay: true,
                    isPolicyDisplay: true,
                    isNotificationAjaxCall: false,
                    notificationSettingList: [],
                    selectedNotification: [],
                    notificationSettingId: [],
                    notificationListing: {
                        'today': [],
                        'yesterday': [],
                        'older': []
                    },
                    notificationCount: 0,
                    totalNotificationCount: 0,
                    isNotificationLoaded: false,
                    submitNewMissionUrl: '',
                    isSubmitNewMissionSet: true,
                    hostUrl: '',
                    getEmailNotification: 0,
                    getEmailNotificationSelected: [],
                    isVolunteeringSettingEnabled: false
                };
            },
            mounted() {


            },
            methods: {
                showclearitem() {
                    let popoverBody = document.querySelector(".popover-body");
                    popoverBody.classList.add("clear-item");
                    clearNotification().then(response => {
                        if (response.error == false) {
                            this.notificationCount = 0
                            this.getNotificationListing();
                        }
                    })
                },
                showsetting() {
                    setTimeout(() => {
                        let popoverBody = document.querySelector(".popover-body");
                        popoverBody.classList.toggle("show-setting");
                    }, 150);

                    this.getNotificationSettingListing()
                },
                cancelsetting() {
                    this.selectedNotification = []
                    let popoverBody = document.querySelector(".popover-body");
                    popoverBody.classList.remove("show-setting");
                    this.$root.$emit("bv::show::popover", "notificationPopover");
                    this.notificationSettingList.filter((data, index) => {
                        if (data.is_active == 1) {
                            this.selectedNotification.push(data.notification_type_id)
                        }
                    })
                },
                openMenu() {
                    let body = document.querySelectorAll("body, html");
                    body.forEach(function (e) {
                        e.classList.add("open-nav");
                    });
                },
                closeMenu() {
                    let body = document.querySelectorAll("body, html");
                    body.forEach(function (e) {
                        e.classList.remove("open-nav");
                    });
                },
                searchMenu() {
                    let body = document.querySelectorAll("body, html");
                    body.forEach(function (e) {
                        e.classList.toggle("open-search");
                    });
                },
                logout() {
                        document.querySelector('body').classList.remove('small-header');
                        logout();
                },
                menuBarclickHandler() {

                    if (this.$route.params.searchParamsType) {
                        this.filterData['parmasType'] = this.$route.params.searchParamsType;
                    }
                    if (this.$route.params.searchParams) {
                        this.filterData['parmas'] = this.$route.params.searchParams;
                    }
                    eventBus.$emit('clearAllFilters');
                    // async () => {
                    //     await eventBus.$emit('clearAllFilters');
                    // }

                    eventBus.$emit('setDefaultText');
                    this.$emit('exploreMisison', this.filterData);
                    let body = document.querySelectorAll("body, html");
                    body.forEach(function (e) {
                        e.classList.remove("open-nav");
                    });
                },

                async exploreMissions() {
                    await exploreMission().then(() => {
                        let menuBar = JSON.parse(store.state.menubar);
                        this.topTheme = menuBar.top_theme;
                        this.topCountry = menuBar.top_country;
                        this.topOrganization = menuBar.top_organization;
                        if (this.topTheme != null && this.topTheme.length > 0) {
                            this.topThemeClass = 'has-submenu';
                        }
                        if (this.topCountry != null && this.topCountry.length > 0) {
                            this.topCountryClass = 'has-submenu';
                        }
                        if (this.topOrganization != null && this.topOrganization.length > 0) {
                            this.topOrganizationClass = 'has-submenu';
                        }
                    });
                },

                getNotificationListing() {
                    notificationListing().then(response => {
                        if (response.error == false) {
                            if (response.data) {
                                if (response.data.notifications) {
                                    this.notificationListing = {
                                        'today': [],
                                        'yesterday': [],
                                        'older': []
                                    }
                                    this.totalNotificationCount = 0;
                                    let notificationData = response.data.notifications;
                                    notificationData.filter((data, index) => {

                                        let notificationDate = moment(data.created_at).format('DD');
                                        let todaysDate = moment().format('DD');
                                        if (notificationDate == todaysDate) {
                                            this.notificationListing.today.push(data)
                                            this.totalNotificationCount++;
                                        } else if (notificationDate == (todaysDate - 1)) {
                                            this.notificationListing.yesterday.push(data)
                                            this.totalNotificationCount++;
                                        } else {
                                            this.notificationListing.older.push(data)
                                            this.totalNotificationCount++;
                                        }

                                    })

                                    //  this.notificationListing
                                } else {
                                    this.totalNotificationCount = 0;
                                    this.notificationListing = {
                                        'today': [],
                                        'yesterday': [],
                                        'older': []
                                    }

                                }
                                if (response.data.unread_notifications) {
                                    this.notificationCount = response.data.unread_notifications
                                } else {
                                    this.notificationCount = 0
                                }
                            } else {
                                this.notificationCount = 0
                                this.totalNotificationCount = 0;
                                this.notificationListing = {
                                    'today': [],
                                    'yesterday': [],
                                    'older': []
                                }

                            }

                        }
                        this.isNotificationLoaded = true
                    })
                },
                getNotificationSettingListing() {
                    setTimeout(() => {
                        if (this.totalNotificationCount <= 0) {
                            let popoverBody = document.querySelector(".popover-body");
                            popoverBody.classList.add("clear-item");
                        }
                        this.notificationSettingId = []
                        this.selectedNotification = []
                        this.isNotificationAjaxCall = true;
                        notificationSettingListing().then(response => {
                            this.isNotificationAjaxCall = false;
                            if (response.error == false) {
                                if (response.data) {
                                    this.notificationSettingList = response.data
                                    this.notificationSettingList.filter((data, index) => {
                                        data.notification_type = this.languageData.label[data
                                            .notification_type]
                                        this.notificationSettingId.push(data
                                            .notification_type_id);
                                        if (data.is_active == 1) {
                                            this.selectedNotification.push(data
                                                .notification_type_id)
                                        }
                                    })
                                }
                            }
                        })
                    }, 100)
                },
                saveNotificationSetting() {
                    let data = {
                        'settings': [],
                        'user_settings' : []

                    }
                    let settingArray = []
                    let notificationEmail = 0;


                    if(this.getEmailNotificationSelected.length != 0) {
                         data.user_settings.push({
                            'receive_email_notification':1
                        })
                    } else {
                        data.user_settings.push({
                            'receive_email_notification':0
                        })
                    }

                    this.notificationSettingId.filter((data, index) => {
                        let values = 0;
                        if (this.selectedNotification.includes(data)) {
                            values = 1;
                        }
                        settingArray.push({
                            'notification_type_id': data,
                            'value': values
                        })

                    })
                    data.settings = settingArray

                    updateNotificationSetting(data).then(response => {
                        let classVariant = 'success'
                        if (response.error == true) {
                            classVariant = 'danger'
                        } else {
                            this.cancelsetting();

                            if(this.getEmailNotificationSelected.length != 0) {
                                store.commit('changeNotificationFlag',1)
                            } else {
                                store.commit('changeNotificationFlag',0)
                            }

                            this.getEmailNotification = store.state.getEmailNotification;
                            this.getEmailNotificationSelected = [];
                            if(store.state.getEmailNotification == 1) {
                                this.getEmailNotificationSelected.push(store.state.getEmailNotification)
                            }
                        }

                        this.makeToast(classVariant, response.message)
                    })
                },
                makeToast(variant = null, message) {
                    this.$bvToast.toast(message, {
                        variant: variant,
                        solid: true,
                        autoHideDelay: 3000
                    })
                },
                readItem(event, isRead, notificationId, link) {
                    event.stopPropagation();
                    let routeData = this.$router.resolve({
                        path: link
                    });
                    window.open(routeData.href, '_blank');
                    if (isRead == 0 && notificationId) {

                        readNotification(notificationId).then(response => {
                            if (response.error == false) {
                                this.getNotificationListing();
                            }
                        })
                    }
                },
                readUnreadItem(event, isRead, notificationId) {
                    event.stopPropagation();
                    readNotification(notificationId).then(response => {
                        if (response.error == false) {
                            this.getNotificationListing();
                        }
                    })
                },
                getTooltipTitle(isRead) {
                    if (isRead == 0) {
                        return this.languageData.label.mark_as_read
                    } else {
                        return this.languageData.label.mark_as_un_read
                    }
                },
                submitNewMission() {
                    if (this.submitNewMissionUrl != '') {
                        window.open(this.submitNewMissionUrl, '_blank');
                    }
                },
                setPolicyPage() {
                    policy().then(response => {
                      if (response.error == false) {
                        if(response.data.length > 0) {
                          this.policyPage = response.data;
                          return store.commit('policyPage',response.data);
                        }
                      }
                      store.commit('policyPage',null);
                    });
                }
            },
            created() {
                this.languageData = JSON.parse(store.state.languageLabel);
                this.submitNewMissionUrl = store.state.submitNewMissionUrl
                this.isSubmitNewMissionSet = this.settingEnabled(constants.USER_CAN_SUBMIT_MISSION);
                this.isVolunteeringSettingEnabled = this.settingEnabled(constants.SETTING_VOLUNTEERING);
                this.hostUrl = process.env.BASE_URL;
                this.getEmailNotification = store.state.getEmailNotification;
                if (store.state.getEmailNotification == 1) {
                    this.getEmailNotificationSelected.push(store.state.getEmailNotification)
                }
                if (!store.state.isLoggedIn) {
                    this.isSubmitNewMissionSet = false
                }

                if (JSON.parse(store.state.policyPage) === null && store.state.isLoggedIn === true) {
                    this.setPolicyPage();
                } else {
                    this.policyPage = JSON.parse(store.state.policyPage);
                }

                setTimeout(function () {
                    let notificationMenu = document.querySelector(".notification-menu");
                    if (notificationMenu != null) {
                        notificationMenu.addEventListener("click", function (e) {
                            e.stopPropagation();
                        });
                    }
                    let notifyStatus = document.querySelectorAll(".status");
                    notifyStatus.forEach(function (statusEvent) {
                        statusEvent.addEventListener("mouseover", function () {
                            setTimeout(function () {
                                let tooltip = document.querySelector(".tooltip");
                                tooltip.classList.add("notify-tooltip");
                            });
                        });
                    });



                    let hasmenuList = document.querySelectorAll(".menu-wrap li");
                    for (let i = 0; i < hasmenuList.length; ++i) {
                        let anchorValue = hasmenuList[i].firstChild;
                        anchorValue.addEventListener("click", function (e) {
                            if (screen.width < 992) {
                                e.stopPropagation();
                                let parentList = e.target.parentNode;
                                let parentUl = parentList.parentNode;
                                let siblingList = parentUl.childNodes;
                                if (parentList.classList.contains("active")) {
                                    parentList.classList.remove("active");
                                } else {
                                    parentList.classList.add("active");
                                }
                                for (let j = 0; j < siblingList.length; ++j) {
                                    if (siblingList[j] != parentList) {
                                        siblingList[j].classList.remove("active");
                                    } else {
                                        let childList = parentList.getElementsByClassName(
                                            "has-submenu"
                                        );
                                        for (let k = 0; k < childList.length; ++k) {
                                            childList[k].classList.remove("active");
                                        }
                                    }
                                }
                            }
                        });
                    }
                    let noMenuList = document.querySelectorAll(".menu-wrap li.no-dropdown");
                    let removeActive = document.querySelector(".navbar-toggler");
                    let breadcrumbDropdown = document.querySelector(
                        ".breadcrumb-dropdown-wrap"
                    );
                    for (let i = 0; i < noMenuList.length; i++) {
                        let anchor_val = noMenuList[i].firstChild;
                        anchor_val.addEventListener("click", function (e) {
                            if (screen.width < 992) {
                                let body = document.querySelectorAll("body, html");
                                body.forEach(function (e) {
                                    e.classList.remove("open-nav");
                                });
                            }
                        });
                    }

                    if (removeActive) {
                        removeActive.addEventListener("click", function () {
                            if (screen.width < 992) {
                                for (let i = 0; i < hasmenuList.length; ++i) {
                                    hasmenuList[i].classList.remove("active");
                                }
                            }
                            if (screen.width < 768) {
                                if (breadcrumbDropdown != null) {
                                    breadcrumbDropdown.classList.remove("open");
                                }
                            }
                        });
                    }

                    let backBtn = document.querySelectorAll(".btn-back");
                    backBtn.forEach(function (e) {
                        e.addEventListener("click", function () {
                            if (screen.width < 992) {
                                let activeItem = e.parentNode.parentNode;
                                activeItem.classList.remove("active");
                            }
                        });
                    });
                    let selectorList = document.querySelectorAll(".nav-link, .nav-item.profile-menu, .nav-item.profile-menu .nav-link");
                    let notificationMenuLink = document.querySelector(".notification-menu .nav-link");
                    for (let i = 0; i < selectorList.length; i++) {
                        if (notificationMenuLink != selectorList[i]) {
                            let selectorClick = selectorList[i];
                            selectorClick.addEventListener("click", function () {
                                let notification_btn = document.querySelector(".btn-notification");
                                let notificationPopover = document.querySelector(".notification-popover");
                                if (notificationPopover != null) {
                                    notification_btn.click();
                                }
                            });
                        }
                    }
                }, 1000);
                document.addEventListener("scroll", this.handscroller);
                this.isThemeDisplay = this.settingEnabled(constants.THEMES_ENABLED);
                this.isStoryDisplay = this.settingEnabled(constants.STORIES_ENABLED);
                this.isNewsDisplay = this.settingEnabled(constants.NEWS_ENABLED);
                this.isPolicyDisplay = this.settingEnabled(constants.POLICIES_ENABLED);
                if (store.state.isLoggedIn) {
                    this.exploreMissions();
                    this.getNotificationListing()
                }

                window.addEventListener("resize", function () {
                    let body = document.querySelectorAll("body, html");
                    if (screen.width > 991) {
                        body.forEach(function (e) {
                            e.classList.remove("open-nav");
                            e.classList.remove("open-filter");
                        });
                    }
                });
            }
        };

    </script>
