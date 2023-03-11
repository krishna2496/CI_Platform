<template>
    <div class="stories-detail-page inner-pages">
        <header>
            <ThePrimaryHeader></ThePrimaryHeader>
        </header>
        <main>
            <b-container v-if="storyDetailList != null">
                <div class="slider-banner-block">
                    <b-row>
                        <b-col xl="9" lg="8" class="slider-col">
                            <div class="title-block">
                                <h1>{{storyDetailList.title}}</h1>
                                <div class="view-tag">
                                    <i>
                                        <img :src="$store.state.imagePath+'/assets/images/eye-ic.svg'" alt="Eye Icon" />
                                    </i>
                                    <span
                                            v-if="storyDetailList.story_visitor_count > 1">{{storyDetailList.story_visitor_count}}
                                        {{languageData.label.views}}</span>
                                    <span v-else>{{storyDetailList.story_visitor_count}}
                                        {{languageData.label.view}}</span>
                                </div>
                            </div>
                            <b-row class="thumb-slider"
                                   v-if="storyDetailList.storyMedia && storyDetailList.storyMedia.length > 0">
                                <b-col :xl="columnWidth" class="left-col">
                                    <div class="gallery-top" v-bind:class="{
											'gallery-top' : true,
											'default-img': storyDetailList.storyMedia[0].type != 'video',
											'default-video': storyDetailList.storyMedia[0].type == 'video'
										}">
                                        <div class="img-wrap inner-gallery-block">
                                            <img :src="storyDetailList.storyMedia[0].path" />
                                        </div>
                                        <div class="video-wrap inner-gallery-block">
                                            <iframe id="video" width="560" height="315"
                                                    :src="getEmbededPath(storyDetailList.storyMedia[0])" frameborder="0"
                                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                        </div>
                                    </div>
                                </b-col>
                                <b-col xl="2" class="right-col"
                                       v-if="storyDetailList.storyMedia && storyDetailList.storyMedia.length > 1">
                                    <slick ref="slick" :options="slickOptions" class="gallery-thumbs">

                                        <div v-for="(media , v) in storyDetailList.storyMedia" :key="v" v-bind:class="{
													'img-block': media.type != 'video',
												'video-block': media.type == 'video',
												'thumbs-col': true
												}">
                                            <img :src="getMediaPath(media)" :data-src="getEmbededPath(media)"
                                                 v-bind:class="{'video-item': media.type == 'video'}" />
                                            <i v-if="media.type == 'video'" class="btn-play"></i>
                                        </div>

                                    </slick>
                                </b-col>
                            </b-row>
                            <b-row class="thumb-slider" v-else>
                                <b-col xl="12" class="left-col">
                                    <div class="gallery-top" v-bind:class="{
											'gallery-top' : true,
											'default-img': true
										
										}">
                                        <div class="img-wrap inner-gallery-block">
                                            <img :src="getDefaultImage()" />
                                        </div>
                                    </div>
                                </b-col>
                            </b-row>
                        </b-col>
                        <b-col xl="3" lg="4" class="ml-auto profile-box-outer">
                            <div class="profile-box"
                                 v-bind:class="{
                                    'blank-profile-content' : (storyDetailList.why_i_volunteer == '' || storyDetailList.why_i_volunteer == null) ? true : false
                                }"
                            >
                                <div class="user-profile">
                                    <i class="user-profile-icon"
                                       :style="{backgroundImage: 'url(' + storyDetailList.avatar + ')'}"></i>
                                    <h4>{{storyDetailList.first_name}} {{storyDetailList.last_name}}</h4>
                                    <p>{{ storyDetailList.city.name === '' ? '' : storyDetailList.city.name + ',' }} {{storyDetailList.country.name}}</p>
                                    <div class="social-nav"  v-if="storyDetailList.linked_in_url != null && storyDetailList.linked_in_url != ''  ">
                                        <b-link :href="storyDetailList.linked_in_url" target="_blank"
                                                :title="languageData.label.linked_in" class="linkedin-link">
                                            <img :src="$store.state.imagePath+'/assets/images/linkedin-ic-blue.svg'"
                                                 class="normal-img" alt="linkedin img" />
                                            <img :src="$store.state.imagePath+'/assets/images/linkedin-ic.svg'"
                                                 class="hover-img" alt="linkedin img" />
                                        </b-link>
                                    </div>
                                </div>

                                <div class="profile-content" v-if="storyDetailList.why_i_volunteer != '' && storyDetailList.why_i_volunteer != null">
                                    <p>{{storyDetailList.why_i_volunteer}}</p>
                                </div>
                            </div>
                        </b-col>
                    </b-row>
                </div>
                <div class="story-content-wrap">
                  <div class="story-content cms-content" v-html="storyDetailList.description"></div>
                    <div class="btn-wrap group-btns">
                        <b-button class="btn-borderprimary icon-btn" @click="handleModal()">
                            <i>
                                <svg height="512pt" viewBox="0 0 512 512" width="512pt"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                            d="m512 428h-84v84h-40v-84h-84v-40h84v-84h40v84h84zm-212.695312-204.5625c1.757812 7.910156 2.695312 16.128906 2.695312 24.5625 0 34.550781-15.59375 65.527344-40.105469 86.269531.699219.277344 1.40625.546875 2.105469.832031v44.199219c-21.414062-11.667969-45.945312-18.300781-72-18.300781v-.039062c-.332031.007812-.667969.007812-1 .015624v.023438c-83.261719 0-151 67.738281-151 151h-40c0-79.371094 48.671875-147.582031 117.730469-176.378906-25.449219-20.734375-41.730469-52.3125-41.730469-87.621094 0-62.308594 50.691406-113 113-113 7.40625 0 14.644531.722656 21.65625 2.089844-1.734375-7.84375-2.65625-15.988282-2.65625-24.34375 0-62.167969 50.578125-112.746094 112.746094-112.746094 62.167968 0 112.746094 50.578125 112.746094 112.746094 0 34.894531-15.9375 66.136718-40.910157 86.832031 33.011719 13.109375 61.464844 35.117187 82.304688 63.421875h-53.847657c-24.847656-22.023438-56.976562-36-92.273437-37.796875-2.652344.1875-5.324219.289063-8.019531.289063-7.332032 0-14.5-.710938-21.441406-2.054688zm-51.304688-110.691406c0 40.113281 32.632812 72.746094 72.746094 72.746094 40.109375 0 72.746094-32.632813 72.746094-72.746094 0-40.113282-32.636719-72.746094-72.746094-72.746094-40.113282 0-72.746094 32.632812-72.746094 72.746094zm14 135.253906c0-40.253906-32.746094-73-73-73s-73 32.746094-73 73 32.746094 73 73 73 73-32.746094 73-73zm0 0" />
                                </svg>
                            </i>
                            <span>{{languageData.label.recommend_to_co_worker}}</span>
                        </b-button>
                        <b-link :to="{ path: '/mission-detail/'+storyDetailList.mission_id}"
                                v-if="storyDetailList.mission_id != '' && storyDetailList.open_mission_button == 1" class="btn-bordersecondary icon-btn btn">
                            <span>{{languageData.label.open_mission}}</span>
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
                    </div>
                </div>
              <invite-co-worker ref="userDetailModal" entity-type="STORY" :entity-id="storyId"></invite-co-worker>
            </b-container>
        </main>
        <footer>
            <TheSecondaryFooter></TheSecondaryFooter>
        </footer>
    </div>
