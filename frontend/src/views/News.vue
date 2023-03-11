<template>
	<div class="inner-pages news-page lists-page">
		<header>
			<ThePrimaryHeader></ThePrimaryHeader>
		</header>
		<main>
			<b-container>

				<div class="banner-wrap">
					<div :style="{backgroundImage: 'url('+bannerUrl+')'}" class="banner-section">
						<b-container>
							<h1>{{languageData.label.news}}</h1>
							<p v-html="bannerText"></p>
						</b-container>
					</div>
				</div>

				<div class="news-detail-container" v-if="showErrorDiv">
					<b-alert show variant="danger" dismissible v-model="showErrorDiv">
						{{ message }}
					</b-alert>
				</div>
				<div v-if="!showErrorDiv && isPageLoaded">
					<div v-if="newsListing.length > 0">
						<NewsCard
								:newsListing="newsListing"
						/>
					</div>
					<div v-else class="text-center news-detail-container">
						<h2>{{languageData.label.news}} {{languageData.label.not_found}}</h2>
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
				</div>
			</b-container>
		</main>
		<footer>
			<TheSecondaryFooter></TheSecondaryFooter>
		</footer>
		<back-to-top bottom="50px" right="40px" :title="languageData.label.back_to_top">
			<i class="icon-wrap">
				<img class="img-normal" :src="$store.state.imagePath+'/assets/images/down-arrow.svg'" alt="Down Arrow" />
				<img class="img-rollover" :src="$store.state.imagePath+'/assets/images/down-arrow-black.svg'" alt="Down Arrow" />
			</i>
		</back-to-top>
	</div>
</template>

<script>
	import NewsCard from "../components/NewsCardView";
	import store from '../store';
	import constants from '../constant';
	import {
		newsListing,
	} from "../services/service";


	export default {
		components: {
			ThePrimaryHeader : () => import("../components/Layouts/ThePrimaryHeader"),
			TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
			NewsCard
		},
		data() {
			return {
				languageData : [],
				isNewsDisplay : true,
				showErrorDiv: false,
				isPageLoaded : false,
				message: null,
				newsListing : [],
				pagination : {
					"total": 0,
					"perPage": 1,
					"currentPage": 1,
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
				this.getNewsListing(page);
			},
			getNewsListing(currentPage) {
				newsListing(currentPage).then(response => {
					if(response.error == false) {
						this.newsListing = response.data
						this.pagination.currentPage = response.pagination.current_page
						this.pagination.total = response.pagination.total
						this.pagination.perPage = response.pagination.per_page
						this.pagination.currentPage = response.pagination.current_page
						this.pagination.totalPages = response.pagination.total_pages
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
			this.isNewsDisplay = this.settingEnabled(constants.NEWS_ENABLED);
			if(!this.isNewsDisplay) {
				this.$router.push('/home')
			}
			this.bannerUrl = store.state.newsBanner
			let bannerTextArray = JSON.parse(store.state.newsBannerText)
			if(bannerTextArray) {
				bannerTextArray.filter((data,index) => {
					if(data.lang == store.state.defaultLanguage.toLowerCase()) {
						this.bannerText = data.message
					}
				})
			}
			this.getNewsListing(this.pagination.currentPage);
		},
		destroyed() {}
	};
</script>