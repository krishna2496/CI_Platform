<template>
    <div class="dashboard-stories inner-pages">
        <header>
            <ThePrimaryHeader></ThePrimaryHeader>
        </header>
        <main>
            <DashboardBreadcrumb />

            <div class="dashboard-tab-content">
                <b-container>
                    <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active': isLoaderActive}">
                        <div class="content-loader"></div>
                    </div>
                    <div class="heading-section">
                        <h1>{{languageData.label.my_stories}}</h1>
                        <b-button type="button" class="btn-bordersecondary" @click="publishNewStory">
                            {{languageData.label.publish_new_story}}
                        </b-button>
                    </div>
                    <div class="dashboard-story-content">
                        <p>
                            {{storyText}}
                        </p>
                    </div>
                    <b-list-group class="status-bar inner-statusbar">
                        <b-list-group-item>
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/published-ic.svg'" alt />
                                </i>
                                <p>
                                    <span v-if="stats.published"> {{stats.published}}</span><span
                                        v-else>0</span>{{languageData.label.published}}
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item>
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/pending-ic.svg'" alt="Pending" />
                                </i>
                                <p>
                                    <span v-if="stats.pending"> {{stats.pending}}</span><span
                                        v-else>0</span>{{languageData.label.pending}}
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item>
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/decline.svg'" alt="Decline" />
                                </i>
                                <p>
                                    <span v-if="stats.declined"> {{stats.declined}}</span><span
                                        v-else>0</span>{{languageData.label.declined}}
                                </p>
                            </div>
                        </b-list-group-item>
                        <b-list-group-item>
                            <div class="list-item">
                                <i>
                                    <img :src="$store.state.imagePath+'/assets/images/draft.svg'" alt="Draft" />
                                </i>
                                <p>
                                    <span v-if="stats.draft"> {{stats.draft}}</span><span
                                        v-else>0</span>{{languageData.label.draft}}
                                </p>
                            </div>
                        </b-list-group-item>
                    </b-list-group>
                    <div class="story-card-wrap" v-if="storyData.length > 0">
                        <h2>{{languageData.label.story_history}}</h2>
                        <b-row class="story-card-row">
                            <b-col class="story-card-block" md="6" lg="4" v-for="(data,index) in storyData" :key=index>
                                <div class="story-card-inner">
                                    <div class="story-img" :style="{backgroundImage: 'url('+getMediaPath(data)+')'}">
                                    </div>
                                    <div class="story-card">
                                        <div class="card-body-outer">
                                            <h4 class="story-card-title">
                                                {{data.title | substring(40)}}
                                            </h4>
                                            <div class="story-card-body">
                                                <span>{{data.created | formatStoryDate}}</span>
                                                <p v-if="data.description" v-html="getDescription(data.description)">
                                                </p>
                                            </div>
                                        </div>
                                        <div class="story-card-footer">
                                            <span class="status-label" v-if="data.status != ''">{{data.status}}</span>
                                            <div class="action-block">
                                                <b-button class="btn-action" v-b-tooltip.hover
                                                          :title="languageData.label.delete"
                                                          v-if="getDeleteAction(data.status)"
                                                          @click="deleteStory(data.story_id)">
                                                    <img :src="$store.state.imagePath+'/assets/images/gray-delete-ic.svg'"
                                                         alt="Delete" />
                                                </b-button>
                                                <b-link class="btn-action" v-b-tooltip.hover
                                                        :title="languageData.label.redirect" target="_blank"
                                                        :to="'/story-detail/' + data.story_id"
                                                        v-if="getRedirectAction(data.status_flag)">
                                                    <img :src="$store.state.imagePath+'/assets/images/external-link.svg'"
                                                         alt="Redirect" />
                                                </b-link>
                                                <b-button class="btn-action" v-b-tooltip.hover
                                                          :title="languageData.label.copy" v-if="getCopyAction(data.status_flag)"
                                                          @click="copyStory(data.story_id)">
                                                    <img :src="$store.state.imagePath+'/assets/images/copy.svg'"
                                                         alt="Copy" />
                                                </b-button>
                                                <b-link class="btn-action" v-if="getEditAction(data.status_flag)"
                                                        :to="'/edit-story/' + data.story_id" v-b-tooltip.hover
                                                        :title="languageData.label.edit">
                                                    <img :src="$store.state.imagePath+'/assets/images/edit-ic.svg'"
                                                         alt="Edit" />
                                                </b-link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </b-col>

                        </b-row>
                        <div class="pagination-block" data-aos="fade-up" v-if="pagination.totalPages > 1">
                            <b-pagination
                                    :hide-ellipsis="hideEllipsis"
                                    v-model="pagination.currentPage" :total-rows="pagination.total"
                                    :per-page="pagination.perPage" align="center" @change="pageChange"
                                    aria-controls="my-cardlist"></b-pagination>
                        </div>
                        <div class="btn-row" v-if="storyData.length > 0">
                            <b-button class="btn-bordersecondary ml-auto" @click="exportFile()">
                                {{languageData.label.export}}</b-button>
                        </div>
                    </div>
                </b-container>
            </div>
        </main>
        <footer>
            <TheSecondaryFooter></TheSecondaryFooter>
        </footer>
    </div>
