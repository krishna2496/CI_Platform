<template>
<div v-bind:class="[handleFilterCount()]">
    <b-container>
        <b-row>
            <b-col xl="5" lg="4" class="search-block">
                <div class="icon-input">
                    <b-form-input type="text" v-on:keyup.enter="searchMission" :placeholder="searchPlaceHolder" @focus="handleFocus()" @blur="handleBlur()" v-model="searchString" id="search" @keyup="searchMissionString">
                    </b-form-input>
                    <i>
                        <img :src="$store.state.imagePath+'/assets/images/search-ic.svg'" alt="Search">
                    </i>
                </div>
                <i class="clear-btn" @click="clearSearchFilter">

                    <img :src="$store.state.imagePath+'/assets/images/cross-ic.svg'" :title="languageData.label.clear_search" alt="clear" />
                </i>
            </b-col>
            <b-col xl="7" lg="8" class="filter-block" v-if="quickAccessFilterSet">
                <div class="mobile-top-block">
                    <b-button class="btn btn-back" @click="handleBack">
                        <img :src="$store.state.imagePath+'/assets/images/down-arrow.svg'" alt="Back Icon">
                    </b-button>
                    <b-button class="btn btn-clear" @click="clearMissionFilters">{{languageData.label.clear_all}}
                    </b-button>
                </div>

                <b-list-group>
                    <b-list-group-item v-if="isCountrySelectionSet" @click="quickAcessFilterChange()">
                        <AppFilterDropdown :optionList="countryList" :defaultText="defaultCountry" translationEnable="false" @updateCall="changeCountry" v-if="isComponentVisible" />
                    </b-list-group-item>
                    <b-list-group-item v-if="isStateSelectionDisplay">
                        <AppCheckboxDropdown @quickAcessFilterChange="quickAcessFilterChange" v-if="isComponentVisible" :filterTitle="defaultState" :selectedItem="selectedState" :checkList="stateList" @updateCall="changeState" />
                    </b-list-group-item>
                    <b-list-group-item>
                        <AppCheckboxDropdown @quickAcessFilterChange="quickAcessFilterChange" v-if="isComponentVisible" :filterTitle="defaultCity" :selectedItem="selectedCity" :checkList="cityList" @updateCall="changeCity" />
                    </b-list-group-item>
                    <b-list-group-item v-if="isThemeDisplay">
                        <AppCheckboxDropdown @quickAcessFilterChange="quickAcessFilterChange" v-if="isComponentVisible" :filterTitle="defaultTheme" :selectedItem="selectedTheme" :checkList="themeList" @changeParmas="changeThemeParmas" @updateCall="changeTheme" />
                    </b-list-group-item>
                    <b-list-group-item v-if="isSkillDisplay && isVolunteeringSettingEnabled">
                        <AppCheckboxDropdown @quickAcessFilterChange="quickAcessFilterChange" v-if="isComponentVisible" :filterTitle="defaultSkill" :checkList="skillList" :selectedItem="selectedSkill" @changeParmas="changeSkillParmas" @updateCall="changeSkill" />
                    </b-list-group-item>
                </b-list-group>
            </b-col>

            <div class="filter-icon" @click="handleFilter" @click.stop v-if="quickAccessFilterSet">
                <img :src="$store.state.imagePath+'/assets/images/filter-ic.svg'" alt="filter">
            </div>
        </b-row>
    </b-container>
</div>
</template>

<script>
import AppFilterDropdown from "../AppFilterDropdown";
import AppCheckboxDropdown from "../AppCheckboxDropdown";
import {
    filterList
} from "../../services/service";
import store from "../../store";
import {
    eventBus
} from "../../main";
import constants from '../../constant';

