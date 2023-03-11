<template>
    <div class="cards-wrapper">
        <div class="card-grid">
            <b-row>
                <b-col lg="4" sm="6" class="card-outer" data-aos="fade-up" v-for="(data,key) in newsListing" v-bind:key="key">
                    <b-card no-body>
                        <b-card-header>
                            <div class="header-img-block">
                                <b-link class="group-img" v-if="data.news_image != '' && data.news_image != null" :style="{backgroundImage: 'url('+data.news_image+')'}"></b-link>
                                <b-link class="group-img" v-else :style="{backgroundImage: 'url('+getDefaultImage()+')'}"></b-link>
                            </div>
                            <div class="group-category">
                                <span class="category-text" v-if="data.news_category[0]">{{data.news_category[0]}}</span>
                            </div>
                            <b-link class="btn btn-borderwhite icon-btn" :title="langauageData.label.view_detail" :to="'/news-detail/'+data.news_id">
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
                                <div class="top-block" v-if="data.news_content">
                                    <b-link
                                            :to="'/news-detail/'+data.news_id"
                                            :title="data.news_content.title"
                                            class="card-title mb-2"
                                            v-if="data.news_content.title"
                                    >{{data.news_content.title | substring(60)}}
                                    </b-link>
                                    <b-card-text v-if="data.news_content.description">
                                        {{data.news_content.description | substring(105)}}
                                    </b-card-text>
                                </div>
                                <div class="bottom-block">
                                    <b-card-text class="publish-date" v-if="data.published_on != null">{{langauageData.label.published_on}} {{data.published_on | formatDate}}</b-card-text>
                                    <div class="author-block">
                                        <i :style="{backgroundImage: 'url('+data.user_thumbnail+')'}"></i>
                                        <span>{{data.user_name}}</span>
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
    name: "NewsCard",
    components: {},
    props : {
      newsListing : Array
    },
    data() {
      return {
        langauageData : [],
        showBlock: false,
        max: 100,
        value: 80
      };
    },
    methods: {
      getDefaultImage() {
        return store.state.imagePath+'/assets/images/'+constants.MISSION_DEFAULT_PLACEHOLDER;
      }
    },
    created() {
      this.langauageData = JSON.parse(store.state.languageLabel);

    },
    mounted() {}
  };
</script>
