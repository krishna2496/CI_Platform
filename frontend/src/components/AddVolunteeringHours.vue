<template>
  <div>
    <b-modal
      ref="timeHoursModal"
      @hidden="hideModal"
      :modal-class="'time-hours-modal table-modal'"
      hide-footer
    >
      <template slot="modal-header" slot-scope="{ close }">
          <i class="close" @click="close()" v-b-tooltip.hover :title="languageData.label.close"></i>
          <h5 class="modal-title">{{languageData.label.hour_entry_modal_title}}</h5>
      </template>
      <b-alert show :variant="classVariant" dismissible v-model="showErrorDiv">
          {{ message }}
      </b-alert>
      <div class="table-wrapper-outer">
        <div v-bind:class="{ 'content-loader-wrap': true, 'loader-active': isAjaxCall}">
          <div class="content-loader"></div>
        </div>
        <form action class="form-wrap">
          <b-form-group>
            <b-row>
              <b-col sm="12">
                <b-form-group>
                  <label for>{{languageData.label.mission}}</label>
                  <b-form-input
                    id
                    type="text"
                    disabled
                    v-model.trim="timeEntryDefaultData.missionName"
                    class="disabled"
                  ></b-form-input>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group>
                  <label for>{{languageData.label.hours}}*</label>
                  <b-form-input
                    id
                    :placeholder="languageData.placeholder.spent_hours"
                    :class="{'is-invalid': submitted && ((!$v.timeEntryDefaultData.hours.required) || ($v.timeEntryDefaultData.hours.requiredHourValidation.$invalid) || (!$v.timeEntryDefaultData.hours.numeric) || (!$v.timeEntryDefaultData.hours.between)) }"
                    type="text"
                    maxlength="2"
                    @input="hourChange"
                    v-model.trim="timeEntryDefaultData.hours"
                  ></b-form-input>

                  <div
                    v-if="submitted && ((!$v.timeEntryDefaultData.hours.required) || ($v.timeEntryDefaultData.hours.requiredHourValidation.$invalid))"
                    class="invalid-feedback"
                  >
                    {{ languageData.errors.hours_is_required }}
                  </div>

                  <div
                    v-if="submitted && (!$v.timeEntryDefaultData.hours.numeric)"
                    class="invalid-feedback"
                  >
                    {{ languageData.errors.hours_numeric }}
                  </div>

                  <div
                    v-if="submitted && (!$v.timeEntryDefaultData.hours.between) && ($v.timeEntryDefaultData.hours.numeric)"
                   class="invalid-feedback"
                  >
                    {{ languageData.errors.hours_is_between }}
                  </div>
                </b-form-group>
              </b-col>
              <b-col sm="6">
                <b-form-group>
                  <label for>{{languageData.label.minutes}}*</label>
                    <b-form-input
                      id
                      :placeholder="languageData.placeholder.spent_minutes"
                      :class="{'is-invalid' :submitted && ((!$v.timeEntryDefaultData.minutes.required) || ($v.timeEntryDefaultData.minutes.requiredMinuteValidation.$invalid) || (!$v.timeEntryDefaultData.minutes.numeric) || (!$v.timeEntryDefaultData.minutes.between)) }"
                      type="text"
                      maxlength="2"
                      @input="minuteChange"
                      v-model.trim="timeEntryDefaultData.minutes"
                    ></b-form-input>

                    <div
                      v-if="submitted && ((!$v.timeEntryDefaultData.minutes.required) || ($v.timeEntryDefaultData.minutes.requiredMinuteValidation.$invalid))"
                      class="invalid-feedback"
                    >
                      {{ languageData.errors.minute_is_required }}
                    </div>
                    <div
                      v-if="submitted && (!$v.timeEntryDefaultData.minutes.numeric)"
                      class="invalid-feedback"
                    >
                      {{ languageData.errors.minute_numeric }}
                    </div>
                    <div
                      v-if="submitted && (!$v.timeEntryDefaultData.minutes.between) && ($v.timeEntryDefaultData.minutes.numeric)"
                      class="invalid-feedback"
                    >
                      {{ languageData.errors.minute_between }}
                    </div>
                </b-form-group>
              </b-col>
            </b-row>
          </b-form-group>
          <b-form-group>
            <b-row>
              <b-col sm="6" class="date-col">
                <b-form-group>
                  <label for>{{languageData.label.date_volunteered}}*</label>
                  <date-picker
                    v-model="timeEntryDefaultData.dateVolunteered"
                    :notAfter="timeEntryDefaultData.disabledFutureDates"
                    :notBefore="timeEntryDefaultData.disabledPastDates" :disabledDays="disableDates"
                    @change="dateChange()"
                    :class="{ 'is-invalid': submitted && $v.timeEntryDefaultData.dateVolunteered.$error }"
                    :lang="lang"
                  >
                  </date-picker>
                  <div
                    v-if="submitted && !$v.timeEntryDefaultData.dateVolunteered.required"
                    class="invalid-feedback"
                  >
                    {{ languageData.errors.date_volunteer_is_required }}
                  </div>
                </b-form-group>
              </b-col>
              <b-col sm="6" class="date-col">
                <b-form-group>
                  <label for>{{languageData.label.day_volunteered}}*</label>
                  <AppCustomDropdown
                    v-model="timeEntryDefaultData.workDay"
                    :optionList="workDayList"
                    :errorClass="submitted && $v.timeEntryDefaultData.workDay.$error"
                    :defaultText="defaultWorkday"
                    @updateCall="updateWorkday"
                    translationEnable="true"
                  />
                  <div
                    v-if="submitted && !$v.timeEntryDefaultData.workDay.required"
                    class="invalid-feedback"
                  >
                    {{ languageData.errors.work_day }}
                  </div>
                </b-form-group>
              </b-col>
            </b-row>
          </b-form-group>
          <b-form-group>
            <b-row>
              <b-col sm="12">
                <b-form-group>
                  <label for>{{languageData.label.notes}}</label>
                  <b-form-textarea
                    id
                    v-model="timeEntryDefaultData.notes"
                    :placeholder="languageData.placeholder.notes"
                    size="lg"
                    rows="5"
                  >
                  </b-form-textarea>
                </b-form-group>
              </b-col>
            </b-row>
          </b-form-group>
          <b-form-group v-if="isFileUploadDisplay">
            <b-row>
              <b-col sm="12">
                <span class="error-message" v-if="fileError">
                  {{fileError}}
                </span>
              </b-col>
              <b-col md="6" class="date-col">
                <label for>{{languageData.label.file_upload}}</label>
                <div
                  class="file-upload-wrap"
                  v-bind:class="{'has-error' : fileError !== ''}"
                >
                  <div
                    class="btn-wrapper"
                    v-bind:class="{'has-error' : fileError !== ''}"
                  >
                    <file-upload
                      class="btn"
                      accept="image/png,image/jpeg,application/doc,application/docx,application/xls,application/xlsx,application/csv,application/pdf"
                      :multiple="true"
                      :drop="true"
                      :drop-directory="true"
                      @input="inputUpdate"
                      :size="1024 * 1024 *10"
                      v-model="fileArray"
                      ref="upload"
                    >
                      {{languageData.label.browse}}
                    </file-upload>
                    <span>{{languageData.label.drop_files}}</span>
                  </div>
                  <div class="uploaded-file-wrap">
                    <div
                      class="uploaded-file-details"
                      v-for="(file, index) in timeEntryDefaultData.documents"
                      v-bind:key=index
                    >
                      <a
                        class="filename"
                        :href="file.document_path"
                        target="_blank"
                      >
                        {{file.document_name}}
                      </a>
                      <b-button
                        class="remove-item"
                        @click.prevent="deleteFile(file.timesheet_id,file.timesheet_document_id)"
                        :title="languageData.label.delete">
                        <img :src="$store.state.imagePath+'/assets/images/delete-ic.svg'" alt="delete-ic" />
                      </b-button>
                    </div>
                    <div
                      class="uploaded-file-details"
                      v-for="file in fileArray"
                      :key="file.id"
                    >
                      <p class="filename">{{file.name}}</p>
                      <b-button
                        class="remove-item"
                        @click.prevent="$refs.upload.remove(file)"
                        :title="languageData.label.delete">
                        <img :src="$store.state.imagePath+'/assets/images/delete-ic.svg'" alt="delete-ic" />
                      </b-button>
                      </div>
                  </div>
                </div>
              </b-col>
            </b-row>
          </b-form-group>
        </form>
        <div class="btn-wrap">
          <b-button class="btn-borderprimary" @click="$refs.timeHoursModal.hide()">
            {{languageData.label.cancel}}
          </b-button>
          <b-button
            class="btn-bordersecondary"
            v-bind:class="{disabled:isAjaxCall}"
            @click="saveTimeHours()"
          >
            {{languageData.label.save}}
          </b-button>
        </div>
      </div>
    </b-modal>
  </div>
