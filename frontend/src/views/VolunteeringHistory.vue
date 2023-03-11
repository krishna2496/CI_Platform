<template>
	<div class="dashboard-history inner-pages">
		<header>
			<TopHeader></TopHeader>
		</header>
		<main>
			<DashboardBreadcrumb />
			<div class="dashboard-tab-content" v-if="!isLoading">
				<b-container>
					<div class="heading-section" v-if="isAllVisible && !isLoading">
						<h1>{{languageData.label.volunteering_history}}</h1>
					</div>
					<div class="inner-content-wrap" v-if="isAllVisible && !isLoading">
						<b-row class="chart-block" v-if="(isThemeDisplay || isSkillDisplay) && isTimeMissionActive">
							<b-col lg="6" class="chart-col" v-if="isThemeDisplay">
								<div class="inner-chart-col">
									<div class="chart-title">
										<h5>{{languageData.label.hours_per_theme}}</h5>
										<AppCustomDropdown :optionList="themeYearList" @updateCall="updateThemeYear"
														   :defaultText="ThemeYearText" translationEnable="false" />
									</div>
									<div
											v-bind:class="{ 'content-loader-wrap': true, 'loader-active': updatingThemeYear}">
										<div class="content-loader"></div>
									</div>
									<div class="line-chart" v-if="perHourApiDataTheme.length && !updatingThemeYear">
										<horizontal-chart :labels="getThemeLabels" :data="getThemeValue">
										</horizontal-chart>
									</div>
									<div v-if="perHourApiDataTheme.length == 0 && !updatingThemeYear" class="text-center">
										<h5>{{perHourDataNotFoundForTheme}}</h5>
									</div>
								</div>
							</b-col>
							<b-col lg="6" class="chart-col" v-if="isSkillDisplay">
								<div class="inner-chart-col">
									<div class="chart-title">
										<h5>{{languageData.label.hours_per_skill}}</h5>
										<AppCustomDropdown :optionList="skillYearList" @updateCall="updateSkillYear"
														   :defaultText="skillYearText" translationEnable="false" />
									</div>
									<div
											v-bind:class="{ 'content-loader-wrap': true, 'loader-active': updatingSkillYear}">
										<div class="content-loader"></div>
									</div>
									<div class="line-chart" v-if="perHourApiDataSkill.length && !updatingSkillYear">
										<horizontal-chart :labels="getSkillLabels" :data="getSkillValue">
										</horizontal-chart>
									</div>
									<div v-if="perHourApiDataSkill.length == 0 && !updatingSkillYear" class="text-center">
										<h5>{{perHourDataNotFoundForSkill}}</h5>
									</div>
								</div>
							</b-col>
						</b-row>
						<b-row class="dashboard-table">
							<b-col lg="6" class="table-col" v-if="isTimeMissionActive">
								<VolunteeringRequest :headerField="timeMissionTimesheetFields"
													 :items="timeMissionTimesheetItems" :headerLable="timeMissionTimesheetLabel"
													 :currentPage="timeMissionCurrentPage" :totalRow="timeMissionTotalRow"
													 @updateCall="getVolunteerMissionsHours"
													 exportUrl="app/volunteer/history/time-mission/export" :perPage="hourRequestPerPage"
													 :nextUrl="hourRequestNextUrl"
													 :fileName="languageData.export_timesheet_file_names.TIME_MISSION_HISTORY_XLSX"
													 :totalPages="timeMissionTotalPage"
													 requestType="time"
								/>
							</b-col>
							<b-col lg="6" class="table-col"  v-if="isGoalMissionActive">
								<VolunteeringRequest :headerField="goalMissionTimesheetFields"
									:items="goalMissionTimesheetItems" :headerLable="goalMissionTimesheetLabel"
									:currentPage="goalMissionCurrentPage" :totalRow="goalMissionTotalRow"
									:perPage="goalRequestPerPage" :nextUrl="goalRequestNextUrl"
									@updateCall="getVolunteerMissionsGoals"
									exportUrl="app/volunteer/history/goal-mission/export"
									:fileName="languageData.export_timesheet_file_names.GOAL_MISSION_HISTORY_XLSX"
									:totalPages="goalMissionTotalPage" requestType="goal"/>
							</b-col>
						</b-row>
					</div>
					<div class="no-history-data" v-else>
						<p>{{languageData.label.empty_volunteer_history_text}}</p>
						<div class="btn-row">
							<b-button :title="languageData.label.start_volunteering" class="btn-bordersecondary"
									  @click="$router.push({ name: 'home' })">{{languageData.label.start_volunteering}}
							</b-button>
						</div>
					</div>
				</b-container>
			</div>
			<div v-else
				 v-bind:class="{ 'content-loader-wrap': true, 'loader-active': isLoading}">
				<div class="content-loader"></div>
			</div>
		</main>
		<footer>
			<PrimaryFooter></PrimaryFooter>
		</footer>
	</div>