</template>

<script>
  import constants from '../constant';
  import ThePrimaryHeader from "../components/Layouts/ThePrimaryHeader";
  import TheSecondaryFooter from "../components/Layouts/TheSecondaryFooter";
  import DashboardBreadcrumb from "../components/DashboardBreadcrumb";
  import {
    myStory,
    copyStory,
    deleteStory
  } from "../services/service";
  import ExportFile from "../services/ExportFile";
  import store from '../store';
  export default {
    components: {
      ThePrimaryHeader,
      TheSecondaryFooter,
      DashboardBreadcrumb
    },
    name: "dashboardstories",
    data() {
      return {
        stats: [],
        storyData: [],
        languageData: [],
        pagination: {
          'currentPage': 1,
          "total": 0,
          "perPage": 1,
          "totalPages": 0,
        },
        storyText: '',
        isLoaderActive: false,
        isStoryDisplay: true,
        hideEllipsis:true
      };
    },
    methods: {
      pageChange(page) {
        setTimeout(() => {
          window.scrollTo({
            'behavior': 'smooth',
            'top': 0
          }, 0);
        });
        this.getMyStory(page);
      },
      getMyStory(page) {
        this.isLoaderActive = true
        myStory(page).then(response => {
          if (response.error == false) {
            if (response.data && response.data.stats) {
              this.stats = response.data.stats
              this.storyData = response.data.story_data
              this.pagination.currentPage = response.pagination.current_page
              this.pagination.total = response.pagination.total
              this.pagination.perPage = response.pagination.per_page
              this.pagination.totalPages = response.pagination.total_pages
            } else {
              this.stats = [];
              this.storyData = []
              this.pagination.currentPage = 1,
                this.pagination.total = 0,
                this.pagination.perPage = 1,
                this.pagination.totalPages = 0
              if (page != 1) {
                this.getMyStory(this.pagination.currentPage)
              }
            }
          }
        })
        this.isLoaderActive = false
      },
      publishNewStory() {
        this.$router.push({
          name: 'ShareStory'
        })
      },
      getDescription(description) {
        let data = description.substring(0, 150);
        return data
      },
      getMediaPath(data) {
        if (data.storyMedia && data.storyMedia.path != '') {
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
          return store.state.imagePath + '/assets/images/' + constants.MISSION_DEFAULT_PLACEHOLDER;
        }
      },
      getDeleteAction(status) {
        if (status != '') {
          return true
        }
      },
      getRedirectAction(status) {
        if (status != '') {
          if (status == constants.PUBLISHED_STORY || status == constants.PENDING_STORY) {
            return true
          } else {
            return false
          }
        }
      },
      getCopyAction(status) {
        if (status != '') {
          if (status == constants.DECLINED_STORY) {
            return true
          } else {
            return false
          }
        }
      },

      getEditAction(status) {
        if (status != '') {
          if (status == constants.DRAFT_STORY || status == constants.PENDING_STORY) {
            return true
          } else {
            return false
          }
        }
      },

      deleteStory(storyId) {
        this.$bvModal.msgBoxConfirm(this.languageData.label.delete_story, {
          buttonSize: 'md',
          okTitle: this.languageData.label.yes,
          cancelTitle: this.languageData.label.no,
          centered: true,
          size: 'md',
          buttonSize: 'sm',
          okVariant: 'success',
          headerClass: 'p-2 border-bottom-0',
          footerClass: 'p-2 border-top-0',
          centered: true
        })
          .then(value => {
            if (value == true) {
              this.isLoaderActive = true
              deleteStory(storyId).then(response => {
                if (response.error === true) {
                  this.makeToast('danger', response.message)
                  this.isLoaderActive = false
                } else {
                  this.makeToast('success', this.languageData.label.story_deleted)
                  // this.pagination.currentPage = 1
                  this.getMyStory(this.pagination.currentPage);
                }
              })
            }
          })

      },

      copyStory(storyId) {
        this.isLoaderActive = true
        copyStory(storyId).then(response => {
          if (response.error === true) {
            this.makeToast('danger', response.message)
            this.isLoaderActive = false
          } else {
            this.makeToast('success', response.message)
            this.getMyStory(this.pagination.currentPage);
          }
        })
      },

      makeToast(variant = null, message) {
        this.$bvToast.toast(message, {
          variant: variant,
          solid: true,
          autoHideDelay: 3000
        })
      },
      exportFile() {
        this.isLoaderActive = true
        let fileName = this.languageData.export_timesheet_file_names.MY_STORIES_XLSX
        let exportUrl = "app/story/export"
        ExportFile(exportUrl, fileName);
        this.isLoaderActive = false
      }
    },

    created() {
      this.getMyStory(this.pagination.currentPage);
      this.languageData = JSON.parse(store.state.languageLabel);
      this.isStoryDisplay = this.settingEnabled(constants.STORIES_ENABLED);
      if (!this.isStoryDisplay) {
        this.$router.push('/home')
      }
      let storyArray = JSON.parse(store.state.storyDashboardText)
      if (storyArray) {
        storyArray.filter((data, index) => {
          if (data.lang == store.state.defaultLanguage.toLowerCase()) {
            this.storyText = data.message
          }
        })
      }
    }
  };

</script>
