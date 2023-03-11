<template>
    <div class="cms-page inner-pages">
        <header>
            <ThePrimaryHeader></ThePrimaryHeader>
        </header>

        <main v-if="isPolicyDataSet">
            <b-container>

                <h1>
                    {{footerItems.pages[0].title}}
                </h1>
                <b-row>
                    <b-col lg="3" md="4" class="cms-nav">
                        <b-nav>
                            <b-nav-item v-for="(item, key) in footerItems.pages[0].sections"
                                        :key=key
                                        v-scroll-to="{ el: '#block-'+key , offset :getOffset}">
                                {{item.title}}
                            </b-nav-item>
                        </b-nav>
                    </b-col>
                    <b-col lg="9" md="8">
                        <div class="cms-content cms-accordian" id="cms-content">
                            <div class="cms-content-block" v-for="(item, key) in footerItems.pages[0].sections"
                                 :key=key
                                 :id="'block-'+key">
                                <h2 v-b-toggle="'content-' + key" class="accordian-title">{{item.title}}</h2>
                                <b-collapse :id="'content-'+key" class="accordian-content" accordion="my-accordion"
                                            visible v-html="item.description">
                                </b-collapse>
                            </div>
                        </div>
                    </b-col>
                </b-row>
            </b-container>
        </main>
        <footer>
            <TheSecondaryFooter v-if="isPolicyDataSet"></TheSecondaryFooter>
        </footer>
    </div>
</template>
<script>
  import constants from '../constant';
  import ThePrimaryHeader from "../components/Layouts/ThePrimaryHeader";
  import TheSecondaryFooter from "../components/Layouts/TheSecondaryFooter";
  import {
    policyDetail
  } from '../services/service';

  export default {
    components: {
      ThePrimaryHeader,
      TheSecondaryFooter
    },
    data() {
      return {
        footerItems: [],
        isPolicyDataSet: false,
        slug: this.$route.params.policyPage,
        isPolicyEnabled: true
      };
    },
    mounted() {},
    methods: {
      // left menu sticky function
      handleScroll() {
        let nav_ = document.querySelector(".cms-nav");
        if (!nav_) return;
        
        let navTop = nav_.offsetTop;
        let screenHeight = document.body.clientHeight;
        let headerHeight = document.querySelector("header").offsetHeight;
        let footerHeight = document.querySelector("footer").offsetHeight
        let windowTop = window.pageYOffset + (headerHeight + 1);
        let navHeight = document.querySelector(".cms-nav .nav").offsetHeight;
        let navBottom = navHeight + navTop;
        let footerTop = document.querySelector("footer").getBoundingClientRect()
          .top;

        let contentHeight = document.querySelector('.cms-content').offsetHeight - parseInt(window
          .getComputedStyle(document.querySelector('.cms-content'), null).getPropertyValue(
            "padding-bottom"));
        let scroll_height = screenHeight - navTop - footerHeight + headerHeight;
        if (screen.width > 767 && screen.width < 1025) {
          if (contentHeight > scroll_height) {
            if (windowTop > navTop) {
              nav_.classList.add("fixed");
            } else {
              nav_.classList.remove("fixed");
            }
          } else {
            nav_.classList.remove("fixed");
          }
        }

        if (screen.width > 1024) {
          if (windowTop > navTop) {
            if (navBottom >= footerTop + 100) {
              nav_.classList.add("absolute");
              nav_.classList.remove("fixed");
            } else {
              nav_.classList.add("fixed");
              nav_.classList.remove("absolute");
            }
          } else {
            nav_.classList.remove("fixed");
          }
        }

        let link_list = document.querySelectorAll(".cms-nav .nav-item");
        let block_list = document.querySelectorAll(".cms-content-block");
        for (let i = 0; i < block_list.length; ++i) {
          if (block_list[i].getBoundingClientRect().top < headerHeight + 42) {
            for (let j = 0; j < link_list.length; j++) {
              let link_siblings = link_list[j].parentNode.childNodes;
              for (let k = 0; k < link_siblings.length; ++k) {
                link_siblings[k].childNodes[0].classList.remove("active");
              }
              link_siblings[i].childNodes[0].classList.add("active");
            }
          }
        }
      },

      getOffset() {
        let headerHeight = document.querySelector("header").offsetHeight;
        return -headerHeight;
      },
      //List cms pages
      async policyListing(slug) {
        // policyDetail


        await policyDetail(slug).then(response => {
          if (response.error == false) {
            this.footerItems = response.data;
            this.isPolicyDataSet = true
          } else {
            this.$router.push({
              name: '404'
            });
          }
        });
      }
    },
    watch: {
      $route(to, from) {
        this.footerItems = [];
        this.isPolicyDataSet = false;
        this.slug = this.$route.params.policyPage;
        this.policyListing(this.$route.params.policyPage);
      }
    },
    created() {
      this.isPolicyEnabled = this.settingEnabled(constants.POLICIES_ENABLED);
      if(!this.isPolicyEnabled) {
        this.$router.push('/home')
      }
      this.policyListing(this.slug);
      window.addEventListener("scroll", this.handleScroll);
      window.addEventListener("resize", this.handleScroll);
      window.addEventListener("resize", this.getOffset);

    },

    destroyed() {
      window.removeEventListener("scroll", this.handleScroll);
      window.removeEventListener("resize", this.handleScroll);
      window.removeEventListener("resize", this.getOffset);
    }
  };
</script>
<style lang="scss">
.cms-content .accordian-title {
  outline: none;

  @media (min-width: 1025px) {
    cursor: text;
  }
}
</style>