export default {
    components: {
        AppFilterDropdown,
        AppCheckboxDropdown
    },
    name: "TheSecondaryHeader",
    props: [
        'search',
        'missionList'
    ],
    data() {
        return {
            searchPlaceHolder: '',
            defaultCountry: "Country",
            defaultCity: "",
            defaultState: "",
            defaultTheme: "",
            defaultSkill: "",
            stateList: [],
            countryList: [],
            cityList: [],
            themeList: [],
            skillList: [],
            filterList: [],
            selectedCity: [],
            selectedSkill: [],
            selectedTheme: [],
            selectedState: [],
            form: {
                text: ""
            },
            selectedfilterParams: {
                countryId: "",
                stateId: "",
                cityId: "",
                themeId: "",
                skillId: "",
                tags: [],
                sortBy: "",
                search: "",
                exploreMissionType: '',
                exploreMissionParams: ''
            },
            show: false,
            isComponentVisible: false,
            tagsFilter: [],
            quickAccessFilterSet: true,
            isThemeDisplay: true,
            isSkillDisplay: true,
            isCountryChange: false,
            isCityChange: false,
            isThemeChange: false,
            isStateChange: false,
            isCountrySelectionSet: false,
            isStateSelectionDisplay: false,
            searchString: this.search,
            languageData: [],
            isStateClick: false,
            isCityClick: false,
            isThemeClick: false,
            isSkillClick: false,
            isVolunteeringSettingEnabled: true
        };
    },
    mounted() {
        let mobileFilter = document.querySelector(".filter-block");
        if (mobileFilter != null) {
            mobileFilter.addEventListener("click", function (e) {
                if (window.innerWidth < 992) {
                    e.stopPropagation();
                }
            });
        }
    },
    methods: {
        quickAcessFilterChange(val) {
            if (!val) {
                return;
            }

            if (val == this.defaultState) {
                this.isStateClick = true
            }
            if (val == this.defaultCity) {
                this.isCityClick = true
            }
            if (val == this.defaultTheme) {
                this.isThemeClick = true
            }
            if (val == this.defaultSkill) {
                this.isSkillClick = true
            }
            this.$parent.addLoader();
        },
        changeThemeParmas() {
            this.isCountryChange = false;
            this.isCityChange = false;
        },

        changeSearch() {
            this.searchString = '';
            this.selectedfilterParams.search = '';
            this.filterSearchListing();
        },

        changeSkillParmas() {
            this.isCountryChange = false;
            this.isCityChange = false;
            this.isThemeChange = false;
        },

        searchMissionString() {
            this.$emit('storeMisisonSearch', this.searchString);
        },

        handleFocus() {
            this.searchPlaceHolder = '';
            let b_header = document.querySelector(".bottom-header");
            b_header.classList.add("active");
        },

        removeItems(data) {
            if (data.selectedType == "country") {
                data.selectedId = store.state.defaultCountryId;
                let selectedCountryData = this.countryList.filter((country) => {
                    return (data.selectedId == country[1].id);
                });
                data.selectedVal = selectedCountryData[0][1].title;
                this.changeCountry(data)
            }
            if (data.selectedType == "state") {
                this.isCountryChange = false;
                this.isStateChange = false;
                this.isCityChange = false;
                this.isThemeChange = false;
                this.isStateClick = true
                let selectedData = store.state.stateId.toString().split(',');
                let filteredState = selectedData.filter((value) => {
                    return value != data.selectedId;
                });
                this.selectedState = filteredState;
                this.selectedCity = [];
                this.selectedTheme = [];
                this.selectedSkill = [];
            }
            if (data.selectedType == "city") {
                this.isCountryChange = false;
                this.isStateChange = false;
                this.isCityChange = false;
                this.isThemeChange = false;
                this.isCityClick = true
                let selectedData = store.state.cityId.toString().split(',');
                let filteredCity = selectedData.filter((value) => {
                    return value != data.selectedId;
                });
                this.selectedCity = filteredCity;
                this.selectedTheme = [];
                this.selectedSkill = [];
            }
            if (data.selectedType == "theme") {
                this.isCountryChange = false;
                this.isStateChange = false;
                this.isCityChange = false;
                this.isThemeChange = false;
                this.isThemeClick = true
                let selectedData = store.state.themeId.toString().split(',');
                let filteredTheme = selectedData.filter((value) => {
                    return value != data.selectedId;
                });
                this.selectedSkill = [];
                this.selectedTheme = filteredTheme;

            }
            if (data.selectedType == "skill") {
                this.isSkillClick = true
                let selectedData = store.state.skillId.toString().split(',');
                let filteredSkill = selectedData.filter((value) => {
                    return value != data.selectedId;
                });
                this.selectedSkill = filteredSkill;
            }
        },

        handleBlur() {
            this.searchPlaceHolder = this.languageData.label.search_mission;
            let b_header = document.querySelector(".bottom-header");
            let input_edit = document.querySelector(".search-block input");
            b_header.classList.remove("active");
            if (input_edit.value.length > 0) {
                b_header.classList.add("active");
            } else {
                b_header.classList.remove("active");
            }
        },

        handleFilter() {
            let body = document.querySelectorAll("body, html");
            body.forEach(function (e) {
                e.classList.add("open-filter");
            });
        },

        handleBack() {
            let body = document.querySelectorAll("body, html");
            body.forEach(function (e) {
                e.classList.remove("open-filter");
            });
        },
        handleFilterCount() {
            let returnData = [];

            if (this.searchString != '') {
                returnData.push('active')
            } else {
                returnData = [];
            }

            returnData.push('bottom-header')
            let filterCount = document.querySelectorAll(
                ".filter-block .list-group-item"
            ).length;

            if (filterCount != null) {
                if (filterCount == 4) {
                    returnData.push('four-filters')
                } else if (filterCount == 3) {
                    returnData.push('three-filters')
                } else if (filterCount == 2) {
                    returnData.push('two-filters')
                } else if (filterCount == 1) {
                    returnData.push('one-filter')
                } else if (filterCount == 0) {
                    returnData.push('zero-filter')
                }
            }
            return returnData;
        },

        async changeCountry(country) {
            this.isCountryChange = true;
            this.selectedfilterParams.countryId = country.selectedId;
            if (country.selectedId != '') {
                this.defaultCountry = country.selectedVal.replace(/<\/?("[^"]*"|'[^']*'|[^>])*(>|$)/g, "");
                this.defaultCountry = this.defaultCountry.replace(/[^a-zA-Z\s]+/g, '');
            } else {
                this.defaultCountry = this.languageData.label.country;
            }
            this.selectedfilterParams.stateId = '';
            this.selectedfilterParams.cityId = '';
            this.selectedfilterParams.themeId = '';
            this.selectedfilterParams.skillId = '';
            this.selectedfilterParams.exploreMissionType = store.state.exploreMissionType
            this.selectedfilterParams.exploreMissionParams = store.state.exploreMissionParams;
            this.cityList = [];
            this.themeList = [];
            this.skillList = [];
            this.stateList = [];
            // let filters = {};
            // filters.exploreMissionType = '';
            // filters.exploreMissionParams = '';
            // store.commit("exploreFilter", filters);
            // this.$router.push({
            //     name: 'home'
            // })
            await filterList(this.selectedfilterParams).then(response => {
                if (response) {
                    if (response.state) {
                        this.stateList = Object.entries(response.state);
                        this.selectedState = [];
                    }
                    if (response.city) {
                        this.cityList = Object.entries(response.city);
                        this.selectedCity = [];
                    }

                    if (response.themes) {
                        this.themeList = Object.entries(response.themes);
                        this.selectedTheme = [];
                    }

                    if (response.skill) {
                        this.skillList = Object.entries(response.skill);
                        this.selectedSkill = [];
                    }
                    this.quickAcessFilterChange();
                }
                this.$parent.searchMissions(this.search, this.selectedfilterParams);
            });
            this.isCountryChange = false;
        },

        async changeState(state) {
            this.isStateChange = true;
            if (!this.isCountryChange && this.isStateClick) {
                this.selectedfilterParams.stateId = state;
                this.selectedfilterParams.cityId = '';
                this.selectedfilterParams.themeId = '';
                this.selectedfilterParams.skillId = '';
                this.selectedfilterParams.exploreMissionType = store.state.exploreMissionType
                this.selectedfilterParams.exploreMissionParams = store.state.exploreMissionParams;
                this.cityList = [];
                this.themeList = [];
                this.skillList = [];

                await filterList(this.selectedfilterParams).then(response => {
                    if (response) {
                        if (response.city) {
                            this.cityList = Object.entries(response.city);
                            this.selectedCity = [];
                        }
                        if (response.themes) {
                            this.themeList = Object.entries(response.themes);
                            this.selectedTheme = [];
                        }

                        if (response.skill) {
                            this.skillList = Object.entries(response.skill);
                            this.selectedSkill = [];
                        }
                        this.isStateClick = false
                    }
                    this.$parent.searchMissions(this.search, this.selectedfilterParams);
                });
                this.isStateChange = false;
            }
        },

        async changeCity(city) {
            this.isCityChange = true;
            if (!this.isCountryChange && this.isCityClick) {
                this.selectedfilterParams.cityId = city;
                this.selectedfilterParams.themeId = '';
                this.selectedfilterParams.skillId = '';
                this.selectedfilterParams.exploreMissionType = store.state.exploreMissionType
                this.selectedfilterParams.exploreMissionParams = store.state.exploreMissionParams;
                this.themeList = [];
                this.skillList = [];

                await filterList(this.selectedfilterParams).then(response => {
                    if (response) {
                        if (response.themes) {
                            this.themeList = Object.entries(response.themes);
                            this.selectedTheme = [];
                        }

                        if (response.skill) {
                            this.skillList = Object.entries(response.skill);
                            this.selectedSkill = [];
                        }
                        this.isCityClick = false
                    }
                    this.$parent.searchMissions(this.search, this.selectedfilterParams);
                });
                this.isCityChange = false;
            }
        },

        async changeTheme(theme) {
            this.isThemeChange = true;
            if (!this.isCountryChange && !this.isCityChange && this.isThemeClick) {
                this.selectedfilterParams.themeId = theme;
                this.selectedfilterParams.skillId = '';
                this.selectedfilterParams.exploreMissionType = store.state.exploreMissionType
                this.selectedfilterParams.exploreMissionParams = store.state.exploreMissionParams;
                this.skillList = [];
                this.selectedSkill = [];

                await filterList(this.selectedfilterParams).then(response => {
                    if (response) {
                        if (response.skill) {
                            this.skillList = Object.entries(response.skill);
                            this.selectedSkill = [];
                        }
                        this.isThemeClick = false
                    }
                    this.$parent.searchMissions(this.search, this.selectedfilterParams);
                });
                this.isThemeChange = false;
            }
        },

        async changeSkill(skill) {
            if (!this.isCountryChange && !this.isCityChange && !this.isThemeChange && this.isSkillClick) {
                this.selectedfilterParams.skillId = skill;
                this.selectedfilterParams.exploreMissionType = store.state.exploreMissionType
                this.selectedfilterParams.exploreMissionParams = store.state.exploreMissionParams;
                this.$parent.searchMissions(this.search, this.selectedfilterParams);
                this.isSkillClick = false
            }
        },

        // Filter listing
        filterListing() {
            let tags = {
                'country': [],
                'state': [],
                'city': [],
                'theme': [],
                'skill': []
            }

            setTimeout(() => {
                this.defaultCity = this.languageData.label.city,
                    this.defaultState = this.languageData.label.state,
                    this.defaultTheme = this.languageData.label.theme,
                    this.defaultSkill = this.languageData.label.skills
            }, 500)

            this.selectedfilterParams.countryId = store.state.countryId;
            this.selectedfilterParams.stateId = store.state.stateId;
            this.selectedfilterParams.cityId = store.state.cityId;
            this.selectedfilterParams.themeId = store.state.themeId;
            this.selectedfilterParams.skillId = store.state.skillId;
            this.selectedfilterParams.search = store.state.search;
            this.selectedfilterParams.exploreMissionType = store.state.exploreMissionType
            this.selectedfilterParams.exploreMissionParams = store.state.exploreMissionParams;
            this.selectedState = [];
            this.selectedCity = [];
            this.selectedTheme = [];
            this.selectedSkill = [];
            filterList(this.selectedfilterParams).then(response => {
                if (response) {
                    if (response.country) {
                        this.countryList = Object.entries(response.country);
                    }

                    if (response.state) {
                        this.stateList = Object.entries(response.state);
                    }

                    if (response.city) {
                        this.cityList = Object.entries(response.city);
                    }

                    if (response.themes) {
                        this.themeList = Object.entries(response.themes);
                    }

                    if (response.skill) {
                        this.skillList = Object.entries(response.skill);
                    }

                    if (store.state.countryId != '') {
                        if (this.countryList) {
                            let selectedCountryData = this.countryList.filter((country) => {
                                if (store.state.countryId == country[1].id) {
                                    return country;
                                }
                            });
                            if (selectedCountryData[0]) {
                                this.defaultCountry = selectedCountryData[0][1].title;
                                tags.country[0] = selectedCountryData[0][1].id + '_' + selectedCountryData[
                                    0][1].title;
                            } else {
                                this.defaultCountry = this.languageData.label.country;
                                tags.country[0] = '';
                            }
                        }
                    } else {
                        this.defaultCountry = this.languageData.label.country;
                    }

                    if (store.state.stateId != '') {
                        this.selectedState = store.state.stateId.toString().split(',')
                    }

                    if (store.state.cityId != '') {
                        this.selectedCity = store.state.cityId.toString().split(',')
                    }

                    if (store.state.themeId != '') {
                        this.selectedTheme = store.state.themeId.toString().split(',')
                    }

                    if (store.state.skillId != '') {
                        this.selectedSkill = store.state.skillId.toString().split(',')
                    }

                }
                this.isComponentVisible = true;
            });
        },

        searchMission() {
            this.$parent.searchMissions(this.search, this.selectedfilterParams);
            this.selectedfilterParams.search = this.search
            this.filterSearchListing();
        },

        fetchFilters() {
            this.$emit('cmsListing', this.$route.params.slug);
        },

        clearFilter() {

            this.selectedfilterParams.countryId = '';
            this.defaultCountry = this.languageData.label.country;
            this.defaultState = this.languageData.label.state;
            this.selectedfilterParams.stateId = '';
            this.selectedfilterParams.cityId = '';
            this.selectedfilterParams.themeId = '';
            this.selectedfilterParams.skillId = '';
            this.selectedfilterParams.sortBy = '';
            if (this.$route.params.searchParamsType) {
                this.selectedfilterParams.exploreMissionType = this.$route.params.searchParamsType
            }
            if (this.$route.params.searchParams) {
                this.selectedfilterParams.exploreMissionParams = this.$route.params.searchParams;
            }
            this.selectedState = [];
            this.selectedCity = [];
            this.selectedSkill = [];
            this.selectedTheme = [];
            filterList(this.selectedfilterParams).then(response => {
                if (response) {
                    if (response.state) {
                        this.stateList = Object.entries(response.state);
                        this.selectedState = [];
                    }
                    if (response.city) {
                        this.cityList = Object.entries(response.city);
                        this.selectedCity = [];
                    }
                }
            });
        },
        // Filter listing
        filterSearchListing() {
            filterList(this.selectedfilterParams).then(response => {
                if (response) {
                    if (response.country) {
                        this.countryList = Object.entries(response.country);
                    } else {
                        this.countryList = []
                        this.defaultCountry = this.languageData.label.country;
                    }

                    if (response.state) {
                        this.stateList = Object.entries(response.state);
                    } else {
                        this.stateList = []
                    }

                    if (response.city) {
                        this.cityList = Object.entries(response.city);
                    } else {
                        this.cityList = []
                    }

                    if (response.themes) {
                        this.themeList = Object.entries(response.themes);
                    } else {
                        this.themeList = []
                    }

                    if (response.skill) {
                        this.skillList = Object.entries(response.skill);
                    } else {
                        this.skillList = []
                    }
                } else {
                    this.defaultCountry = this.languageData.label.country;
                    this.countryList = []
                    this.stateList = []
                    this.cityList = []
                    this.themeList = []
                    this.skillList = []
                    this.selectedCity = []
                    this.selectedSkill = []
                    this.selectedTheme = []
                }
            });
        },
        clearSearchFilter() {
            this.searchString = '';
            this.selectedfilterParams.search = '';
            this.$parent.searchMissions(this.searchString, this.selectedfilterParams);
            this.filterSearchListing();

            setTimeout(() => {
                this.handleBlur()
            }, 200)

        },
        clearAllFilter() {
            this.selectedfilterParams.countryId = '';
            this.selectedfilterParams.stateId = '';
            this.selectedfilterParams.cityId = '';
            this.selectedfilterParams.themeId = '';
            this.selectedfilterParams.skillId = '';
            this.stateList = [];
            this.cityList = [];
            this.themeList = [];
            this.skillList = [];
            let filters = {};
            filters.exploreMissionType = '';
            filters.exploreMissionParams = '';
            store.commit("exploreFilter", filters);
            let userFilter = {};
            userFilter.search = store.state.search;
            userFilter.sortBy = store.state.sortBy;
            userFilter.countryId = '';
            userFilter.stateId = '';
            userFilter.cityId = '';
            userFilter.themeId = '';
            userFilter.skillId = '';
            userFilter.tags = [];
            userFilter.sortBy = store.state.sortBy;
            store.commit("userFilter", userFilter);
            const currentPath = this.$route.name;
            if (currentPath !== 'home') {
                this.$router.push({
                    name: 'home'
                });
            }
            let country = {};
            country.selectedId = store.state.defaultCountryId;
            let selectedCountryData = this.countryList.filter((countryData) => {
                return (country.selectedId == countryData[1].id);
            });
            country.selectedVal = selectedCountryData[0][1].title;
            this.changeCountry(country);
        },
        clearMissionFilters() {
            this.$parent.clearMissionFilter();
        }
    },
    created() {
        this.languageData = JSON.parse(store.state.languageLabel);

        this.searchPlaceHolder = this.languageData.label.search_mission;
        this.quickAccessFilterSet = this.settingEnabled(constants.QUICK_ACCESS_FILTERS);
        this.isThemeDisplay = this.settingEnabled(constants.THEMES_ENABLED);
        this.isSkillDisplay = this.settingEnabled(constants.SKILLS_ENABLED);
        this.isCountrySelectionSet = this.settingEnabled(constants.IS_COUNTRY_SELECTION);
        this.isStateSelectionDisplay = this.settingEnabled(constants.STATE_ENABLED);
        this.isVolunteeringSettingEnabled = this.settingEnabled(constants.SETTING_VOLUNTEERING);
        eventBus.$on('clearAllFilters', () => {
            this.clearFilter();
        });
        eventBus.$on('setDefaultText', () => {
            this.defaultCountry = this.languageData.label.country;
        });
        eventBus.$on('setDefaultData', () => {
            this.filterListing();
        });
        // Fetch Filters
        this.filterListing();
        if (store.state.search != null) {
            this.search = store.state.search;
        }
        if (this.missionList.length < 0) {
            this.countryList = [];
            this.stateList = [];
            this.cityList = [];
            this.themeList = [];
            this.skillList = [];
        }
        setTimeout(() => {
            this.handleFilterCount();
        });
    }
};
</script>
