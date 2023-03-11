<template>
<div>
    <ul class="profile-navs" v-if="isUserProfileComplete">
        <li v-bind:class="{ active: isProfileActive }">
            <b-link href="#" :title="languageData.label.profile" @click="redirectToPage('my-account')">
                <i class="profile-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 27 26" width="27" height="26">
                        <g id="Main Content">
                            <g id="Group 14 copy">
                                <path id="Forma 1 copy" class="shp0" d="M13.96 15.03C17.8 15.03 20.92 11.88 20.92 8C20.92 4.12 17.8 1 13.96 1C10.11 1 7 4.15 7 8C7 11.86 10.11 15.03 13.96 15.03ZM13.96 2.83C16.77 2.83 19.08 5.16 19.08 8C19.08 10.85 16.77 13.18 13.96 13.18C11.14 13.18 8.83 10.85 8.83 8C8.83 5.16 11.14 2.83 13.96 2.83ZM1.92 26L26.08 26C26.59 26 27 25.59 27 25.07C27 20.22 23.09 16.26 18.27 16.26L9.73 16.26C4.93 16.26 1 20.2 1 25.07C1 25.59 1.41 26 1.92 26ZM9.73 18.11L18.27 18.11C21.77 18.11 24.65 20.74 25.1 24.15L2.9 24.15C3.35 20.76 6.23 18.11 9.73 18.11Z" />
                            </g>
                        </g>
                    </svg>
                </i>
                {{ languageData.label.profile }}
            </b-link>
        </li>
        <li v-bind:class="{ active: isSettingActive }">
            <b-link href="#" :title="languageData.label.settings" @click="redirectToPage('setting')">
                <i class="settings-icon">
                    <svg id="Layer_1" enable-background="new 0 0 512 512" height="512" viewBox="0 0 512 512" width="512" xmlns="http://www.w3.org/2000/svg">
                        <path d="m272.066 512h-32.133c-25.989 0-47.134-21.144-47.134-47.133v-10.871c-11.049-3.53-21.784-7.986-32.097-13.323l-7.704 7.704c-18.659 18.682-48.548 18.134-66.665-.007l-22.711-22.71c-18.149-18.129-18.671-48.008.006-66.665l7.698-7.698c-5.337-10.313-9.792-21.046-13.323-32.097h-10.87c-25.988 0-47.133-21.144-47.133-47.133v-32.134c0-25.989 21.145-47.133 47.134-47.133h10.87c3.531-11.05 7.986-21.784 13.323-32.097l-7.704-7.703c-18.666-18.646-18.151-48.528.006-66.665l22.713-22.712c18.159-18.184 48.041-18.638 66.664.006l7.697 7.697c10.313-5.336 21.048-9.792 32.097-13.323v-10.87c0-25.989 21.144-47.133 47.134-47.133h32.133c25.989 0 47.133 21.144 47.133 47.133v10.871c11.049 3.53 21.784 7.986 32.097 13.323l7.704-7.704c18.659-18.682 48.548-18.134 66.665.007l22.711 22.71c18.149 18.129 18.671 48.008-.006 66.665l-7.698 7.698c5.337 10.313 9.792 21.046 13.323 32.097h10.87c25.989 0 47.134 21.144 47.134 47.133v32.134c0 25.989-21.145 47.133-47.134 47.133h-10.87c-3.531 11.05-7.986 21.784-13.323 32.097l7.704 7.704c18.666 18.646 18.151 48.528-.006 66.665l-22.713 22.712c-18.159 18.184-48.041 18.638-66.664-.006l-7.697-7.697c-10.313 5.336-21.048 9.792-32.097 13.323v10.871c0 25.987-21.144 47.131-47.134 47.131zm-106.349-102.83c14.327 8.473 29.747 14.874 45.831 19.025 6.624 1.709 11.252 7.683 11.252 14.524v22.148c0 9.447 7.687 17.133 17.134 17.133h32.133c9.447 0 17.134-7.686 17.134-17.133v-22.148c0-6.841 4.628-12.815 11.252-14.524 16.084-4.151 31.504-10.552 45.831-19.025 5.895-3.486 13.4-2.538 18.243 2.305l15.688 15.689c6.764 6.772 17.626 6.615 24.224.007l22.727-22.726c6.582-6.574 6.802-17.438.006-24.225l-15.695-15.695c-4.842-4.842-5.79-12.348-2.305-18.242 8.473-14.326 14.873-29.746 19.024-45.831 1.71-6.624 7.684-11.251 14.524-11.251h22.147c9.447 0 17.134-7.686 17.134-17.133v-32.134c0-9.447-7.687-17.133-17.134-17.133h-22.147c-6.841 0-12.814-4.628-14.524-11.251-4.151-16.085-10.552-31.505-19.024-45.831-3.485-5.894-2.537-13.4 2.305-18.242l15.689-15.689c6.782-6.774 6.605-17.634.006-24.225l-22.725-22.725c-6.587-6.596-17.451-6.789-24.225-.006l-15.694 15.695c-4.842 4.843-12.35 5.791-18.243 2.305-14.327-8.473-29.747-14.874-45.831-19.025-6.624-1.709-11.252-7.683-11.252-14.524v-22.15c0-9.447-7.687-17.133-17.134-17.133h-32.133c-9.447 0-17.134 7.686-17.134 17.133v22.148c0 6.841-4.628 12.815-11.252 14.524-16.084 4.151-31.504 10.552-45.831 19.025-5.896 3.485-13.401 2.537-18.243-2.305l-15.688-15.689c-6.764-6.772-17.627-6.615-24.224-.007l-22.727 22.726c-6.582 6.574-6.802 17.437-.006 24.225l15.695 15.695c4.842 4.842 5.79 12.348 2.305 18.242-8.473 14.326-14.873 29.746-19.024 45.831-1.71 6.624-7.684 11.251-14.524 11.251h-22.148c-9.447.001-17.134 7.687-17.134 17.134v32.134c0 9.447 7.687 17.133 17.134 17.133h22.147c6.841 0 12.814 4.628 14.524 11.251 4.151 16.085 10.552 31.505 19.024 45.831 3.485 5.894 2.537 13.4-2.305 18.242l-15.689 15.689c-6.782 6.774-6.605 17.634-.006 24.225l22.725 22.725c6.587 6.596 17.451 6.789 24.225.006l15.694-15.695c3.568-3.567 10.991-6.594 18.244-2.304z" />
                        <path d="m256 367.4c-61.427 0-111.4-49.974-111.4-111.4s49.973-111.4 111.4-111.4 111.4 49.974 111.4 111.4-49.973 111.4-111.4 111.4zm0-192.8c-44.885 0-81.4 36.516-81.4 81.4s36.516 81.4 81.4 81.4 81.4-36.516 81.4-81.4-36.515-81.4-81.4-81.4z" />
                    </svg>
                </i>
                {{ languageData.label.settings }}
            </b-link>
        </li>
    </ul>
