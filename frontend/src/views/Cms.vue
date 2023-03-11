<template>
    <div class="cms-page inner-pages">
        <header>
            <ThePrimaryHeader></ThePrimaryHeader>
        </header>

        <main v-if="isDynamicFooterItemsSet">
            <b-container>
                <h1>
                    {{footerItems.title}}
                </h1>
                <b-row>
                    <b-col lg="3" md="4" class="cms-nav">
                        <b-nav>
                            <b-nav-item v-for="(item,key) in footerItems.sections"
                                        :key=key
                                        v-scroll-to="{ el: '#block-'+key , offset :getOffset}">
                                {{item.title}}
                            </b-nav-item>
                        </b-nav>
                    </b-col>
                    <b-col lg="9" md="8">
                        <div class="cms-content cms-accordian" id="cms-content">
                            <div class="cms-content-block" v-for="(item,key) in footerItems.sections"
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
            <TheSecondaryFooter @cmsListing="cmsListing"></TheSecondaryFooter>
        </footer>
    </div>
</template>
<script>
  import ThePrimaryHeader from "../components/Layouts/ThePrimaryHeader";
  import TheSecondaryFooter from "../components/Layouts/TheSecondaryFooter";
  import axios from "axios";
  import store from '../store';
  import {
    cmsDetail
  } from '../services/service';

  export default {
    components: {
      ThePrimaryHeader,
      TheSecondaryFooter
    },
    data() {
      return {
        footerItems: [],
        isDynamicFooterItemsSet: false,
        slug: this.$route.params.slug
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
        let scrollHeight = screenHeight - navTop - footerHeight + headerHeight;
        if (screen.width > 767 && screen.width < 1025) {
          if (contentHeight > scrollHeight) {
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

        let linkList = document.querySelectorAll(".cms-nav .nav-item");
        let blockList = document.querySelectorAll(".cms-content-block");
        for (let i = 0; i < blockList.length; ++i) {
          if (blockList[i].getBoundingClientRect().top < headerHeight + 42) {
            for (let j = 0; j < linkList.length; j++) {
              let linkSiblings = linkList[j].parentNode.childNodes;
              for (let k = 0; k < linkSiblings.length; ++k) {
                linkSiblings[k].childNodes[0].classList.remove("active");
              }
              linkSiblings[i].childNodes[0].classList.add("active");
            }
          }
        }
      },

      getOffset() {
        let headerHeight = document.querySelector("header").offsetHeight;
        return -headerHeight;
      },
      //List cms pages
      cmsListing(slug) {
        cmsDetail(slug).then((response) => {
          if (response.error === false) {
            this.isDynamicFooterItemsSet = true;
            this.footerItems = response.data.pages[0];
          } else {
            this.$router.push({
              name: '404'
            });
          }
        });
      }
    },

    created() {
      this.cmsListing(this.slug);
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