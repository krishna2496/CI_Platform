<template>
	<div class="banner-wrap">
		<div :style="{backgroundImage: 'url('+bannerUrl+')'}" class="banner-section">
			<b-container>
				<p v-html="bannerText"></p>
				<b-link class="btn btn-secondary btn-borderwhite icon-btn"  to="/share-story">
					<span>{{languageData.label.share_your_story}}</span>
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
				</b-link>
			</b-container>
		</div>
	</div>
</template>

<script>
	import axios from "axios";
	import store from '../store';
	import constants from '../constant';
	export default {
		name: "BannerSection",
		props: [],
		components: {},
		data() {
			return {
				images: [],
				languageData : [],
				bannerUrl : '',
				bannerText : ''
			};
		},
		mounted() {},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			this.bannerUrl = store.state.storyBanner
			let bannerTextArray = JSON.parse(store.state.storyBannerText)
			if(bannerTextArray) {
				bannerTextArray.filter((data,index) => {
					if(data.lang == store.state.defaultLanguage.toLowerCase()) {
						this.bannerText = data.message
					}
				})
			}
		}
	};
</script>