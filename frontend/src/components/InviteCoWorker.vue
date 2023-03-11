<template>
<b-modal @hidden="hideModal" ref="userDetailModal" :modal-class="['userdetail-modal']" size="lg" hide-footer>
    <template slot="modal-header" slot-scope="{ close }">
        <i class="close" @click="close()" v-b-tooltip.hover :title="labels.close"></i>
        <h5 class="modal-title"> {{ labels.search_user }} </h5>
    </template>
    <b-alert show :variant="classVariant" dismissible v-model="showErrorDiv">
        {{ message }}
    </b-alert>

    <div class="autocomplete-control">
        <div class="autosuggest-container">
            <VueAutosuggest ref="autosuggest" name="user" :suggestions="filteredOptions" @input="onInputChange" @selected="onSelected" :get-suggestion-value="getSuggestionValue" :input-props="{
            id:'autosuggest__input',
            placeholder:placeholders.search_user,
            ref:'inputAutoSuggest'
          }">
                <div slot-scope="{suggestion}">
                    <img :src="suggestion.item.avatar" />
                    <div>
                        {{suggestion.item.first_name}} {{suggestion.item.last_name}}
                    </div>
                </div>
            </VueAutosuggest>
        </div>
    </div>

    <b-form>
        <div class="btn-wrap">
            <b-button @click="$refs.userDetailModal.hide()" class="btn-borderprimary">
                {{ labels.close }}
            </b-button>
            <b-button class="btn-bordersecondary" @click="inviteColleagues" ref="autosuggestSubmit" v-bind:disabled="submitDisable">
                {{ labels.submit }}
            </b-button>
        </div>
    </b-form>
</b-modal>
</template>

<script>
import {
    searchUser,
    inviteColleague,
    storyInviteColleague
} from "../services/service";
import {
    VueAutosuggest
} from "vue-autosuggest";
import store from "@/store";

const STORY_ENTITY_TYPE = 'STORY';
const MISSION_ENTITY_TYPE = 'MISSION';

export default {
    name: "InviteCoWorker",
    components: {
        VueAutosuggest
    },
    props: {
        entityType: {
            type: String,
            required: true
        },
        entityId: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            classVariant: 'success',
            labels: [],
            message: null,
            placeholders: [],
            selected: null,
            showErrorDiv: false,
            submitDisable: true,
            users: []
        }

    },
    computed: {
        filteredOptions() {
            return [{
                data: this.users
            }];
        }
    },
    methods: {
        /*
         * Sets display value of suggestion in Invite Co-worker modal
         */
        getSuggestionValue(suggestion) {
            let firstName = suggestion.item.first_name;
            let lastName = suggestion.item.last_name;
            return firstName + " " + lastName;
        },
        /*
         * Hide Invite a co-worker modal
         */
        hideModal() {
            this.submitDisable = true;
            this.invitedUserId = null;
            this.selected = null;
            this.users = [];
            const cardHeader = document.querySelectorAll(
                ".card-grid .card .card-header"
            );
            if (cardHeader != null) {
                const cardBody = document.querySelectorAll(".card-grid .card .card-body");

                cardBody.forEach(function (cardBodyElem) {
                    cardBodyElem.style.transform = "translateY(0)";
                });
                cardHeader.forEach(function (cardHeaderElem) {
                    cardHeaderElem.style.transform = "translateY(0)";
                });

                const cardInner = document.querySelectorAll(".card-grid .card-inner");
                cardInner.forEach(function (cardInnerElem) {
                    cardInnerElem.classList.remove("active");
                });
            }
        },
        /*
         * Invite colleague api call
         */
        inviteColleagues() {
            let invitation = {
                to_user_id: this.invitedUserId
            }

            let promise;
            switch (this.entityType) {
                case MISSION_ENTITY_TYPE:
                    invitation.mission_id = this.entityId;
                    promise = inviteColleague(invitation);
                    break;
                case STORY_ENTITY_TYPE:
                    invitation.story_id = this.entityId;
                    promise = storyInviteColleague(invitation);
                    break;
                default:
                    return;
            }

            promise.then(response => {
                this.submitDisable = true;

                if (response.error == true) {
                    this.classVariant = "danger";
                    this.message = response.message;
                    this.$refs.autosuggest.$data.currentIndex = null;
                    this.$refs.autosuggest.$data.internalValue = "";
                    this.showErrorDiv = true;

                } else {
                    this.selected = null;
                    this.invitedUserId = null;
                    this.$refs.autosuggest.$data.currentIndex = null;
                    this.$refs.autosuggest.$data.internalValue = "";
                    this.classVariant = "success";
                    this.message = response.message;
                    this.showErrorDiv = true;
                }
            });
        },
        /*
         * Autocomplete search for users
         */
        onInputChange(input) {
            this.submitDisable = true;
            if (input.length > 2) {
                searchUser(input).then(users => {
                    this.users = users
                });
            } else {
                // reset to empty when user did not input at least 3 chars
                this.users = [];
            }
        },
        /*
         * Event triggered when user selects co-worker to invite
         */
        onSelected(user) {
            if (user) {
                this.selected = user.item;
                this.submitDisable = false;
                this.invitedUserId = user.item.user_id;
                this.users = [];
            }
        },
        /*
         * Opens the modal
         */
        show() {
            this.showErrorDiv = false;
            this.message = null;
            this.$refs.userDetailModal.show();

            setTimeout(() => {
                this.$refs.autosuggest.$refs.inputAutoSuggest.focus();
                let input = document.getElementById("autosuggest__input");

                input.addEventListener("keyup", (event) => {
                    if (event.keyCode === 13 && !this.submitDisable) {
                        event.preventDefault();
                        this.inviteColleagues()
                    }
                });
            }, 100);
        }
    },
    created() {
        let translations = JSON.parse(store.state.languageLabel);
        this.labels = translations.label;
        this.placeholders = translations.placeholder;
    }
}
</script>

<style scoped>

</style>
