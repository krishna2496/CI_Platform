<template>
  <div class="error-page inner-pages">
    <main>
      <b-container>
        <div class="error-page-wrap">
          <div class="error-img" :style="{backgroundImage: 'url('+img+')'}"></div>
          <div class="error-content">
            <i class="glyphicon glyphicon-th-list"></i>

            <!-- From Saml SSO -->
            <template v-if="saml && error">
              <h4 class="text-danger">{{ error }}</h4>
            </template>

            <template v-if="saml && errors.length">
              <h4 class="text-danger">{{ languageData.errors.invalid_saml_setting }}</h4>
              <span class="errors" v-for="error in errors">{{ error }}</span>
            </template>

            <!-- From Google OAuth - no need for error translations -->
            <template v-if="google && errors.length">
              <span class="errors" v-for="error in errors">
                <template v-if="error === 'GOOGLE_AUTH_UNAUTHORIZE'">
                  <span>{{ 'Unauthorize access' }}</span>
                </template>
                <template v-if="error === 'GOOGLE_AUTH_ERROR'">
                  <span>{{ 'Failed to authenticate' }}</span>
                </template>
                <template v-if="error === 'INVALID_OPTIMY_EMAIL'">
                  <span>{{ 'Invalid optimy email' }}</span>
                </template>
              </span>
            </template>

            <div class="btn-row">
              <b-link class="btn btn-bordersecondary icon-btn"
                :title="action.label" :to="action.url">
                <span>{{action.label}}</span>
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
        </div>
      </b-container>
    </main>
  </div>
</template>

<script>
import store from "../../../store";

export default {
  name: "SsoErrorHandler",

  data() {
    return {
      img: require("@/assets/images/danger.png"),
      languageData: [],
      errors: []
    };
  },
  created() {
    this.saml = this.$route.query.source === 'saml';
    this.google = this.$route.query.source === 'google';
    if (this.$route.query.error) {
      this.error = this.$route.query.error;
    }
    if (this.$route.query.errors) {
      this.errors = this.$route.query.errors.split(',');
    }

    this.languageData = JSON.parse(store.state.languageLabel);

    this.action = this.$route.query.action && this.$route.query.action === 'login'
      ? {
          label: this.languageData.label.go_to_login_page,
          url: '/',
        }
      : {
          label: this.languageData.label.go_to_home_page,
          url: '/home',
        };
  },
};

</script>

<style scoped>
.errors {
  display: block;
  font-size: 1em;
}
</style>
