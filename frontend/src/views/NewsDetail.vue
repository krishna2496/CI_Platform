<template>
	<div class="news-detail-page inner-pages">
		<header>
			<ThePrimaryHeader></ThePrimaryHeader>
		</header>
		<main>

			<b-container>
				<div class="news-detail-container">
					<div class="news-detail-block" v-if="isContentLoaded">
						<h2><p v-if="newsDetailList.news_content">{{newsDetailList.news_content.title}}</p>
							<b-badge class="status-label" v-if="newsDetailList.news_category[0]">{{newsDetailList.news_category[0]}}</b-badge>
						</h2>
						<h3 class="author-name">
							<i v-if="newsDetailList.user_thumbnail && newsDetailList.user_thumbnail != ''" :style="{backgroundImage: 'url('+newsDetailList.user_thumbnail+')'}"></i>
							<span v-if="newsDetailList.user_name">{{newsDetailList.user_name}}</span> <span v-if="newsDetailList.user_name && newsDetailList.user_title"> - </span> <span v-if="newsDetailList.user_title">{{newsDetailList.user_title}}</span></h3>
						<p class="publish-date" v-if="newsDetailList.published_on != null">{{langauageData.label.published_on}} {{newsDetailList.published_on | formatDate}}</p>
						<div class="news-img-wrap" :style="{backgroundImage: 'url('+newsDetailList.news_image+')'}" v-if="newsDetailList.news_image"></div>
						<div
								v-if="newsDetailList.news_content"
								class="news-content cms-content"
								v-bind:class="{'news-img-wrap' : !newsDetailList.news_image}"
								v-html="newsDetailList.news_content.description">
						</div>
					</div>

				</div>
			</b-container>
		</main>
		<footer>
			<TheSecondaryFooter></TheSecondaryFooter>
		</footer>
	</div>
</template>
<script>
	import constants from '../constant';
	import store from '../store';
	import {
		newsDetail,
	} from "../services/service";
	export default {
		components: {
			ThePrimaryHeader : () => import("../components/Layouts/ThePrimaryHeader"),
			TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
		},
		data() {
			return {
				isNewsDisplay : true,
				isContentLoaded : false,
				newsDetailList : [],
				langauageData : [],
				newsId : this.$route.params.newsId
			};
		},
		mounted() {},
		computed: {},
		methods: {
			getNewsDetail() {
				newsDetail(this.newsId).then(response => {
					if(response.error == false) {
						this.newsDetailList = response.data
						this.isContentLoaded = true
					} else {
						this.$router.push('/404');
					}
				})
			}
		},
		created() {
			this.langauageData = JSON.parse(store.state.languageLabel);
			this.isNewsDisplay = this.settingEnabled(constants.NEWS_ENABLED);
			if(!this.isNewsDisplay) {
				this.$router.push('/home')
			}
			this.getNewsDetail();
		},
		updated() {}
	};
</script>


