<template>
    <div class="share-stories-page inner-pages">
        <header>
            <ThePrimaryHeader></ThePrimaryHeader>
        </header>
        <main>
            <b-container>

                <h1>{{languageData.label.share_your_story}}</h1>

                <b-alert show variant="warning" v-if="pageLoaded && missionTitle.length == 0">
                    {{languageData.label.not_volunteered_text}}</b-alert>
                <b-alert show :variant="classVariant" dismissible v-model="showDismissibleAlert">{{ message }}</b-alert>
                <b-row class="story-form-wrap">
                    <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active': isLoaderActive}">
                        <div class="content-loader"></div>
                    </div>
                    <b-col xl="8" lg="7" class="left-col">
                        <div class="story-form">
                            <b-form autocomplete="on">
                                <b-form-group>
                                    <label for>{{languageData.label.my_story_title}}*</label>
                                    <b-form-input id v-model.trim="story.title"
                                                  :class="{ 'is-invalid': submitted && $v.story.title.$error }" type="text"
                                                  maxLength="255" :placeholder="languageData.placeholder.story_title">
                                    </b-form-input>
                                    <div v-if="submitted && !$v.story.title.required" class="invalid-feedback">
                                        {{ languageData.errors.story_title_required }}
                                    </div>
                                </b-form-group>
                                <b-row>
                                    <b-col md="12">
                                        <b-form-group>
                                            <label for>{{languageData.label.select_mission}}*</label>
                                            <AppCustomDropdown v-model="story.mission"
                                                               :errorClass="submitted && $v.story.mission.$error"
                                                               :optionList="missionTitle" @updateCall="updateMissionTitle"
                                                               translationEnable="false" :defaultText="defaultMissionTitle" />
                                            <div v-if="submitted && !$v.story.mission.required"
                                                 class="invalid-feedback">
                                                {{ languageData.errors.mission_required }}
                                            </div>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col md="12">
                                        <label for>{{languageData.label.my_story}}*</label>
                                        <vue-ckeditor :config="config" v-model="story.myStory"
                                                      :class="{ 'is-invalid': submitted && $v.story.myStory.$error }"
                                                      :placeholder="languageData.placeholder.story_detail" />
                                        <div v-if="submitted && !$v.story.myStory.required" class="invalid-feedback">
                                            {{ languageData.errors.story_description_required }}
                                        </div>
                                    </b-col>
                                </b-row>
                            </b-form>


                        </div>
                    </b-col>
                    <b-col xl="4" lg="5" class="right-col">
                        <div class="story-form">
                            <b-form-group>
                                <label
                                        for>{{languageData.label.enter_video_url}} <span>({{languageData.label.new_line_to_enter_multiple_urls}})</span></label>
                                <b-form-textarea id v-model.trim="story.videoUrl"
                                                 :class="{ 'is-invalid': submitted && (youtubeUrlError || maxYoutubeUrlError || duplicateYoutubeUrlError)}"
                                                 :placeholder="languageData.placeholder.video_url" size="lg" rows="5">
                                </b-form-textarea>
                                <div v-if="submitted && youtubeUrlError" class="invalid-feedback">
                                    {{ languageData.errors.valid_video_url }}
                                </div>
                                <div v-if="submitted && !youtubeUrlError && maxYoutubeUrlError"
                                     class="invalid-feedback">
                                    {{ languageData.errors.max_video_upload }}
                                </div>
                                <div v-if="submitted && !youtubeUrlError && !maxYoutubeUrlError && duplicateYoutubeUrlError"
                                     class="invalid-feedback">
                                    {{languageData.errors.duplicate_video_url}}
                                </div>
                            </b-form-group>
                            <b-form-group>

                                <label for>{{languageData.label.upload_your_photos}}</label>
                                <div class="btn-wrapper" v-bind:class="{'has-error' : fileError != '' ? true : false}">
                                    <file-upload class="btn" v-model="story.files" accept="image/png,image/jpeg"
                                                 :multiple="true" :drop="true" :drop-directory="true" :size="1024 * 1024 * 10"
                                                 @input="inputUpdate" @input-filter="inputFilter" ref="upload">
                                        <span class="hidden-sm">{{languageData.label.drag_and_drop_pictures}}</span>
                                        <span class="visible-sm">{{languageData.label.upload_pictures}}</span>
                                    </file-upload>
                                </div>
                                <span class="error-message" v-if="fileError">{{fileError}}</span>
                            </b-form-group>
                            <div class="uploaded-block">
                                <div class="uploaded-file-details" v-if="fileArray.length > 0"
                                     v-for="(fileData, index) in fileArray" :key=index>
                                    <span class="image-thumb">
                                        <img :src="fileData[1]" width="40" height="auto" />
                                        <b-button type="button" @click.prevent="removeFiles(fileData[0],index)"
                                                  class="remove-btn" v-b-tooltip.hover :title="languageData.label.delete">
                                            <img :src="$store.state.imagePath+'/assets/images/cross-ic-white.svg'"
                                                 alt />
                                        </b-button>
                                    </span>
                                </div>
                                <div class="uploaded-file-details" v-for="(file, index) in story.files" :key="index">
                                    <span v-if="file.thumb" class="image-thumb">
                                        <img :src="file.thumb" width="40" height="auto" />
                                        <b-button type="button" @click.prevent="remove(file)" class="remove-btn"
                                                  v-b-tooltip.hover :title="languageData.label.delete">
                                            <img :src="$store.state.imagePath+'/assets/images/cross-ic-white.svg'"
                                                 alt />
                                        </b-button>
                                    </span>
                                </div>

                            </div>
                        </div>
                    </b-col>
                </b-row>
                <div class="btn-row">
                    <b-button class="btn-borderprimary btn-validate" @click="cancleShareStory">{{languageData.label.cancel}}
                    </b-button>
                    <!-- <b-button class="btn-borderprimary" v-bind:class="{disabled:previewButtonEnable}"
                        @click="previewStory(storyId)"><span>{{languageData.label.preview}}
                        </span></b-button> -->
                    <b-button class="btn-borderprimary btn-validate" @click="saveStory('preview')">
                        <span>{{languageData.label.preview}}
                        </span></b-button>
                    <b-button class="btn-bordersecondary btn-validate"
                              v-bind:class="{disabled:saveButtonEnable || saveButtonAjaxCall}" @click="saveStory('save')">
                        {{languageData.label.save}}</b-button>
                    <b-button class="btn-bordersecondary btn-submit btn-validate"
                              v-bind:class="{disabled:submitButtonEnable || submitButtonAjaxCall}"
                              @click="saveStory('submit')">{{languageData.label.submit}}</b-button>
                </div>
            </b-container>
        </main>
        <footer>
            <TheSecondaryFooter></TheSecondaryFooter>
        </footer>
    </div>
