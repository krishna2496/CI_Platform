<template>
    <div class="cards-wrapper">
        <div class="card-grid">
            <b-row>
                <b-col lg="4" sm="6" class="card-outer" data-aos="fade-up" v-for="(data,key) in storyListing" v-bind:key="key">
                    <b-card no-body>
                        <b-card-header>
                            <div class="header-img-block">
                                <b-link class="group-img" :style="{backgroundImage: 'url('+getMediaPath(data)+')'}"></b-link>
                            </div>
                            <div class="group-category" v-if="data.theme_name != ''">
                                <span class="category-text">{{data.theme_name}}</span>
                            </div>
                            <b-link class="btn btn-borderwhite icon-btn" :title="langauageData.label.view_detail" :to="'/story-detail/'+data.story_id">
                                <span>{{langauageData.label.view_detail}}</span>
                                <i>
                                    <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 16"
                                            width="19"
                                            height="15"
                                    >
                                        <g id="Main Content">
                                            <g id="1">
                                                <g id="Button">
                                                    <path
                                                            id="Forma 1 copy 12"
                                                            class="shp0"
                                                            d="M16.49,1.22c-0.31,-0.3 -0.83,-0.3 -1.16,0c-0.31,0.29 -0.31,0.77 0,1.06l5.88,5.44h-19.39c-0.45,0 -0.81,0.33 -0.81,0.75c0,0.42 0.36,0.76 0.81,0.76h19.39l-5.88,5.43c-0.31,0.3 -0.31,0.78 0,1.07c0.32,0.3 0.85,0.3 1.16,0l7.27,-6.73c0.32,-0.29 0.32,-0.77 0,-1.06z"
                                                    />
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                </i>
                            </b-link>
                        </b-card-header>
                        <b-card-body>
                            <div class="content-block">
                                <div class="top-block">
                                    <b-link
                                            :to="'/story-detail/'+data.story_id"
                                            :title="data.title"
                                            class="card-title mb-2"
                                            v-if="data.title"
                                    >{{data.title | substring(60)}}
                                    </b-link>
                                    <b-card-text v-if="data.description" v-html="getDescription(data.description)">
                                    </b-card-text>
                                </div>
                                <div class="bottom-block">
                                    <b-card-text class="publish-date" v-if="data.published_at != null">{{langauageData.label.published_on}} {{data.published_at | formatStoryDate}}</b-card-text>
                                    <div class="author-block">
                                        <i :style="{backgroundImage: 'url('+data.user_avatar+')'}"></i>
                                        <span>{{data.user_first_name}} {{data.user_last_name}}</span>
                                    </div>
                                </div>
                            </div>
                        </b-card-body>
                    </b-card>
                </b-col>
            </b-row>
        </div>
    </div>
</template>
<script>
  import store from '../store';
  import constants from '../constant';

  export default {
    name: "StoryCard",
    components: {},
    props : {
      storyListing : Array
    },
    data() {
      return {
        langauageData : [],
        showBlock: false
      };
    },
    methods: {
      getDescription(description) {
        let data = description.substring(0,105);
        return data
      },
      getMediaPath(data) {
        if(data.storyMedia && data.storyMedia.path != '') {
          let media = data.storyMedia;
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
        } else {
          return store.state.imagePath+'/assets/images/'+constants.MISSION_DEFAULT_PLACEHOLDER;
        }
      }
    },
    created() {
      this.langauageData = JSON.parse(store.state.languageLabel);

    },
    mounted() {}
  };
</script>
