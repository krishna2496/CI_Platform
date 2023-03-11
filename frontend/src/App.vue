<template>
<div id="app" :class="{'loaded': stylesLoaded}">
    <router-view />
</div>
</template>

<script>
import {
    setTimeout
} from "timers";
import customCss from './services/CustomCss';
import customFavicon from './services/CustomFavicon';
import { setSiteTitle } from './utils';

export default {
    data() {
        return {
            stylesLoaded: false
        };
    },
    mounted() {
        document.addEventListener("click", this.onClick);
    },
    methods: {
        onClick() {
            let dropdownList = document.querySelectorAll(".dropdown-open");
            let body = document.querySelectorAll("body, html");
            let notification_btn = document.querySelector(".btn-notification");
            if (dropdownList.length > 0) {
                for (let i = 0; i < dropdownList.length; ++i) {
                    dropdownList[i].classList.remove("dropdown-open");
                }
            }
            if (screen.width < 992) {
                body.forEach(function (e) {
                    e.classList.remove("open-nav");
                    e.classList.remove("open-filter");
                });
            }
            if (screen.width < 992) {
                body.forEach(function () {
                    let breadcrumbDropdown = document.querySelector(
                        ".breadcrumb-dropdown-wrap"
                    );
                    if (document.querySelector(".breadcrumb") != null) {
                        breadcrumbDropdown.classList.remove("open");
                    }
                });
            }
            let notification_popover = document.querySelector(
                ".notification-popover"
            );
            if (notification_popover != null) {
                notification_btn.click();
            }
        },
        signinAdj() {
            setTimeout(function () {
                if (document.querySelector(".signin-form-wrapper") != null) {
                    let contentH = document.body.clientHeight;
                    document.querySelector(".signin-form-wrapper").style.minHeight = contentH + "px";
                }
            }, 1000);
        },
        handleScroll() {
            if (document.querySelector(".inner-pages > header") != null) {
                let body = document.querySelector("body");
                let bheader = document.querySelector("header");
                let bheaderTop = bheader.offsetHeight;
                if (window.scrollY > bheaderTop) {
                    body.classList.add("small-header");
                } else {
                    body.classList.remove("small-header");
                }
            }
        },
    },
    beforeMount() {
        this.signinAdj();
    },
    created() {
        document.body.classList.add("loader-enable");
        customCss()
            .catch(() => {
                import( /* webpackChunkName: "default-theme.css" */ './assets/scss/custom.scss');
            }).finally(() => {
                document.body.classList.remove("loader-enable");
                this.stylesLoaded = true;
            });

        customFavicon()
            .catch(() => {
              /* nothing to do since default favicon is already set to Optimy icon*/
            });

        let ua = navigator.userAgent.toLowerCase();
        if (ua.indexOf("safari") != -1) {
            if (ua.indexOf("chrome") > -1) {
                document.querySelector("body , html").classList.add("browser-chrome"); // Chrome
            } else {
                document.querySelector("body , html").classList.add("browser-safari"); // Safari
            }
        }
        //ios browser detection

        let isIOS =
            /iPad|iPhone|iPod/.test(navigator.platform) ||
            (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
        if (isIOS) {
            document.querySelector("body").classList.add("browser-ios");
        }
        window.addEventListener("resize", this.signinAdj);
        window.addEventListener("scroll", this.handleScroll);
        window.scrollTo(0, 0);
        setSiteTitle();
    },
    updated() {
        window.scrollTo(0, 0);
        this.signinAdj();
        setTimeout(function () {
            let selectorList = document.querySelectorAll(".nav-link");
            let menuLinkList = document.querySelectorAll(".menu-wrap a");
            let dropdownList = document.querySelectorAll(".custom-dropdown, .checkbox-select");
            let notificationButton = document.querySelector(
                ".notification-menu .nav-link .btn-notification");

            selectorList.forEach(function (event) {
                event.addEventListener("mouseover", function () {
                    event.removeAttribute("href");
                });
                event.addEventListener("click", function () {
                    dropdownList.forEach(function (removeDropdown) {
                        removeDropdown.classList.remove("dropdown-open");
                    });
                });
            });
            menuLinkList.forEach(function (linkEvent) {
                linkEvent.addEventListener("click", function () {
                    dropdownList.forEach(function (removeDropdown) {
                        removeDropdown.classList.remove("dropdown-open");
                    });
                });
            });
            if (notificationButton != null) {
                notificationButton.addEventListener("click", function () {
                    dropdownList.forEach(function (removeDropdown) {
                        removeDropdown.classList.remove("dropdown-open");
                    });
                });
            }

            let paginationItem = document.querySelectorAll(".pagination-block .page-item .page-link");
            paginationItem.forEach(function (pageLink) {
                pageLink.addEventListener("mouseover", function () {
                    pageLink.removeAttribute("href");
                });
            });

            // favourite-icon clickable
            let buttonActive = document.querySelectorAll(".favourite-icon");
            buttonActive.forEach(function (event) {
                event.addEventListener("click", function () {
                    event.classList.toggle("active");
                });
            });
            let dataInput = document.querySelectorAll(".mx-input");
            dataInput.forEach(function (inputEvent) {
                inputEvent.addEventListener("click", function () {
                    dropdownList.forEach(function (removeDropdown) {
                        removeDropdown.classList.remove("dropdown-open");
                    });
                });
            });

            let validationButton = document.querySelectorAll(".btn-validate");
                validationButton.forEach((saveButton) => {
                    saveButton.addEventListener("click", () => {
                        let windowTop = window.pageYOffset;
                        let controlError = document.querySelector(".is-invalid");
                        setTimeout(() => {
                            let alertPopup = document.querySelector(".alert");
                            if (alertPopup) {
                                window.scrollTo(0, 0);
                            }
                        }, 100)

                        if (controlError) {
                            let headerHeight = document.querySelector("header").offsetHeight;
                            let offsetTopValue = controlError.getBoundingClientRect().top +
                                windowTop - headerHeight - 40;
                            window.scrollTo(0, offsetTopValue);
                        }
                    });
                });

        }, 1000);

    },
    destroyed() {
        window.removeEventListener("scroll", this.handleScroll);
    }
};
</script>
