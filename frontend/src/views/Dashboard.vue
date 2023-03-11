<template>
<div class="user-dashboard inner-pages">
    <header>
        <ThePrimaryHeader v-if="isShownComponent"></ThePrimaryHeader>
    </header>
    <main>
        <DashboardBreadcrumb />
        <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active': isLoaderActive}">
            <div class="content-loader"></div>
        </div>
        <div class="dashboard-tab-content">
            <b-container v-if="isPageLoaded">
                <div class="heading-section">
                    <h1>{{languageData.label.dashboard}}</h1>
                    <div class="date-filter">
                        <AppCustomDropdown :optionList="yearList" @updateCall="updateYear"
                                           :defaultText="defaultYear" translationEnable="false" />
                        <AppCustomDropdown :optionList="monthList" @updateCall="updateMonth"
                                           :noListItem="noListItem" :defaultText="defaultMonth"
                                           translationEnable="true" class="month-dropdown" />
                    </div>
                </div>
                <div class="inner-content-wrap">
                    <b-list-group class="status-bar">
                        <b-list-group-item v-if="isTotalVolunteeredHourDisplay && isTimeMissionActive && isVolunteeringSettingEnabled">
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/clock-ic.svg'" alt />
                                </i>
                                <p>
                                    <span>{{stats.totalHours}}</span>{{languageData.label.hours}}
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item v-if="isTimeMissionActive && isVolunteeringSettingEnabled">
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/certified-ic.svg'" alt />
                                </i>
                                <p>
                                    <span>
                                        {{stats.volunteeringRank}}%</span>{{languageData.label.volunteering_rank}}
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item v-if="isVolunteeringSettingEnabled">
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/request-ic.svg'" alt />
                                </i>
                                <p>
                                    <span>{{stats.openVolunteeringRequests}}</span>{{languageData.label.open_volunteering_requests}}
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item>
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/target-ic.svg'" alt />
                                </i>
                                <p>
                                    <span>{{stats.missionCount}}</span>
                                    <p v-if="stats.missionCount <= 1">{{languageData.label.mission}}</p>
                                    <p v-else>{{languageData.label.missions}}</p>
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item>
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/group-ic.svg'" alt />
                                </i>
                                <p>
                                    <span>{{stats.organizationCount}}</span>
                                    <p v-if="stats.organizationCount <= 1">{{languageData.label.organisation}}</p>
                                    <p v-else>{{languageData.label.organisations}}</p>
                                </p>
                            </div>
                        </b-list-group-item>
                    </b-list-group>
                    <b-row class="chart-block">
                        <b-col lg="6" class="chart-col" v-if="isTimeMissionActive && isVolunteeringSettingEnabled">
                            <div class="inner-chart-col">
                                <div class="chart-title">
                                    <h5>{{languageData.label.hours_tracked_this_year}}</h5>
                                </div>
                                <div class="progress-chart">
                                    <span class="progress-status">{{languageData.label.completed}}:
                                        {{ completedGoalHours }} {{languageData.label.hours}}</span>
                                    <b-progress :max="totalGoalHours">
                                        <b-progress-bar :value="completedGoalHours">
                                        </b-progress-bar>
                                    </b-progress>
                                    <ul class="progress-axis">
                                        <li v-for="xvalue in xvalues" :key="xvalue">{{xvalue}}%</li>
                                    </ul>
                                    <p class="progress-label">{{languageData.label.goal}}: {{ totalGoalHours }}
                                        {{languageData.label.hours}}</p>
                                </div>
                            </div>
                        </b-col>
                        <b-col lg="6" class="chart-col" v-if="isTimeMissionActive && isVolunteeringSettingEnabled">
                            <div class="inner-chart-col">
                                <div class="chart-title">
                                    <h5>{{languageData.label.hours_per_month}}</h5>
                                    <model-select
                                        class="search-dropdown"
                                        v-if="missionFilterDisplay"
                                        :options="missionTitle"
                                        v-model="defaultMissionModel"
                                        :placeholder="defaultMissionTitle"
                                        @input="updateMissionTitle">
                                    </model-select>
                                </div>
                                <div
                                    v-bind:class="{ 'content-loader-wrap': true, 'loader-active': hoursMonthActive}">
                                    <div class="content-loader"></div>
                                </div>

                                <div class="line-chart" v-if="isChartDataFound">
                                    <canvas ref="barChartRefs"></canvas>
                                </div>

                                <!-- Start new chart structure -->
                                <div class="line-chart hidden-chart" v-if="!isChartDataFound">
                                    <!-- <canvas ref="barChartRefs"></canvas> -->
                                </div>
                                <div class="has-no-chart" v-if="!isChartDataFound">
                                    <p>{{languageData.label.no_data}}</p>
                                </div>
                                <!-- End new chart structure -->

                            </div>
                        </b-col>
                    </b-row>
                </div>
            </b-container>
        </div>
    </main>
    <footer>
        <TheSecondaryFooter v-if="isShownComponent"></TheSecondaryFooter>
    </footer>
