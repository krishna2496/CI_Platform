<template>
	<div class="inner-pages storie-page lists-page">
		<header>
			<ThePrimaryHeader></ThePrimaryHeader>
		</header>
		<main>

			<b-container>
				<StoryBanner/>
				<div class="news-detail-container" v-if="showErrorDiv">
					<b-alert show variant="danger" dismissible v-model="showErrorDiv">
						{{ message }}
					</b-alert>
				</div>
				<div v-if="!showErrorDiv && isPageLoaded">
					<div v-if="storyListing.length > 0">
						<StoriesCard :storyListing="storyListing"/>
					</div>
					<div v-else class="cards-wrapper text-center">
						<h2>{{languageData.label.stories}} {{languageData.label.not_found}}</h2>
					</div>
				</div>

				<div class="pagination-block" data-aos="fade-up" v-if="pagination.totalPages > 1">
					<b-pagination
							:hide-ellipsis="hideEllipsis"
							v-model="pagination.currentPage"
							:total-rows="pagination.total"
							:per-page="pagination.perPage"
							align="center"
							@change="pageChange"
							aria-controls="my-cardlist"
					></b-pagination>
				</div>
			</b-container>
		</main>
		<footer>
			<TheSecondaryFooter></TheSecondaryFooter>
		</footer>
		<back-to-top bottom="50px" right="40px" :title="languageData.label.back_to_top">
			<i class="icon-wrap">
				<img class="img-normal" :src="$store.state.imagePath+'/assets/images/down-arrow.svg'"
					 alt="Down Arrow" />
				<img class="img-rollover" :src="$store.state.imagePath+'/assets/images/down-arrow-black.svg'"
					 alt="Down Arrow" />
			</i>
		</back-to-top>
	</div>
</template>
<script>
	import StoryBanner from "../components/StoryBanner";
	import StoriesCard from "../components/StoriesCard";
	import store from '../store';
	import constants from '../constant';
	import {
		storyListing,
	} from "../services/service";

	export default {
		components: {
			ThePrimaryHeader: () => import("../components/Layouts/ThePrimaryHeader"),
			TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
			StoryBanner,
			StoriesCard
		},
		data() {
			return {
				languageData : [],
				isStoryDisplay : true,
				showErrorDiv: false,
				isPageLoaded : false,
				message: null,
				storyListing : [],
				pagination : {
					'currentPage' :1,
					"total": 0,
					"perPage": 1,
					"totalPages": 0,
				},
				bannerUrl : '',
				bannerText : '',
				hideEllipsis:true
			};
		},

		methods: {
			pageChange(page){
				setTimeout(() => {
					window.scrollTo({
						'behavior': 'smooth',
						'top': 0
					}, 0);
				});
				this.getStoryListing(page);
			},
			getStoryListing(currentPage) {
				storyListing(currentPage).then(response => {
					if(response.error == false) {
						this.storyListing = response.data
						if(response.pagination) {
							this.pagination.currentPage = response.pagination.current_page
							this.pagination.total = response.pagination.total
							this.pagination.perPage = response.pagination.per_page
							this.pagination.currentPage = response.pagination.current_page
							this.pagination.totalPages = response.pagination.total_pages
						}
					} else {
						this.showErrorDiv = true;
						this.message = response.message
					}
					this.isPageLoaded = true
				})
			}
		},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			this.isStoryDisplay = this.settingEnabled(constants.STORIES_ENABLED);
			if(!this.isStoryDisplay) {
				this.$router.push('/home')
			}
			this.getStoryListing(this.pagination.currentPage)
		},
		destroyed() {}
	};
</script>