</template>
<script>
  import Slick from "vue-slick";
  import store from '../store';
  import constants from '../constant';
  import { storyDetail } from "../services/service";
  import InviteCoWorker from "@/components/InviteCoWorker";
  export default {
    components: {
      ThePrimaryHeader: () => import("../components/Layouts/ThePrimaryHeader"),
      TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
      Slick,
      InviteCoWorker
    },
    name: "StoryDetail",
    data() {
      return {
        storyId: parseInt(this.$route.params.storyId),
        isStoryDisplay: true,
        languageData: [],
        sliderToShow: false,
        slickOptions: {
          autoplay: false,
          arrows: true,
          dots: false,
          slidesToShow: 4,
          slidesToScroll: 1,
          centerPadding: "10px",
          infinite: true,
          accesibility: true,
          draggable: true,
          swipe: true,
          touchMove: true,
          vertical: true,
          useTransform: true,
          adaptiveHeight: true,
          responsive: [{
            breakpoint: 1200,
            settings: {
              vertical: false,
            }
          },
            {
              breakpoint: 576,
              settings: {
                slidesToShow: 3,
                vertical: false,
              }
            }
          ]

        },
        storyDetailList: null,
        columnWidth: 10
      };
    },
    methods: {
      handleSliderClick(event) {
        event.stopPropagation();
        let hideVideo = document.querySelector(".video-wrap");
        let galleryImg = document.querySelector(".gallery-top .img-wrap");
        let galleryImgSrc = document.querySelector(".gallery-top .img-wrap img");
        let videoSrc = document.querySelector(".video-wrap iframe");
        let dataSrc = event.target.getAttribute("data-src");
        if (event.target.classList.contains("video-item")) {
          videoSrc.src = dataSrc;
          hideVideo.style.display = "block";
          galleryImg.style.display = "none";
        } else if (event.target.classList.contains("btn-play")) {
          let parentBtn = event.target.parentNode;
          let siblingBtn = parentBtn.childNodes;
          hideVideo.style.display = "block";
          galleryImg.style.display = "none";
          videoSrc.src = siblingBtn[0].getAttribute("data-src");
        } else {
          galleryImgSrc.src = event.target.src;
          galleryImg.style.display = "block";
          hideVideo.style.display = "none";
        }
      },

      getMediaPath(media) {
        if (media.type == 'video') {
          let videoPath = media.path;
          let videoId = '';
          let regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
          let match = videoPath.match(regExp);

          if (match && match[2].length == 11) {
            videoId = match[2];
          }
          return "https://img.youtube.com/vi/" + videoId + "/mqdefault.jpg";
        } else {
          return media.path;
        }
      },

      getEmbededPath(media) {
        if (media.type == 'video') {
          let videoPath = media.path;
          let videoId = '';
          let regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
          let match = videoPath.match(regExp);

          if (match && match[2].length == 11) {
            videoId = match[2];
          }

          return "https://www.youtube.com/embed/" + videoId;
        } else {
          return media.path;
        }
      },

      getStoryDetail() {
        storyDetail(this.storyId).then(response => {
          if (response.error == false) {
            let mediaType = []
            this.storyDetailList = response.data
            this.storyDetailList.description = this.$sanitize(this.storyDetailList.description)
            let newMediaType = response.data.storyMedia
            if (newMediaType) {
              newMediaType.filter((data, index) => {
                if (data.type == 'video') {
                  let path = data.path.split(',')
                  path.filter((pathData) => {
                    mediaType.push({
                      'path': pathData,
                      'story_id': data.story_id,
                      'story_media_id': data.story_media_id,
                      'type': data.type
                    })
                  })
                } else {
                  mediaType.push(data)
                }
              })
            }
            if (this.storyDetailList.storyMedia.length < 2) {
              this.columnWidth = 12
            }

            this.storyDetailList.storyMedia = mediaType
            this.isContentLoaded = true

            setTimeout(() => {
              this.sliderToShow = true
            }, 200)
          } else {
            this.$router.push('/404');
          }
        })
      },

      handleModal() {
        this.$refs.userDetailModal.show();
      },

      getDefaultImage() {
        return store.state.imagePath + '/assets/images/' + constants.MISSION_DEFAULT_PLACEHOLDER;
      }
    },
    created() {
      this.languageData = JSON.parse(store.state.languageLabel);
      this.isStoryDisplay = this.settingEnabled(constants.STORIES_ENABLED);
      if (!this.isStoryDisplay) {
        this.$router.push('/home')
      }
      this.getStoryDetail();
      setTimeout(() => {
        let thumbImg = document.querySelectorAll(
          ".gallery-thumbs .slick-slide img, .gallery-thumbs .slick-slide .btn-play"
        );
        thumbImg.forEach((itemEvent) => {
          itemEvent.removeEventListener("click", this.handleSliderClick);
          itemEvent.addEventListener("click", this.handleSliderClick);
        });
      }, 3000);
      window.addEventListener("resize", () => {
        setTimeout(() => {
          let thumbImg = document.querySelectorAll(
            ".gallery-thumbs .slick-slide img, .gallery-thumbs .slick-slide .btn-play"
          );
          thumbImg.forEach((itemEvent) => {
            itemEvent.removeEventListener("click", this.handleSliderClick);
            itemEvent.addEventListener("click", this.handleSliderClick);
          });
        }, 2000);
      });
    }
  };

</script>