</div>
</template>

<script>
import constants from '../constant';
import store from '../store';
import {
  ModelSelect
} from 'vue-search-select';
export default {
  components: {
    ModelSelect,
  },
  name: 'Breadcrumb',
  data() {
    return {
      isUserProfileComplete: 1,
      languageData: [],
      timeList: [],
      selectTimeZone: '',
      isProfileActive: false,
      isSettingActive: false,
      isPaymentActive: false,
      isRecurringPaymentActive: false,
      isPreviewProfileActive: false
    };
  },
  methods: {
    updateTime(value) {
      this.selectTimeZone = value;
    },
    redirectToPage(pageName) {
      if (this.$route.name !== pageName) {
        this.$router.push({
          path: pageName
        })
      } else {
        return false;
      }
    },
    settingModalShow() {
      return false;
      this.$refs.settingsModal.show();
      const settingPath = `${this.$route.path}#setting`;
      this.$router.push(settingPath)
      this.isSettingActive = true
      this.isProfileActive = false
      this.isPaymentActive = false
      this.isRecurringPaymentActive = false
    },
    closeSetting() {
      this.$router.push(this.$route.path)
      this.isSettingActive = false;
      if (this.$route.path == '/my-account') {
        this.isProfileActive = true
      }
      if (this.$route.path == '/payment') {
        this.isPaymentActive = true
      }
      if (this.$route.path == '/recurring-payment') {
        this.isRecurringPaymentActive = true
      }
    }
  },
  created() {
    this.languageData = JSON.parse(store.state.languageLabel);
    if (this.$route.path == '/my-account') {
      this.isProfileActive = true
    }
    if (this.$route.path == '/payment') {
      this.isPaymentActive = true
    }
    if (this.$route.path == '/recurring-payment') {
      this.isRecurringPaymentActive = true
    }
    if (this.$route.path == '/setting') {
      this.isSettingActive = true
    }
    this.isUserProfileComplete = store.state.isProfileComplete;
  }
};
</script>