</template>
<script>
  import AppCustomDropdown from "../components/CustomFieldDropdown";
  import FileUpload from "vue-upload-component";
  import DatePicker from "vue2-datepicker";
  import store from '../store';
  import constants from '../constant';
  import {
    required,
    maxLength,
    email,
    sameAs,
    minLength,
    between,
    helpers
  } from 'vuelidate/lib/validators';
  import {
    storyMissionListing,
    submitStory,
    updateStory,
    editStory,
    updateStoryStatus,
    deleteStoryImage
  } from "../services/service";
  import VueCkeditor from 'vue-ckeditor2'
  import { setTimeout } from 'timers';
  export default {
    components: {
      ThePrimaryHeader: () => import("../components/Layouts/ThePrimaryHeader"),
      TheSecondaryFooter: () => import("../components/Layouts/TheSecondaryFooter"),
      AppCustomDropdown,
      FileUpload,
      DatePicker,
      VueCkeditor
    },
    data() {
      return {
        formChange: 0,
        languageData: [],
        isStoryDisplay: true,
        submitted: false,
        defaultMissionTitle: "",
        fileError: "",
        missionTitle: [],
        classVariant: 'danger',
        message: null,
        time1: "",
        story: {
          title: "",
          mission: "",
          myStory: "",
          videoUrl: "",
          files: []
        },
        youtubeUrlError: false,
        maxYoutubeUrlError: false,
        showDismissibleAlert: false,
        storyId: '',
        saveButtonAjaxCall: false,
        submitButtonAjaxCall: false,
        saveButtonEnable: false,
        submitButtonEnable: true,
        previewButtonEnable: true,
        content: '',
        fileArray: [],
        isLoaderActive: false,
        duplicateYoutubeUrlError: false,
        pageLoaded: false,
        config: {
          toolbar: [
            ['Templates'],
            ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'],
            ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-'],
            ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote',
              '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr',
              'BidiRtl'
            ],
            ['Link', 'Unlink'],
            ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'],
            ['Format'],
            ['ShowBlocks','Maximize']
          ]
        },
        errorInGetStoryDetail: false,
        unprocessableEntityStatus: 422
      }
    },
    validations: {
      story: {
        title: {
          required
        },
        mission: {
          required
        },
        myStory: {
          required
        }
      }
    },
    methods: {
      saveStory(params) {
        this.youtubeUrlError = false
        this.duplicateYoutubeUrlError = false
        this.submitted = true;
        this.$v.$touch();
        let youtubeUrl = [];
        if (this.story.videoUrl.toString() != '') {
          youtubeUrl = this.story.videoUrl.toString().split("\n")
          if (youtubeUrl.length > constants.MAX_FILE_NUMBER) {
            this.maxYoutubeUrlError = true
            return
          } else {
            this.maxYoutubeUrlError = false
          }
          youtubeUrl.filter((url, index) => {
            url = url.trim()
            var valid =
              /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})?$/;
            if (!valid.test(url)) {
              this.youtubeUrlError = true
              return
            }
          })
          if ((new Set(youtubeUrl)).size !== youtubeUrl.length) {
            this.duplicateYoutubeUrlError = true
            return
          }
        }
        if (this.youtubeUrlError == true || this.maxYoutubeUrlError == true) {
          return
        }
        if (this.$v.$invalid) {
          return;
        }

        this.isLoaderActive = true
        if (params == 'save') {
          this.saveButtonAjaxCall = true
          this.submitStoryDetail(params);
        } else if (params == 'preview') {
          this.saveButtonAjaxCall = true
          this.submitStoryDetail(params);
        } else {
          this.submitStory();
        }
      },
      cancleShareStory() {
        this.storyId = ''
        this.$router.go(-1)
      },

      submitStory() {
        this.submitButtonAjaxCall = true;
        updateStoryStatus(this.storyId).then(response => {
          this.formChange = 0;
          this.showDismissibleAlert = true
          if (response.error === true) {
            this.classVariant = 'danger'
            //set error msg
            this.message = response.message
          } else {
            this.classVariant = 'success'
            //set error msg
            this.message = response.message
          }
          this.isLoaderActive = false
          this.submitButtonAjaxCall = false
        })
      },
      previewStory(storyId) {
        let routeData = this.$router.resolve({
          path: "/story-preview" + '/' + storyId
        });
        window.open(routeData.href, '_blank');
      },
      updateMissionTitle(value) {
        this.defaultMissionTitle = value.selectedVal;
        this.story.mission = value.selectedId;
      },
      inputUpdate(files) {
        let allowedFileTypes = constants.ALLOWED_PICTURE_TYPES
        let availableFileCount = this.fileArray.length

        this.fileError = '';
        let error = false
        let duplicateUpload = false
        let latestUpload = files[files.length - 1];
        if (files.length > 0) {
          let latestUploadIndex = files.length - 1;
          let latestUploadName = latestUpload.name
          let latestUploadSize = latestUpload.size
          let latestUploadType = latestUpload.type
          let totalFiles = availableFileCount + files.length
          if (totalFiles > constants.MAX_FILE_NUMBER) {
            files.splice(latestUploadIndex, 1)
            this.fileError = this.languageData.errors.max_image_upload
          } else {
            files.filter((data, index) => {
              let fileName = data.name.split('.');
              fileName = fileName[fileName.length - 1].toLowerCase()
              if (!allowedFileTypes.includes(fileName)) {
                this.fileError = this.languageData.errors.invalid_image_type
                error = true
              } else {
                if (data.size > constants.FILE_MAX_SIZE_BYTE) {
                  this.fileError = this.languageData.errors.file_max_size
                  error = true
                }
              }
              if (index != files.length - 1) {
                if (data.name == latestUploadName && data.size == latestUploadSize && data
                    .type ==
                  latestUploadType) {
                  this.fileError = this.languageData.errors.file_already_uploaded
                  error = true
                  duplicateUpload = true;
                }
              }
              if (error == true) {
                if (duplicateUpload == true) {
                  files.splice(latestUploadIndex, 1)
                } else {
                  files.splice(index, 1)
                }
              }
            });
          }
        }
      },
      inputFilter(newFile, prevent) {
        let allowedFileTypes = constants.ALLOWED_PICTURE_MIME_TYPES
        if (newFile && (allowedFileTypes.includes(newFile.type)) && newFile.size < constants
          .FILE_MAX_SIZE_BYTE) {
          if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
            return prevent();
          }
          // Filter php html js file
          if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
            return prevent();
          }
        }
        if (newFile) {
          // Create a blob field
          newFile.blob = "";
          let URL = window.URL || window.webkitURL;
          if (URL && URL.createObjectURL) {
            newFile.blob = URL.createObjectURL(newFile.file);
          }
          // Thumbnails
          newFile.thumb = "";
          if (newFile.blob && newFile.type.substr(0, 6) === "image/") {
            newFile.thumb = newFile.blob;
          }
        }
      },

      remove(file) {
        this.$refs.upload.remove(file);
      },

      missionListing() {
        storyMissionListing().then(response => {
          // missionTitle
          var array = [];
          if (response.error == false) {
            let missionArray = response.data
            if (missionArray) {
              missionArray.filter((data, index) => {
                array[index] = new Array(2);
                array[index][0] = data.mission_id
                array[index][1] = data.title
              })
              this.missionTitle = array
            }
          }
          this.pageLoaded = true
          if (this.$route.params.storyId) {
            this.storyId = this.$route.params.storyId;
            this.getStoryDetail();
          }
        })
      },

      submitStoryDetail(params) {
        const formData = new FormData();
        this.message = null;
        this.showDismissibleAlert = false
        let file = this.story.files;
        if (file) {
          file.filter((fileItem, fileIndex) => {
            formData.append('story_images[]', fileItem.file);
          })
        }

        formData.append('mission_id', this.story.mission);
        formData.append('title', this.story.title);
        formData.append('description', this.story.myStory);
        if (this.story.videoUrl != '') {
          let videoUrl = this.story.videoUrl.split('\n').join(',');
          formData.append('story_videos', videoUrl);
        }

        if (this.storyId == '') {
          submitStory(formData).then(response => {
            if (response.error === true) {
              this.showDismissibleAlert = true
              this.classVariant = 'danger'
              //set error msg
              this.message = response.message
            } else {
              this.formChange = 0;
              this.storyId = response.data;

              if (params == "preview" && this.storyId != '') {
                let routeData = this.$router.resolve({
                  path: "/story-preview" + '/' + this.storyId
                });
                window.open(routeData.href, '_blank');
                this.isLoaderActive = false
                this.saveButtonAjaxCall = false
                this.getStoryDetail();
                return;
              } else {
                this.showDismissibleAlert = true
                if (this.storyId != '') {
                  this.previewButtonEnable = false
                  this.submitButtonEnable = false
                  this.getStoryDetail();
                } else {
                  this.previewButtonEnable = true
                  this.submitButtonEnable = true
                }
                this.classVariant = 'success'
                //set error msg
                this.message = response.message
              }
            }
            this.isLoaderActive = false
            this.saveButtonAjaxCall = false
          })
        } else {
          if (this.story.videoUrl == '') {
            formData.append('story_videos', '');
          }

          formData.append('_method', 'PATCH');
          updateStory(formData, this.storyId).then(response => {
            this.showDismissibleAlert = true
            if (response.error === true) {
              this.classVariant = 'danger'
              //set error msg
              this.message = response.message
            } else {
              this.formChange = 0;

              if (params == "preview" && this.storyId != '') {
                let routeData = this.$router.resolve({
                  path: `/story-preview/${this.storyId}`
                });
                window.open(routeData.href, '_blank');
                this.isLoaderActive = false;
                this.saveButtonAjaxCall = false;
                this.showDismissibleAlert = false;
                this.getStoryDetail();
                return;
              }

              if (this.storyId != '' && params != 'preview') {
                this.previewButtonEnable = false
                this.submitButtonEnable = false
                this.getStoryDetail();
              } else {
                this.previewButtonEnable = true
                this.submitButtonEnable = true
              }
              this.classVariant = 'success'
              //set error msg
              this.message = response.message
            }
            this.isLoaderActive = false
            this.saveButtonAjaxCall = false
          })
        }
        this.fileError = ''
      },

      removeFiles(id, index) {
        this.showDismissibleAlert = false
        let data = {
          'storyId': '',
          'imageId': ''
        }
        data.imageId = id;
        // data.imageId = 5
        data.storyId = this.storyId;
        deleteStoryImage(data).then(response => {
          if (response.error === true) {
            this.showDismissibleAlert = true
            this.classVariant = 'danger'
            //set error msg
            this.message = response.message
          } else {
            this.fileArray.splice(index, 1)
          }
          this.saveButtonAjaxCall = false
        })
      },

      getStoryDetail() {
        this.isLoaderActive = true
        let storyID = ''
        if (this.$route.params.storyId) {
          storyID = this.$route.params.storyId
        } else {
          storyID = this.storyId
        }
        this.story.files = []
        editStory(storyID).then(response => {
          if (response.error == false) {
            this.story.title = response.data.title
            setTimeout(() => {
              this.story.myStory = response.data.description
            }, 200);
            let videoUrl = '';
            let imageUrl = []
            let i = 0;
            if (response.data.story_media) {
              let storyMedia = response.data.story_media;
              storyMedia.filter((data, index) => {
                if (data.type == 'video') {
                  videoUrl = data.path
                } else {
                  imageUrl[i] = new Array(2);
                  imageUrl[i][0] = data.story_media_id
                  imageUrl[i][1] = data.path
                  i++;
                }
              })

            }
            this.story.videoUrl = videoUrl.split(",").join("\n");
            this.fileArray = imageUrl
            this.missionTitle.filter((data, index) => {
              if (data[0] == response.data.mission_id) {
                this.defaultMissionTitle = data[1]
              }
            })

            this.story.mission = response.data.mission_id
            this.submitButtonEnable = false
            if (response.data.status == constants.PENDING) {
              this.submitButtonEnable = true
            }
            this.previewButtonEnable = false
            this.isLoaderActive = false
            setTimeout(() => {
              this.formChange = 0;
            },500)

          } else {
            if (response.status !== this.unprocessableEntityStatus) {
              this.errorInGetStoryDetail = true;
              return this.$router.push('/404');
            }

            this.$bvModal.msgBoxOk(response.message, {
              buttonSize: 'sm',
              size: 'md',
              okVariant: 'danger',
              footerClass: 'p-2 border-top-0',
              centered: true
            }).then(() => {
              this.errorInGetStoryDetail = true;
              return this.$router.push('/my-stories');
            });

          }
        })
      },

      openConfirmModal() {
        this.$bvModal.msgBoxConfirm(this.languageData.label.cance_story, {
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
        }).then(value => {
          if (value == true) {
            this.cancleShareStory()
          }
        })
      }

    },
    beforeRouteLeave (to, from, next) {
      if(this.formChange != 0 && this.errorInGetStoryDetail === false) {
        this.$bvModal.msgBoxConfirm(this.languageData.label.cancel_story, {
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
        }).then(value => {
          if (value == true) {
            next()
          } else {
            next(false)
          }
        })
      } else {
        next();
      }
    },
    created() {
      this.languageData = JSON.parse(store.state.languageLabel);
      this.isStoryDisplay = this.settingEnabled(constants.STORIES_ENABLED);
      if (!this.isStoryDisplay) {
        this.$router.push('/home')
      }
      this.defaultMissionTitle = this.languageData.label.mission_title
      this.missionListing();
    },
    watch: {
      story: {
        handler: function(val, oldVal) {
          this.formChange = 1;
        },
        deep: true
      },
    },
    updated() {}
  };

</script>
