<template>
    <div>
        <div class="skillset-wrap">
            <b-button class="btn-borderprimary add-skill-btn" @click="showSkillModal">
                {{languageData.label.add_skills}}
            </b-button>
            <b-modal centered ref="skillModal" :modal-class="myclass" hide-footer @hidden="hideModal">
                <template slot="modal-header" slot-scope="{ close }">
                    <i class="close" @click="close()" v-b-tooltip.hover :title="languageData.label.close"></i>
                    <h5 class="modal-title">{{languageData.label.add_your_skills}}</h5>
                </template>
                <b-alert show variant="danger" dismissible v-model="showErrorDiv">
                    {{ message }}
                </b-alert>
                <div class="multiselect-options">
                    <div class="options-col" data-simplebar>

                        <ul class="fromlist-group">
                            <li v-for="(fromitem, index) in fromList" :key="index" :id="fromitem.id">
                                <span>{{fromitem.name}}</span>
                                <b-button @click="addToList(fromitem.id)">
                                    <img :src="$store.state.imagePath+'/assets/images/plus-ic.svg'"
                                         :title="languageData.label.add" alt="plus icon sss" />
                                </b-button>
                            </li>
                        </ul>
                    </div>
                    <div class="options-col" data-simplebar>
                        <ul class="tolist-group">
                            <li v-for="(toitem, idx) in toList" :id="toitem.id" :key="idx">
                                <span>{{toitem.name}}</span>
                                <b-button @click="removeFromToList(toitem.id)">
                                    <img :src="$store.state.imagePath+'/assets/images/cross-ic.svg'"
                                         :title="languageData.label.remove" alt="cross icon" />
                                </b-button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="btn-wrap">
                    <b-button @click="resetSkill" class="btn-borderprimary" v-bind:class="{disabled:resetButtonDisable}">{{languageData.label.reset}}</b-button>
                    <b-button @click="saveSkill" class="btn-bordersecondary">{{languageData.label.save}}</b-button>
                </div>
            </b-modal>

        </div>
    </div>
</template>
<script>
  import store from "../store";

  export default {
    name: "Multiselect",
    props: {
      fromList: Array,
      toList: Array,
    },
    data() {
      return {
        languageData: [],
        selectedListIndexs: [],
        updated: false,
        selectList: [],
        myclass: ["skill-modal"],
        showErrorDiv: false,
        message: '',
        closeClick: true,
        dataFromList: this.fromList,
        dataToList: this.toList,
        resetButtonDisable:true
      };
    },

    methods: {
      handleclick() {
        let fromListGroup = document.querySelectorAll(".fromlist-group li");
        for (let i = 0; i < fromListGroup.length; ++i) {
          fromListGroup[i].addEventListener("click", this.handleSelected);
        }
      },
      showSkillModal: function () {
        this.toList.filter((toItem) => {
          this.fromList.filter( (fromItem, fromIndex) => {
            if (toItem.id == fromItem.id) {
              this.fromList.splice(fromIndex, 1);
            }
          });
        });
        this.$refs.skillModal.show();
      },
      hideModal() {
        if (this.closeClick) {
          if (localStorage.getItem("currentSkill") !== null && localStorage.getItem("currentFromSkill") !==
            null) {
            this.$emit("resetPreviousData");
          } else {
            this.dataFromList = [];
            this.dataToList = [];
            this.$emit("resetData");
          }
        } else {
          this.toList = this.toList;
        }
        this.resetButtonDisable = true
      },
      // Add to list
      addToList(id) {
        this.closeClick = true;
        if (this.toList.length <= 14) {
          let filteredObj = this.fromList.filter( (item, i) => {
            if (item.id == id) {
              this.fromList.splice(i, 1);
              return item;
            }
          });
          this.toList.push(filteredObj[0])
          this.showErrorDiv = false
        } else {
          this.showErrorDiv = true,
            this.message = this.languageData.errors.max_skill_selection
        }

        this.resetButtonDisable = false
      },
      // Remove data from to list
      removeFromToList(id) {
        this.closeClick = true;
        let filteredObj = this.toList.filter( (item, i) => {
          if (item.id == id) {
            this.toList.splice(i, 1);
            return item;
          }
        });
        this.fromList.push(filteredObj[0])
        this.fromList.sort();
        this.fromList.sort(function (first, next) {
          first = first.name;
          next = next.name;
          return first < next ? -1 : (first > next ? 1 : 0);
        });
        this.resetButtonDisable = false
      },
      resetSkill() {
        if (localStorage.getItem("currentSkill") !== null && localStorage.getItem("currentFromSkill") !==
          null) {
          this.$emit("resetPreviousData");
        } else {
          this.dataFromList = [];
          this.dataToList = [];
          this.$emit("resetData");
        }
        this.closeClick = false;
      },
      saveSkill() {
        store.commit("saveCurrentSkill", this.toList)
        store.commit("saveCurrentFromSkill", this.fromList)
        this.$emit("saveSkillData");
        this.showErrorDiv = false
        this.$refs.skillModal.hide();
        this.closeClick = false;
      }
    },
    created() {
      this.languageData = JSON.parse(store.state.languageLabel);
    }
  };
</script>