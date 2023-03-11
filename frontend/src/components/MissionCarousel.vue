<template>
	<div>
		<div v-bind:class="{ 'content-loader-wrap': true, 'slider-loader': carouselLoader}">
			<div class="content-loader"></div>
		</div>
		<div v-if="isCarouselLoaded">
			<div class="thumb-slider" v-if="mediaCarouselList.length > 0">
				<div v-bind:class="{
				'gallery-top' : true,
				'default-img': deafultImage,
				'default-video': deafultVideo
				}">
					<div class="img-wrap inner-gallery-block">
						<img :src="mediaCarouselList[0].media_path">
					</div>
					<div class="video-wrap inner-gallery-block">
						<iframe id="video" width="560" height="315" :src="getEmbededPath(mediaCarouselList[0])"
								frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
								allowfullscreen>
						</iframe>
					</div>
				</div>
				<carousel :nav="true" :dots="false" :items="5" :loop="loop" :mouseDrag="false" class="gallery-thumbs"
						  :margin="8" :responsive="{0:{items:3},576:{items:4},1200:{items:5}}">
					<div class="thumbs-col" v-bind:class="{
				'video-block': media.media_type == 'mp4',
				'img-block': media.media_type != 'mp4'}" v-for="(media , v) in mediaCarouselList" :key="v">
						<img :src="getMediaPath(media)" v-bind:class="{'video-item': media.media_type == 'mp4'}"
							 :data-src="getEmbededPath(media)">
						<i v-if="media.media_type == 'mp4'" class="btn-play"></i>
					</div>
				</carousel>
			</div>
			<div class="thumb-slider" v-else>
				<div v-bind:class="{
					'gallery-top' : true,
					'default-img': true
					}">
					<div class="img-wrap inner-gallery-block">
						<img :src="getDefaultImage()">
					</div>

				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import {
		missionCarousel
	} from "../services/service";
	import carousel from 'vue-owl-carousel';
	import store from "../store";
	import constants from '../constant';
	export default {
		name: "MissionCarousel",
		components: {
			carousel
		},
		props: [],
		data: function () {
			return {
				mediaCarouselList: [],
				carouselLoader: true,
				deafultImage: true,
				deafultVideo: false,
				loop: true,
				defaultMediaPath: '',
				isCarouselLoaded : false
			}
		},
		directives: {},
		computed: {

		},
		methods: {
			getDefaultImage() {
				return store.state.imagePath+'/assets/images/'+constants.MISSION_DEFAULT_PLACEHOLDER;
			},

			getMediaPath(media) {
				if (media.media_type == 'mp4') {
					let videoPath = media.media_path;
					let videoId = '';
					let regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
					let match = videoPath.match(regExp);

					if (match && match[2].length == 11) {
						videoId = match[2];
					}
					return "https://img.youtube.com/vi/" + videoId + "/mqdefault.jpg";
				} else {
					return media.media_path;
				}
			},

			getEmbededPath(media) {
				if (media.media_type == 'mp4') {
					let videoPath = media.media_path;
					let videoId = '';
					let regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
					let match = videoPath.match(regExp);

					if (match && match[2].length == 11) {
						videoId = match[2];
					}

					return "https://www.youtube.com/embed/" + videoId;
				} else {
					return media.media_path;
				}
			},

			handleSliderClick(event) {
				event.stopPropagation()
				let hideVideo = document.querySelector(".video-wrap");
				let galleryImg = document.querySelector(".gallery-top .img-wrap");
				let galleryImgSrc = document.querySelector(".gallery-top .img-wrap img");
				let videoSrc = document.querySelector(".video-wrap iframe");
				let dataSrc = event.target.getAttribute('data-src');
				if (event.target.classList.contains("video-item")) {
					videoSrc.src = dataSrc
					hideVideo.style.display = "block";
					galleryImg.style.display = "none";
				} else if (event.target.classList.contains("btn-play")) {
					let parentBtn = event.target.parentNode;
					let siblingBtn = parentBtn.childNodes;
					hideVideo.style.display = "block";
					galleryImg.style.display = "none";
					videoSrc.src = siblingBtn[0].getAttribute('data-src')
				} else {
					let iframe = document.querySelector('iframe');
					let iframeSrc = iframe.src;
					iframe.src = iframeSrc;
					galleryImgSrc.src = event.target.src;
					galleryImg.style.display = "block";
					hideVideo.style.display = "none";
				}
			},
		},
		created() {

			if (this.$route.params.misisonId) {
				missionCarousel(this.$route.params.misisonId).then(response => {
					this.carouselLoader = true;
					if (!response.error) {
						this.mediaCarouselList = response.data;
						if (response.data.length <= 5) {
							this.loop = false
						}
						this.carouselLoader = false;
						if (this.mediaCarouselList && this.mediaCarouselList[0]) {
							if (this.mediaCarouselList[0].media_type == "mp4") {
								this.deafultVideo = true;
								this.deafultImage = false;
							}
							this.defaultMediaPath = this.getMediaPath(this.mediaCarouselList[0]);
						}
						this.$emit("defaultMediaPathDetail", this.defaultMediaPath);
					}
					this.isCarouselLoaded = true
				})
			}

			setTimeout(() => {
				let thumbImg = document.querySelectorAll(
						".gallery-thumbs .owl-item img, .gallery-thumbs .owl-item .btn-play");
				thumbImg.forEach((itemEvent) => {
					itemEvent.addEventListener("click", this.handleSliderClick);
				});

			}, 1000);
			window.addEventListener('resize', () =>  {
				setTimeout(() => {
					let thumbImg = document.querySelectorAll(
							".gallery-thumbs .owl-item img, .gallery-thumbs .owl-item .btn-play");
					thumbImg.forEach((itemEvent) => {
						itemEvent.removeEventListener("click", this.handleSliderClick);
						itemEvent.addEventListener("click", this.handleSliderClick);
					});
				}, 2000);
			});
		}
	};
</script>