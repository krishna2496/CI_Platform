<template>
    <div class="row custom-field" v-if="CustomFieldList != null && CustomFieldList.length > 0">
        <b-col md="12" v-for="(item,key) in optionList" :key=key>
            <b-form-group v-if="item.type == 'drop-down'">
                <label>{{item.translations.name}}
                    <span v-if="item.is_mandatory == 1">*</span>
                </label>
                <AppCustomFieldDropdown v-model="customFeildData[item.field_id]"
                                        :defaultText="defaultValue[item.field_id]" :optionList="getArrayValue(item.translations.values)"
                                        :errorClass="getErrorClass(item.field_id)" :validstate="getErrorState(item.field_id)"
                                        :fieldId="item.field_id" translationEnable="false" @updateCall="updateCustomDropDown" />
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    {{item.translations.name}} {{ languageData.errors.field_required }}
                </div>
            </b-form-group>

            <b-form-group v-if="item.type == 'radio'">
                <label>{{item.translations.name}}
                  <span v-if="item.is_mandatory == 1">*</span>
                </label>
                <b-form-radio-group
                  class="container custom-group"
                  :id='`radio-${item.field_id}`'
                  v-model="customFeildData[item.field_id]"
                  :class="{ 'is-invalid': getErrorClass(item.field_id)}"
                  :validstate="getErrorState(item.field_id)"
                  @change="updateChanges" :name="item.translations.name"
                >
                  <b-row
                    cols="1"
                    :cols-md="getOptionColumnCount(item.translations.values)"
                  >
                    <b-col
                      v-for="(option, index) in getRadioArrayValue(item.translations.values)"
                      :key="index"
                    >
                      <label class="d-inline-block p-1">
                        <b-form-radio :value="option.value">
                          {{ option.text }}
                        </b-form-radio>
                      </label>
                    </b-col>
                  </b-row>
                </b-form-radio-group>
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    {{item.translations.name}} {{ languageData.errors.field_required }}
                </div>
            </b-form-group>

            <b-form-group v-if="item.type == 'checkbox'">
                <label>{{item.translations.name}}
                  <span v-if="item.is_mandatory == 1">*</span>
                </label>
                <b-form-checkbox-group
                  class="container custom-group"
                  :id='`checkbox-id-${item.field_id}`'
                  v-model="customFeildData[item.field_id]"
                  name="checkbox-custom"
                  :class="{ 'is-invalid': getErrorClass(item.field_id)}"
                  :validstate="getErrorState(item.field_id)"
                  @input="updateChanges"
                >
                  <b-row
                    cols="1"
                    :cols-md="getOptionColumnCount(item.translations.values)"
                  >
                    <b-col
                      v-for="(option, index) in getRadioArrayValue(item.translations.values)"
                      :key="index"
                    >
                      <label class="d-inline-block p-1">
                        <b-form-checkbox :value="option.value">
                          {{ option.text }}
                        </b-form-checkbox>
                      </label>
                    </b-col>
                  </b-row>
                </b-form-checkbox-group>
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    {{item.translations.name}} {{ languageData.errors.field_required }}
                </div>
            </b-form-group>

            <b-form-group v-if="item.type == 'multiselect'">
                <label>{{item.translations.name}} <span v-if="item.is_mandatory == 1">*</span></label>
                <multiselect
                  v-model="multiSelectModel[item.field_id]"
                  :options="multiSelectOptions(item.field_id, item.translations.values)"
                  class="optimy-multiselect"
                  :multiple="true"
                  track-by="value"
                  :custom-label="customLabel"
                  :placeholder="defaultText"
                  :allow-empty="item.is_mandatory !== 1"
                  :class="{ 'is-invalid': getErrorClass(item.field_id) }"
                  :validstate="getErrorState(item.field_id)"
                  :close-on-select="false"
                  @select="addMultiSelect"
                  @remove="removeMultiSelect"
                ></multiselect>
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    {{item.translations.name}} {{ languageData.errors.field_required }}
                </div>
            </b-form-group>

            <b-form-group v-if="item.type == 'textarea'">
                <label>{{item.translations.name}}<span v-if="item.is_mandatory == 1">*</span></label>
                <b-form-textarea v-model.trim="customFeildData[item.field_id]" :id='`textarea-${item.field_id}`'
                                 :placeholder='`Enter ${item.translations.name}`' no-resize rows="3"
                                 :class="{ 'is-invalid': getErrorClass(item.field_id) }" :validstate="getErrorState(item.field_id)"
                                 @change="updateChanges">
                </b-form-textarea>
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    {{item.translations.name}} {{ languageData.errors.field_required }}
                </div>
            </b-form-group>

            <b-form-group v-if="item.type == 'text'">
                <label>{{item.translations.name}}<span v-if="item.is_mandatory == 1">*</span></label>
                <b-form-input v-model.trim="customFeildData[item.field_id]" @input="updateChanges"
                              :class="{ 'is-invalid': getErrorClass(item.field_id) }" :validstate="getErrorState(item.field_id)"
                              :placeholder='`Enter ${item.translations.name}`'></b-form-input>
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    {{item.translations.name}} {{ languageData.errors.field_required }}
                </div>
            </b-form-group>

            <b-form-group v-if="item.type == 'email'">
                <label>{{item.translations.name}} <span v-if="item.is_mandatory == 1">*</span></label>
                <b-form-input type="email" v-model.trim="customFeildData[item.field_id]" @input="updateChanges"
                              :class="{ 'is-invalid': getErrorClass(item.field_id) }" :validstate="getErrorState(item.field_id)"
                              :placeholder='`Enter ${item.translations.name}`'></b-form-input>
                <div v-if="getErrorClass(item.field_id)" class="invalid-feedback">
                    <span v-if="!$v.customFeildData[item.field_id].required">{{item.translations.name}}
                        {{ languageData.errors.field_required }}</span>
                    <span v-if="!$v.customFeildData[item.field_id].email">{{ languageData.errors.invalid_email }}</span>
                </div>
            </b-form-group>
        </b-col>
    </div>
    <div v-else>
    </div>