</div>
</template>

<script>
import ThePrimaryHeader from "../components/Layouts/ThePrimaryHeader";
import TheSecondaryFooter from "../components/Layouts/TheSecondaryFooter";
import AppCustomDropdown from "../components/AppCustomDropdown";
import DashboardBreadcrumb from "../components/DashboardBreadcrumb";
import {
    ModelSelect
} from 'vue-search-select'
import store from '../store';
import Chart from "chart.js";
import constants from '../constant';
import {
    storyMissionListing,
    myDashboard,
} from "../services/service";
import moment from 'moment'
import {
    setTimeout
} from 'timers';
export default {
    components: {
        ThePrimaryHeader,
        AppCustomDropdown,
        TheSecondaryFooter,
        DashboardBreadcrumb,
        ModelSelect
    },

    name: "Dashboard",

    data() {
        return {
            isShownComponent: false,
            isPageLoaded: true,
            isLoaderActive: true,
            max: 0,
            xvalues: [0],
            defaultYear: "",
            defaultMonth: "",
            defaultMissionTitle: "",
            yearList: [],
            missionTitle: [],
            missionIdArray: [],
            noListItem: true,
            monthList: [
                ["0", "all"],
                ["01", "january"],
                ["02", "february"],
                ["03", "march"],
                ["04", "april"],
                ["05", "may"],
                ["06", "june"],
                ["07", "july"],
                ["08", "august"],
                ["09", "september"],
                ["10", "october"],
                ["11", "november"],
                ["12", "december"]
            ],
            chartMonthList: [
                ["1", "jan"],
                ["2", "feb"],
                ["3", "mar"],
                ["4", "apr"],
                ["5", "may"],
                ["6", "jun"],
                ["7", "jul"],
                ["8", "aug"],
                ["9", "sep"],
                ["10", "oct"],
                ["11", "nov"],
                ["12", "dec"]
            ],
            chartdata: [],
            chartHourdata: [],
            currentpage: "dashboard",
            languageData: [],
            filterData: {
                year: '',
                month: '',
                mission_id: ''
            },
            stats: {
                'totalHours': 0,
                'volunteeringRank': 0,
                'openVolunteeringRequests': 0,
                'missionCount': 0,
                'votedMissions': 0,
                'organizationCount': 0
            },
            totalGoalHours: 0,
            totalGoalHoursMax: 0,
            completedGoalHours: 0,
            chartStep: 0,
            chartMaxValue: 0,
            hoursMonthActive: false,
            goalHourPart: 10,
            defaultMissionModel: 0,
            barChartCanvas: null,
            isTotalVolunteeredHourDisplay: true,
            isChartDataFound: true,
            missionFilterDisplay: false,
            isGoalMissionActive: false,
            isTimeMissionActive: false,
            isVolunteeringSettingEnabled: true
        };
    },
    mounted() {
        var currentYear = new Date().getFullYear();
        var yearsListing = [];
        yearsListing.push([0, this.languageData.label.all])
        let yearDiff = 5;
        if (store.state.timesheetFromYear && store.state.timesheetFromYear != '') {
            let lastYear = store.state.timesheetFromYear;
            if ((currentYear - lastYear) + 1 > 0) {
                yearDiff = (currentYear - lastYear) + 1;
            }
        }
        for (var index = currentYear; index > (currentYear - yearDiff); index--) {
            yearsListing.push([index, index]);
        }
        this.yearList = yearsListing;

        // let currentYear = new Date().getFullYear();
        // let yearsList = [];
        // let yearDiff  = 5;
        // if(store.state.timesheetFromYear && store.state.timesheetFromYear != '') {
        // 	let lastYear = store.state.timesheetFromYear;
        // 	if((currentYear - lastYear) +1 > 0) {
        // 		yearDiff = (currentYear - lastYear) +1;
        // 	}
        // }
        // for (let index = currentYear; index > (currentYear - yearDiff); index--) {
        // 	yearsList.push([index, index]);
        // }
        // this.yearList = yearsList;
        // this.lastYear = parseInt(yearsList[yearsList.length -1][1]);

    },
    methods: {
        updateYear(value) {

            this.defaultYear = value.selectedVal;
            this.filterData.year = value.selectedId
            if (value.selectedId == 0) {
                this.noListItem = true
                this.defaultMonth = this.languageData.label.all;
                this.filterData.month = 0
            } else {
                this.noListItem = false
            }
            var barChartRefs = this.$refs.barChartRefs;
            this.getDashboardData(this.filterData, 'dashboad')
        },
        updateMonth(value) {
            this.defaultMonth = value.selectedVal;
            this.filterData.month = value.selectedId

            this.getDashboardData(this.filterData, 'dashboad')

        },
        updateMissionTitle(value) {
            this.defaultMissionModel = value;
            this.filterData.mission_id = value
            this.getDashboardData(this.filterData, 'graph')
        },
        handleActive() {},
        missionListing() {
            storyMissionListing().then(response => {
                // missionTitle
                var array = [];
                array.push({
                    'text': this.languageData.label.all_missions,
                    'value': 0
                })
                if (response.error == false) {
                    let missionArray = response.data
                    if (missionArray) {
                        missionArray.filter((data, index) => {
                            // array[index] = new Array(2);
                            // array[index][0] = data.mission_id
                            // array[index][1] = data.title
                            // array[index] = data.title

                            array.push({
                                'text': data.title,
                                'value': data.mission_id
                            })
                            this.missionIdArray[index] = data.mission_id
                        })
                        this.missionTitle = array
                    }
                }
            })
        },
        getDashboardData(filterData, params) {
            if (params != 'graph') {
                this.isLoaderActive = true;
            } else {
                this.hoursMonthActive = true;
            }

            myDashboard(filterData).then(response => {
                this.isChartDataFound = true
                this.missionFilterDisplay = false
                if (response.error == false) {
                    this.xvalues = [0]
                    this.max = 0
                    this.stats.totalHours = 0
                    this.stats.volunteeringRank = 0
                    this.stats.openVolunteeringRequests = 0
                    this.stats.missionCount = 0
                    this.stats.votedMissions = 0
                    this.stats.organizationCount = 0
                    this.totalGoalHours = 0
                    this.completedGoalHours = 0
                    if (response.data) {
                        if (response.data.total_hours && response.data.total_hours != '') {
                            this.stats.totalHours = response.data.total_hours
                        }
                        if (response.data.volunteering_rank && response.data.volunteering_rank != '') {
                            this.stats.volunteeringRank = response.data.volunteering_rank
                        }
                        if (response.data.open_volunteering_requests && response.data
                            .open_volunteering_requests != '') {
                            this.stats.openVolunteeringRequests = response.data.open_volunteering_requests
                        }
                        if (response.data.mission_count && response.data.mission_count != '') {
                            this.stats.missionCount = response.data.mission_count
                        }
                        if (response.data.voted_missions && response.data.voted_missions != '') {
                            this.stats.votedMissions = response.data.voted_missions
                        }
                        if (response.data.organization_count && response.data.organization_count != '') {
                            this.stats.organizationCount = response.data.organization_count
                        }
                        if (response.data.completed_goal_hours && response.data.completed_goal_hours !=
                            '') {
                            this.completedGoalHours = response.data.completed_goal_hours
                        }
                        if (response.data.total_goal_hours && response.data.total_goal_hours != '') {
                            this.totalGoalHours = response.data.total_goal_hours

                            if (screen.width < 576) {
                                this.goalHourPart = 5
                            } else {
                                this.goalHourPart = 10
                            }
                            let axes = (this.totalGoalHours / this.goalHourPart);
                            this.max = Math.ceil(axes)
                            let xValue = 0;

                            for (var i = 0; i < this.goalHourPart; i++) {
                                xValue = (i + 1) * 10;
                                if (this.goalHourPart == 5) {
                                    xValue = (i + 1) * 20;
                                }
                                this.xvalues.push(xValue)
                            }
                            this.totalGoalHoursMax = this.xvalues[this.xvalues.length - 1];;
                        }
                        if (response.data.chart) {
                            let chartData = response.data.chart;
                            let chartList = [];
                            let chartListData = []
                            let chartListHourData = []
                            let chartMaxValue = {
                                totalMaxValue: 0
                            };
                            let dataFlag = 0
                            chartData.filter((data, index) => {

                                if (data.total_hours && data.total_hours != '' && data.total_hours != 0) {
                                    chartMaxValue.totalMaxValue = chartMaxValue.totalMaxValue + data
                                        .total_hours
                                    chartList[data.month] = data.total_hours
                                    dataFlag = 1;
                                    this.missionFilterDisplay = true
                                } else {
                                    chartList[data.month] = 0
                                }
                            })
                            if (dataFlag != 1) {
                                this.isChartDataFound = false
                            }
                            if (filterData.mission_id != 0 && filterData.mission_id != '') {
                                this.missionFilterDisplay = true
                            }
                            let yearLable = (this.filterData.year.toString()).slice(2, this.filterData.year
                                .toString().length)
                            chartList.filter((data, index) => {
                                let monthLabel = this.languageData.label[this.chartMonthList[index -
                                        1]
                                    [1]];

                                chartListData.push(monthLabel + ' ' + yearLable);
                                chartListHourData.push(data);
                            })
                            this.chartdata = chartListData
                            this.chartHourdata = chartListHourData
                            if (chartMaxValue.totalMaxValue <= 0) {
                                this.chartStep = 1
                                this.chartMaxValue = 1
                            } else {
                                let step = Math.ceil((chartMaxValue.totalMaxValue) / 4)
                                this.chartStep = step
                                this.chartMaxValue = step * 4
                            }

                            // chart js
                            if (this.barChartCanvas != null) {
                                this.barChartCanvas.destroy();
                            }
                            if (this.isChartDataFound) {
                                setTimeout(() => {
                                    var barChartRefs = this.$refs.barChartRefs;
                                    var lineContent = barChartRefs.getContext("2d");
                                    barChartRefs.height = 350;

                                    let chartConfig = {
                                        type: "bar",
                                        data: {
                                            labels: this.chartdata,
                                            datasets: [{
                                                data: this.chartHourdata,
                                                backgroundColor: "#b2cc9d",
                                                borderColor: "#4b6d30",
                                                pointBackgroundColor: "#4b6d30"
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            legend: {
                                                display: false
                                            },
                                            scales: {
                                                xAxes: [{
                                                    gridLines: {
                                                        display: false
                                                    },
                                                    ticks: {
                                                        fontColor: "#414141",
                                                        fontSize: 14
                                                    },
                                                    scaleLabel: {
                                                        display: true,
                                                        labelString: this.languageData.label.month,
                                                        fontSize: 16,
                                                        fontColor: "#414141"
                                                    }
                                                }],
                                                yAxes: [{
                                                    stacked: true,
                                                    ticks: {
                                                        max: this.chartMaxValue,
                                                        min: 0,
                                                        stepSize: this.chartStep,
                                                        fontColor: "#414141",
                                                        fontSize: 14
                                                    },
                                                    scaleLabel: {
                                                        display: true,
                                                        labelString: this.languageData.label.hours,
                                                        fontSize: 16,
                                                        fontColor: "#414141"
                                                    }
                                                }]
                                            }
                                        }
                                    }
                                    this.barChartCanvas = new Chart(lineContent, chartConfig);
                                }, 500)
                            }
                        }

                    }
                }

                this.isShownComponent = true
                this.isLoaderActive = false;
                this.hoursMonthActive = false;
            })
        }
    },
    created() {
        this.languageData = JSON.parse(store.state.languageLabel);
        this.defaultYear = this.languageData.label.years
        this.defaultMonth = this.languageData.label.month
        this.defaultMissionTitle = this.languageData.label.mission_title
        this.isTotalVolunteeredHourDisplay = this.settingEnabled(constants.TOTAL_HOURS_VOLUNTEERED)
        this.isGoalMissionActive = this.settingEnabled(constants.VOLUNTEERING_GOAL_MISSION),
        this.isTimeMissionActive = this.settingEnabled(constants.VOLUNTEERING_TIME_MISSION)
        this.isVolunteeringSettingEnabled = this.settingEnabled(constants.SETTING_VOLUNTEERING);
        let currentYear = new Date().getFullYear()
        let currentMonth = moment().format('MM')
        // this.defaultYear = currentYear.toString();
        this.defaultYear = this.languageData.label.all;
        // this.monthList.filter((data, index) => {
        //     if (data[0] == currentMonth) {
        //         this.defaultMonth = data[1]
        //     }
        // })
        this.defaultMonth = this.languageData.label.all;
        // this.filterData.year = currentYear
        // this.filterData.month = currentMonth
        this.filterData.year = 0
        this.filterData.month = 0
        this.missionListing()
        this.getDashboardData(this.filterData, 'dashboad')
        window.addEventListener('resize', () => {
            this.xvalues = [0]
            this.max = 0
            // this.getDashboardData(this.filterData);
            if (screen.width < 576) {
                this.goalHourPart = 5
            } else {
                this.goalHourPart = 10
            }
            if (this.totalGoalHours != 0) {
                let axes = (this.totalGoalHours / this.goalHourPart);
                this.max = Math.ceil(axes)
                let xValue = 0;

                for (var i = 0; i < this.goalHourPart; i++) {
                    xValue = (i + 1) * 10;
                    if (this.goalHourPart == 5) {
                        xValue = (i + 1) * 20;
                    }
                    this.xvalues.push(xValue)
                }
            }
        })
    }
};
</script>