</template>

<script>
  import store from '../store';
  import moment from 'moment'
  import DatePicker from "vue2-datepicker";
  import AppCustomDropdown from "../components/CustomFieldDropdown";
  import {
    required,
    between,
    numeric
  } from 'vuelidate/lib/validators';
  import FileUpload from 'vue-upload-component';
  import {
    addVolunteerEntry,
    removeDocument
  } from '../services/service';
  import constants from '../constant';

  export default {
    name: "VolunteeringHours",
    components: {
      DatePicker,
      AppCustomDropdown,
      FileUpload
    },
    props: {
      defaultWorkday: String,
      files: Array,
      timeEntryDefaultData: Object,
      workDayList: Array,
      disableDates: Array,
      defaultHours: String,
      defaultMinutes: String
    },
    data: function () {
      return {
        lang: '',
        languageData: [],
        submitted: false,
        disabledFutureDates: new Date(),
        fileArray: this.files,
        showErrorDiv: false,
        message: null,
        fileError: "",
        classVariant: "success",
        isFileUploadDisplay: false,
        isAjaxCall: false,

        saveVolunteerHours: {
          mission_id: "",
          date_volunteered: "",
          day_volunteered: "",
          notes: "",
          hours: "",
          minutes: "",
          documents: []
        }
      }
    },
    validations() {
      const isHourInvalid = this.timeEntryDefaultData.hours === ''
        || (this.timeEntryDefaultData.minutes === '0' && this.timeEntryDefaultData.hours === '0');

      const requiredHourValidation = isHourInvalid ? { required } : {};

      const isMinuteInvalid = this.timeEntryDefaultData.minutes === ''
        || (this.timeEntryDefaultData.minutes === '0' && this.timeEntryDefaultData.hours === '0');

      const requiredMinuteValidation = isMinuteInvalid ? { required } : {};

      return {
        timeEntryDefaultData: {
          hours: {
            required,
            numeric,
            between: between(0, 23),
            requiredHourValidation
          },
          minutes : {
            required,
            numeric,
            between: between(0, 59),
            requiredMinuteValidation
          },
          workDay: {
            required
          },
          dateVolunteered: {
            required
          }
        }
      }
    },
    methods: {
      hourChange() {
        if(this.timeEntryDefaultData.hours == "00") {
          this.timeEntryDefaultData.hours = Math.floor(this.timeEntryDefaultData.hours).toString()
        }
      },
      minuteChange() {
        if(this.timeEntryDefaultData.minutes == "00") {
          this.timeEntryDefaultData.minutes = Math.floor(this.timeEntryDefaultData.minutes).toString()
        }
      },
      dateChange() {
        this.$emit('changeDocument', this.timeEntryDefaultData.dateVolunteered)
      },
      inputUpdate(files) {
        let allowedFileTypes = constants.FILE_ALLOWED_FILE_TYPES
        this.fileError = '';
        let error = false
        let duplicateUpload = false
        let latestUpload = files[files.length - 1];
        let latestUploadIndex = files.length - 1;
        let latestUploadName = latestUpload.name
        let latestUploadSize = latestUpload.size
        let latestUploadType = latestUpload.type

        files.filter((data, index) => {
          let fileName = data.name.split('.');
          fileName = fileName[fileName.length - 1].toLowerCase()
          if (!allowedFileTypes.includes(fileName)) {
            this.fileError = this.languageData.errors.invalid_file_type
            error = true
          } else {
            if (data.size > constants.FILE_MAX_SIZE_BYTE) {
              this.fileError = this.languageData.errors.file_max_size
              error = true
            }
          }
          if (index != files.length - 1) {
            if (
              data.name == latestUploadName
              && data.size == latestUploadSize
              && data.type == latestUploadType
            ) {
              this.fileError = this.languageData.errors.file_already_uploaded
              error = true
              duplicateUpload = true;
            }
          }

          if(error == true) {
            if(duplicateUpload == true) {
                files.splice(latestUploadIndex, 1)
            } else {
                files.splice(index, 1)
            }
          }
        });
      },
      updateWorkday(value) {
        let selectedData = {
          'selectedVal': '',
          'fieldId': ''
        }
        selectedData['selectedVal'] = value.selectedVal
        selectedData['fieldId'] = 'workday';
        this.timeEntryDefaultData.workDay = value.selectedId
        this.$emit("updateCall", selectedData)
      },
      updateHours(value) {
        let selectedData = {
          'selectedVal': '',
          'fieldId': ''
        }
        selectedData['selectedVal'] = value.selectedVal
        selectedData['fieldId'] = 'hours';
        this.timeEntryDefaultData.hours = value.selectedId
        this.$emit("updateCall", selectedData)
      },
      updateMinutes(value) {
        let selectedData = {
          'selectedVal': '',
          'fieldId': ''
        }
        selectedData['selectedVal'] = value.selectedVal
        selectedData['fieldId'] = 'minutes';
        this.timeEntryDefaultData.minutes = value.selectedId
        this.$emit("updateCall", selectedData)
      },
      saveTimeHours() {
        this.submitted = true;
        this.$v.$touch();
        if (this.$v.$invalid) {
          return;
        }

        if ((this.timeEntryDefaultData.hours == '' || this.timeEntryDefaultData.hours == '0') &&
          (this.timeEntryDefaultData.minutes == "0" || this.timeEntryDefaultData.minutes == "")) {
          return
        }

        this.fileError = '';
        this.isAjaxCall = true;
        const formData = new FormData();
        let fileData = []
        let file = this.fileArray;
        if (file) {
          file.filter((fileItem) => {
            fileData.push(fileItem.file);
            formData.append('documents[]', fileItem.file);
          })
        }
        let volunteeredDate = moment(String(this.timeEntryDefaultData.dateVolunteered)).format('YYYY-MM-DD');
        let hours = this.timeEntryDefaultData.hours == '' ? 0 : this.timeEntryDefaultData.hours
        let minutes = this.timeEntryDefaultData.minutes == '' ? 0 : this.timeEntryDefaultData.minutes
        formData.append('mission_id', this.timeEntryDefaultData.missionId);
        formData.append('date_volunteered', volunteeredDate);
        formData.append('day_volunteered', this.timeEntryDefaultData.workDay);
        formData.append('notes', this.timeEntryDefaultData.notes);
        formData.append('hours', parseInt(hours, 10));
        formData.append('minutes', parseInt(minutes, 10));

        addVolunteerEntry(formData).then(response => {
          if (response.error === true) {
            this.message = null;
            this.showErrorDiv = true
            this.classVariant = 'danger'
            //set error msg
            this.message = response.message
          } else {
            this.message = null;
            this.showErrorDiv = true
            this.classVariant = 'success'
            //set error msg
            this.message = response.message
            this.submitted = false;

            this.$emit("changeTimeSheetView",volunteeredDate);
            this.$emit("getTimeSheetData");

            setTimeout(()  => {
              this.$refs.timeHoursModal.hide();
              this.hideModal();
            }, 700);
          }
          this.isAjaxCall = false;
        })

      },
      deleteFile(timeSheetId, documentId) {
        let deletFile = {
          'timesheet_id': timeSheetId,
          'document_id': documentId
        }

        removeDocument(deletFile).then(response => {
          if (response) {
            this.message = null;
            this.showErrorDiv = true
            this.classVariant = 'success'
            this.message = this.languageData.errors.file_deleted_successfully
            this.timeEntryDefaultData.documents.filter((document, index) => {
              if (document.timesheet_document_id == documentId && document.timesheet_id ==
                timeSheetId) {
                this.timeEntryDefaultData.documents.splice(index, 1);
              }
            });
          } else {
            this.message = null;
            this.showErrorDiv = true
            this.classVariant = 'danger'
            this.message = response
          }

        })
      },
      hideModal() {
        this.submitted = false;
        this.showErrorDiv = false
        this.fileError = ''
        this.fileArray = [];
        this.$emit("resetModal");
        document.querySelector('html').classList.remove('modal-open');
      }

    },
    created() {
      this.languageData = JSON.parse(store.state.languageLabel)
      this.isFileUploadDisplay = this.settingEnabled(constants.TIMESHEET_DOCUMENT_UPLOAD)
      this.lang = (store.state.defaultLanguage).toLowerCase();
    }
  };
</script>