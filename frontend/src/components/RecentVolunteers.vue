<template>
	<div v-bind:class="{ 
		'recent-volunteer-block': true,
		'no-volunteer' : noVolunteerFound,
		'hide-pagination' : hidePagination,
	}">
		<div v-bind:class="{
			'content-loader-wrap': true,
			'recent-loader': recentVolunterLoader,
		}">
			<div class="content-loader"></div>
		</div>
		<h2 class="title-with-border"><span>{{ languageData.label.recent_volunteers }} </span></h2>
		<div class="recent-details-block" v-if="volunteerList.length > 0">
			<b-list-group class="volunteers-list" :current-page="currentPage">
				<b-list-group-item v-for="(volunteer , v) in volunteerList" :key="v">
					<div class="list-item">
						<i class="user-profile-icon" :style="{backgroundImage: 'url(' + volunteer.avatar + ')'}">
						</i>
						<span>{{volunteer.first_name}} {{volunteer.last_name}}</span>
					</div>
				</b-list-group-item>
			</b-list-group>
			<div class="custom-pagination" v-if="rows > perPage">
				<b-pagination v-model="currentPage" :total-rows="rows" :per-page="perPage" @change="pageChange">
				</b-pagination>
				<span>
					{{((currentPage - 1 ) * perPage ) + 1}} - {{Math.min(perPage * currentPage , rows )}} of {{rows}}
					{{ languageData.label.recent_volunteers }}</span>
			</div>
		</div>
		<p v-else>
			{{ languageData.label.no_volunteers }}
		</p>
	</div>
</template>

<script>
	import {
		missionVolunteers
	} from "../services/service";
	import store from '../store';
	export default {
		name: "RecentVolunteers",
		components: {},
		props: [],
		data: function () {
			return {
				currentPage: 1,
				rows: 0,
				volunteerList: [],
				recentVolunterLoader: true,
				perPage: 9,
				noVolunteerFound: false,
				hidePagination: true,
				languageData: [],
			}
		},
		directives: {},
		computed: {

		},
		methods: {

			pageChange(page) {
				//Change pagination
				this.currentPage = page;
				this.getMissionVolunteers();
			},
			// missionVolunteers
			getMissionVolunteers() {
				let missionData = {
					mission_id: '',
					page: ''
				};
				missionData.mission_id = this.$route.params.misisonId;
				missionData.page = this.currentPage;
				if (missionData.mission_id) {
					this.recentVolunterLoader = true;
					missionVolunteers(missionData).then(response => {

						if (!response.error) {

							this.volunteerList = response.data;
							if (this.volunteerList.length <= 0) {
								this.noVolunteerFound = true;
							}

							if (response.pagination) {
								this.rows = response.pagination.total
								if (this.rows > 9) {
									this.hidePagination = false;
								}
							}
						}
						this.recentVolunterLoader = false;
					})
				}
			},
		},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			this.getMissionVolunteers();
		}
	};
</script>