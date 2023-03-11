<template>
	<div class="signin-slider">
		<b-carousel id="carousel-1" :fade="slideEffect" :interval="slideInterval"
			:sliding-start="0" :sliding-end="1" :indicators="carouselItems.length !== 1 ? true : false"
			v-if="isDynamicCarsousetSet">
				<b-carousel-slide
					:no-wrap="wrap"
					v-for="item in carouselItems"
					:key="item.sort_order"
					:caption="getTitle(item.slider_detail)"
					:text="getDescription(item.slider_detail)"
					:img-src="item.url"
				>
				</b-carousel-slide>
		</b-carousel>

		<b-carousel id fade :interval="0" v-else>
			<b-carousel-slide :img-src="$store.state.imagePath+'/assets/images/sliderimg1.png'"></b-carousel-slide>
		</b-carousel>
	</div>

</template>

<script>
	import store from '../store';

	export default {
		name: "TheSlider",
		data() {
			return {
				carouselItems: [],
				isDynamicCarsousetSet: false,
				wrap: true,
				slideInterval : 2000,
				slideEffect : true
			};
		},
		created() {
			if (store.state.slider != null && JSON.parse(store.state.slider).length > 0) {
				this.carouselItems = JSON.parse(store.state.slider);
				this.isDynamicCarsousetSet = true
			}
			if(store.state.slideInterval != '') {
				this.slideInterval = store.state.slideInterval
			}
			let slideEffects = store.state.slideEffect
			if(slideEffects != '' && slideEffects != "fade") {
				this.slideEffect = false
			}
		},
		methods: {
			getTitle: (sliderDetail) => {
				if (typeof sliderDetail !== 'undefined') {
					let translations = JSON.parse(JSON.stringify(sliderDetail)).translations;
					//Fetch slider title by language
					if (translations) {
						let filteredObj = translations.filter( (item, i) => {
							if (item.lang === store.state.defaultLanguage.toLowerCase()) {
								return translations[i].slider_title;
							}
						});
						if (filteredObj.length > 0 && filteredObj[0].slider_title) {
							return filteredObj[0].slider_title;
						} else {
							let filtereObj = translations.filter((item, i) => {
								if (item.lang === store.state.defaultTenantLanguage.toLowerCase()) {
									return translations[i].slider_title;
								}
							});

							if (filtereObj.length > 0 && filtereObj[0].slider_title) {
								return filtereObj[0].slider_title;
							}
						}
					}
				}
			},
			getDescription: (sliderDetail) => {

				if (typeof sliderDetail !== 'undefined') {
					let translations = JSON.parse(JSON.stringify(sliderDetail)).translations;
					// Fetch slider description by language			
					if (translations) {
						let filteredObj = translations.filter( (item, i) => {
							if (item.lang === store.state.defaultLanguage.toLowerCase()) {
								return translations[i].slider_description;
							}
						});
						if (filteredObj.length > 0 && filteredObj[0].slider_description) {
							return filteredObj[0].slider_description;
						} else {
							let filtereObj = translations.filter( (item, i) => {
								if (item.lang === store.state.defaultTenantLanguage.toLowerCase()) {
									return translations[i].slider_description;
								}
							});

							if (filtereObj.length > 0 && filtereObj[0].slider_description) {
								return filtereObj[0].slider_description;
							}
						}
					}
				}
			}
		}

	};
</script>