</template>

<script>
	import TopHeader from "../components/Layouts/ThePrimaryHeader";
	import PrimaryFooter from "../components/Layouts/TheSecondaryFooter";
	import AppCustomDropdown from "../components/AppCustomDropdown";
	import DashboardBreadcrumb from "../components/DashboardBreadcrumb";
	import HorizontalChart from "../components/HorizontalChart";
	import VolunteerHistoryHours from "../services/VolunteerHistory/VolunteerHistoryHours";
	import VolunteerMissionHours from "../services/VolunteerHistory/VolunteerMissionHours";
	import VolunteerMissionGoals from "../services/VolunteerHistory/VolunteerMissionGoals";
	import VolunteeringRequest from "../components/VolunteeringRequest";
	import store from "../store";
	import constants from '../constant';

	export default {
		components: {
			TopHeader,
			PrimaryFooter,
			AppCustomDropdown,
			DashboardBreadcrumb,
			HorizontalChart,
			VolunteeringRequest
		},

		name: "dashboardhistory",

		data() {
			return {
				languageData: [],
				perHourApiDataTheme: [],
				perHourApiDataSkill: [],
				timeMissionTimesheetLabel: "",
				timeMissionTimesheetFields: [],
				timeMissionTimesheetItems: [],
				timeMissionCurrentPage: 1,
				timeMissionTotalRow: 0,
				timeMissionTotalPage: null,
				goalMissionTimesheetLabel: "",
				goalMissionTimesheetFields: [],
				goalMissionTimesheetItems: [],
				goalMissionCurrentPage: 1,
				goalMissionTotalRow: 0,
				goalMissionTotalPage: null,
				ThemeYearText: "Year",
				skillYearText: "Year",
				skillYearList: [],
				themeYearList: [],
				updatingThemeYear: false,
				updatingSkillYear: false,
				hourRequestPerPage: 5,
				goalRequestPerPage: 5,
				hourRequestNextUrl: null,
				goalRequestNextUrl: null,
				perHourDataNotFoundForTheme: null,
				perHourDataNotFoundForSkill: null,
				isLoading: true,
				isThemeDisplay: true,
				isSkillDisplay: true,
				isGoalMissionActive : false,
        		isTimeMissionActive : false
			};
		},
		mounted() {
			let currentYear = new Date().getFullYear();
			let yearsList = [];
			yearsList.push([0, this.languageData.label.all]);
			let yearDiff = 5;
			if (store.state.timesheetFromYear && store.state.timesheetFromYear != '') {
				let lastYear = store.state.timesheetFromYear;
				if ((currentYear - lastYear) + 1 > 0) {
					yearDiff = (currentYear - lastYear) + 1;
				}
			}
			for (let index = currentYear; index > (currentYear - yearDiff); index--) {
				yearsList.push([index, index]);
			}
			this.skillYearList = yearsList;
			this.themeYearList = yearsList;
		},
		methods: {
			updateThemeYear(value) {
				this.ThemeYearText = value.selectedVal;
				this.updatingThemeYear = true;
				this.getVolunteerHistoryHoursOfType("theme", this.ThemeYearText);
			},
			updateSkillYear(value) {
				this.skillYearText = value.selectedVal;
				this.updatingSkillYear = true;
				this.getVolunteerHistoryHoursOfType("skill", this.skillYearText);
			},
			getVolunteerHistoryHoursOfType(type = "theme", year = "") {
				if(year ==  this.languageData.label.all) {
					year = '';
				}
				VolunteerHistoryHours(type, year).then(response => {
					let typeName =
							"perHourApiData" + type.charAt(0).toUpperCase() + type.slice(1);
					let perHourDataNotFoundForType = "perHourDataNotFoundFor" + type.charAt(0).toUpperCase() + type.slice(1);
					if (typeof response.data !== "undefined") {
						this[typeName] = Object.values(response.data);
					} else {
						this[typeName] = [];
						this[perHourDataNotFoundForType] = response.message
					}
					this.updatingThemeYear = false;
					this.updatingSkillYear = false;
				});
			},
			getVolunteerMissionsHours(currentPage) {
				VolunteerMissionHours(currentPage).then(response => {
					this.timeMissionTimesheetItems = [];
					if (response && response.data) {
						let data = response.data;
						let mission = this.languageData.label.mission;
						let time = this.languageData.label.time;
						let hours = this.languageData.label.hours;
						let organisation = this.languageData.label.organisation;

						if (response.pagination) {
							this.timeMissionTotalRow = response.pagination.total;
							this.timeMissionCurrentPage = response.pagination.current_page
							this.hourRequestPerPage = response.pagination.per_page;
							this.hourRequestNextUrl = response.pagination.next_url;
							this.timeMissionTotalPage = response.pagination.total_pages;
						}

						data.filter( (item) => {
							this.timeMissionTimesheetItems.push({
								['mission']: item.title,
								['time']: item.time,
								['hours']: item.hours,
								['organisation']: item.organization_name,
								['mission_id']: item.mission_id
							})
						})
					}

					if (!this.isGoalMissionActive) {
						this.isLoading = false;
					}
				})
			},
			getVolunteerMissionsGoals(currentPage) {
				VolunteerMissionGoals(currentPage).then(response => {
					this.goalMissionTimesheetItems = [];
					if (response && response.data) {
						let data = response.data;
						let mission = this.languageData.label.mission;
						let action = this.languageData.label.actions;
						let organisation = this.languageData.label.organisation;
						if (response.pagination) {
							this.goalMissionTotalRow = response.pagination.total;
							this.goalMissionCurrentPage = response.pagination.current_page;
							this.goalRequestPerPage = response.pagination.per_page;
							this.goalRequestNextUrl = response.pagination.next_url;
							this.goalMissionTotalPage = response.pagination.total_pages;
						}

						data.filter( (item) => {
							this.goalMissionTimesheetItems.push({
								['mission']: item.title,
								['action']: item.action,
								['organisation']: item.organization_name,
								['mission_id']: item.mission_id
							})
						})
					}
					this.isLoading = false;
				})
			}
		},
		created() {

			this.languageData = JSON.parse(store.state.languageLabel);
			this.timeMissionTimesheetLabel = this.languageData.label.volunteering_hours
			this.goalMissionTimesheetLabel = this.languageData.label.volunteering_goals
			this.ThemeYearText = this.languageData.label.all
			this.skillYearText = this.languageData.label.all
			this.isThemeDisplay = this.settingEnabled(constants.THEMES_ENABLED);
			this.isSkillDisplay = this.settingEnabled(constants.SKILLS_ENABLED);
			this.isGoalMissionActive = this.settingEnabled(constants.VOLUNTEERING_GOAL_MISSION),
			this.isTimeMissionActive = this.settingEnabled(constants.VOLUNTEERING_TIME_MISSION)

			if (this.isTimeMissionActive) {
				this.getVolunteerHistoryHoursOfType('theme');
				this.getVolunteerHistoryHoursOfType('skill');
				this.getVolunteerMissionsHours();
			}

			if (this.isGoalMissionActive) {
				this.getVolunteerMissionsGoals();
			}

			let timeRequestFieldArray = [
				this.languageData.label.mission,
				this.languageData.label.time,
				this.languageData.label.hours,
				this.languageData.label.organisation,
			]

			timeRequestFieldArray.filter( (data) => {
				this.timeMissionTimesheetFields.push({
					"key": data
				})
			});

			let goalRequestFieldArray = [
				this.languageData.label.mission,
				this.languageData.label.actions,
				this.languageData.label.organisation,
			]

			goalRequestFieldArray.filter((data) => {
				this.goalMissionTimesheetFields.push({
					"key": data
				})
			});
		},
		computed: {
			getThemeLabels: {
				get: function () {
					let labelArray = [];
					if (this.perHourApiDataTheme.length > 0) {
						this.perHourApiDataTheme.map(function (data) {
							labelArray.push(data.theme_name);
						});
					} else {
						return labelArray;
					}
					return labelArray;
				}
			},
			getThemeValue: {
				get: function () {
					let valueArray = [];
					if (this.perHourApiDataTheme.length > 0) {
						this.perHourApiDataTheme.map(function (data) {
							valueArray.push((data.total_minutes / 60).toFixed(2));
						});
					} else {
						return valueArray;
					}
					return valueArray;
				}
			},
			getSkillLabels: {
				get: function () {
					let labelArray = [];
					if (this.perHourApiDataSkill.length > 0) {
						this.perHourApiDataSkill.map(function (data) {
							labelArray.push(data.skill_name);
						});
					} else {
						return labelArray;
					}
					return labelArray;
				}
			},
			getSkillValue: {
				get: function () {
					let valueArray = [];
					if (this.perHourApiDataSkill.length > 0) {
						this.perHourApiDataSkill.map(function (data) {
							valueArray.push((data.total_minutes / 60).toFixed(2));
						});
					} else {
						return valueArray;
					}
					return valueArray;
				}
			},
			isAllVisible: {
				get: function () {
					if (this.perHourApiDataTheme.length == 0 && this.perHourApiDataSkill.length == 0 && this
							.timeMissionTimesheetItems.length == 0 && this.goalMissionTimesheetItems.length == 0) {
						return false;
					}
					return true;
				}
			}
		}
	};
</script>