</template>
<script>
  import store from "../store";
  import AppCustomFieldDropdown from "../components/AppCustomFieldDropdown";
  import AppCustomCheckboxDropdown from "../components/AppCustomCheckboxDropdown";
  import {
    required,
    email
  } from 'vuelidate/lib/validators';
  import Multiselect from 'vue-multiselect';

  export default {
    components: {
      AppCustomFieldDropdown,
      AppCustomCheckboxDropdown,
      Multiselect
    },
    name: "CustomField",
    props: {
      optionList: Array,
      optionListValue: Array,
      isSubmit: Boolean
    },
    data() {
      return {
        CustomFieldList: this.optionList,
        CustomField: [],
        list: [],
        CustomFieldValidation: {},
        defaultText: "",
        customFeildData: {},
        submit: false,
        defaultValue: {},
        languageData: [],
        multiSelectModel: this.getSelectedItems(),
        multiSelectOptions: function(fieldId, data) {
          let optionData = [];
          if (data) {
            Object.keys(data).map(function (key) {
              let newData = data[key]
              Object.keys(newData).map(function (key) {
                optionData.push({
                  text: newData[key],
                  value: key,
                  fieldId: fieldId
                });
              });
            });
          }
          return optionData;
        }
      };
    },
    validations() {
      const validations = {
        customFeildData: {}
      };

      this.CustomFieldList.forEach(wrr => {
        if (wrr.is_mandatory == 1) {
          validations.customFeildData[wrr.field_id] = {
            required
          };
        } else {
          validations.customFeildData[wrr.field_id] = {};
        }

        if (wrr.type == "email") {
          if (wrr.is_mandatory == 1) {
            validations.customFeildData[wrr.field_id] = {
              required,
              email
            };
          } else {
            validations.customFeildData[wrr.field_id] = {
              email
            };
          }
        }

        switch (wrr.type) {
          case 'drop-down':
            this.$set(this.defaultValue, wrr.field_id, this.defaultText)
            for (let key in wrr.translations.values) {
                if (wrr.translations.values[key] && wrr.translations.values[key][wrr.user_custom_field_value]) {
                    this.$set(this.defaultValue, wrr.field_id, wrr.translations.values[key][wrr.user_custom_field_value])
                }
            }
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
            break;
          case 'text':
            this.$set(this.defaultValue, wrr.field_id, wrr.user_custom_field_value)
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
            break;
          case 'email':
            this.$set(this.defaultValue, wrr.field_id, wrr.user_custom_field_value)
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
            break;
          case 'textarea':
            this.$set(this.defaultValue, wrr.field_id, wrr.user_custom_field_value)
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
            break;
          case 'multiselect':
            if (wrr.translations.values[0] && wrr.translations.values[0][wrr
              .user_custom_field_value]) {
              this.$set(this.defaultValue, wrr.field_id, wrr.translations.values[0][wrr
                .user_custom_field_value
                ])

            } else {
              this.$set(this.defaultValue, wrr.field_id, "")
            }
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
            break;
          case 'radio':
            this.$set(this.defaultValue, wrr.field_id, wrr.user_custom_field_value)
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
            break;
          case 'checkbox':
            this.$set(this.defaultValue, wrr.field_id, wrr.user_custom_field_value)
            if (wrr.user_custom_field_value.toString().indexOf(",") !== -1) {
              this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value.split(
                ","))
            } else {
              if (wrr.user_custom_field_value.toString() != '') {
                this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value
                  .toString().split(","))
              } else {
                this.$set(this.customFeildData, wrr.field_id, [])
              }
            }
            break;
          default:
            this.$set(this.defaultValue, wrr.field_id, wrr.user_custom_field_value)
            this.$set(this.customFeildData, wrr.field_id, wrr.user_custom_field_value)
        }
      });
      return validations;
    },
    mounted() {
      this.validateCustomFieldValues();
    },
    methods: {
      validateCustomFieldValues() {
        this.CustomFieldList.map(customField => {
          let customFieldValue = `${customField.user_custom_field_value}`;
          if (customFieldValue === '' || customFieldValue === null) {
            return;
          }
          // Some custom field value are separated by comma, that is used in multiselect type
          customFieldValue = customFieldValue.split(',');


          if (!customField.translations.values) {
            /**
             * Remove the custom field value if the custom field is an open type 
             *  and it has UUID as its value. This means that the custom field is 
             *  previously a closed type, so we removed the value previously saved in it.
             */
            const values = customFieldValue.filter(value => {
              return this.isUUID(value) === true;
            });
            if (customFieldValue.length === values.length) {
              customField.user_custom_field_value = '';
            }
            return;
          }

          /**
           * Remove the custom field value if the custom field is a closed type 
           *  and the custom field value does not exists in the array of options. 
           *  This means that the custom field is previously an open type, 
           *  so we removed the value previously saved in it.
           */
          const customFieldKeys = customField.translations.values.map(data => {
            return Object.keys(data)[0];
          });

          const result = customFieldValue.filter(value => {
            return customFieldKeys.includes(value) === true;
          });
          
          if (result.length === 0) {
            customField.user_custom_field_value = '';
            return;
          }

          if (customFieldValue.length > 1 && (customField.type === 'radio' || customField.type === 'drop-down')) {
            customField.user_custom_field_value = customFieldValue[0];
          }
        });
      },
      customLabel(item){
        return `${item.text}`
      },
      getSelectedItems(){
        let options = [];
        let selectedItems = [];
        this.optionList.forEach((item) => {
          if(item.type == 'multiselect') {
            let arr = [];
            item.translations.values.forEach((a) => {
              Object.keys(a).forEach(function(k){
                arr[k] = a[k];
              })
            });
            options[item.field_id] = arr;

            if(item.user_custom_field_value != ''){
              let obj = [];
              item.user_custom_field_value.split(",").forEach(function(val){
                if (options[item.field_id][val]) {
                  obj.push({
                    text: options[item.field_id][val],
                    value: val,
                    fieldId: item.field_id
                  });
                }
              });
              selectedItems[item.field_id] = obj;
            }
          }
        });
        return selectedItems;
      },
      updateCustomDropDown(value) {
        this.customFeildData[value.fieldId] = value.selectedId
        this.defaultValue[value.fieldId] = value.selectedVal
        this.updateChanges();
      },
      addMultiSelect(data) {
        if(this.customFeildData[data.fieldId] == null || this.customFeildData[data.fieldId] == ''){
          this.customFeildData[data.fieldId] = data.value
        } else {
          let arr = this.customFeildData[data.fieldId].split(',')
          const index = arr.indexOf(data.value);
          if(index === -1){
            arr.push(data.value)
          }
          this.customFeildData[data.fieldId] = arr.join()
        }
        this.updateChanges();
      },
      removeMultiSelect(data) {
        let arr = this.customFeildData[data.fieldId].split(',')
        const index = arr.indexOf(data.value);
        if (index > -1) {
          arr.splice(index, 1);
        }

        if(arr.length == 0) {
          this.customFeildData[data.fieldId] = null
        } else {
          this.customFeildData[data.fieldId] = arr.join()
        }
        this.updateChanges();
      },
      changeMultiSelect(value) {
        this.customFeildData[value.fieldId] = value.selectedVal
        this.updateChanges();
      },
      getArrayValue(data) {
        let returnData = [];
        if (data) {
          Object.keys(data).map(function (key) {
            let newData = data[key]
            Object.keys(newData).map(function (key) {
              returnData.push({
                text: newData[key],
                value: key
              });
            });
          });
        }
        return returnData;
      },
      getErrorClass(id) {
        if (this.$v.customFeildData[id] && this.isSubmit == true) {
          return this.$v.customFeildData[id].$invalid
        } else {
          return false
        }
      },
      getErrorState(id) {
        if (this.$v.customFeildData[id]) {
          return this.$v.customFeildData[id].$invalid
        } else {
          return false
        }
      },
      getRadioArrayValue(data) {
        let radioData = [];
        if (data) {
          Object.keys(data).map(function (key) {
            let newData = data[key]
            Object.keys(newData).map(function (key) {
              radioData.push({
                text: newData[key],
                value: key
              });
            });
          });
        }
        return radioData;
      },
      getSelectedItem(id) {
        let selectedDataArray = [];
        let selectedData = this.$v.customFeildData[id].$model;
        if (selectedData != '') {
          let selectedString = selectedData.toString()
          selectedDataArray = selectedString.split(",");
        }
        return selectedDataArray
      },
      updateChanges() {
        this.$emit("detectChangeInCustomFeild", this.customFeildData);
      },
      isUUID(value) {
        const pattern = /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/;
        return pattern.test(value);
      },
      getOptionColumnCount (options) {
        if (options.length > 5) {
          const max = 35;
          const withLongText = options.some(option => {
            const text = option[Object.keys(option)];
            return text && text.length > max;
          })
          if (!withLongText) return 2;
        }

        return 1;
      }
    },
    updated() {},
    created() {
      this.languageData = JSON.parse(store.state.languageLabel);
      this.defaultText = this.languageData.label.please_select
    }
  };
</script>