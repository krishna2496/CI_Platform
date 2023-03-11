<template>
<div class="breadcrumb-wrap">
    <b-container>
        <div class="breadcrumb-dropdown-wrap">
            <span class="breadcrumb-current" @click.stop></span>
            <div class="breadcrumb-dropdown">
                <b-breadcrumb>
                    <b-breadcrumb-item v-for="(item, index) in items" :key="index" :to="item.link" @click.stop>
                        {{item.name}}
                    </b-breadcrumb-item>
                </b-breadcrumb>
            </div>
        </div>
    </b-container>
</div>
</template>

<script>
import {
    setTimeout
} from 'timers';
import constants from '../constant';
import store from '../store';
export default {
    name: 'Breadcrumb',
    props: {
        breadcrumbActive: String
    },
    data() {
        return {
            isStoryDisplay: true,
            isCommentDisplay: true,
            isMessageDisplay: true,
            languageData: [],
            items: [
                {
                    id: 1,
                    name: '',
                    link: 'dashboard'
                },
                {
                    id: 2,
                    name: '',
                    link: 'volunteering-history'
                },
                {
                    id: 3,
                    name: '',
                    link: 'volunteering-timesheet'
                },
                {
                    id: 4,
                    name: '',
                    link: 'messages'
                },
                {
                    id: 5,
                    name: '',
                    link: 'comment-history'
                },
                {
                    id: 6,
                    name: '',
                    link: 'my-stories'
                }
            ]
        };
    },
    methods: {
        handleBreadcrumb() {
            if (screen.width < 768) {
                let breadcrumbDropdown = document.querySelector(
                    '.breadcrumb-dropdown-wrap'
                );
                breadcrumbDropdown.classList.toggle('open');
            }
        }
    },
    created() {
        setTimeout(() => {
            if (document.querySelector('.breadcrumb') != null) {
                let currentDashboard = document.querySelector(
                    '.breadcrumb .router-link-active'
                ).innerHTML;
                this.currentDashboardPage = currentDashboard;
                let currentLink = document.querySelector(".breadcrumb-current");
                currentLink.innerHTML = this.currentDashboardPage;
                currentLink.addEventListener("click", this.handleBreadcrumb);
            }
		});

        this.languageData = JSON.parse(store.state.languageLabel);
        this.items[0].name = this.languageData.label.dashboard
        this.items[1].name = this.languageData.label.volunteering_history;
        this.items[2].name = this.languageData.label.volunteering_timesheet;
        this.items[3].name = this.languageData.label.messages;
        this.items[4].name = this.languageData.label.comment_history;
        this.items[5].name = this.languageData.label.my_stories;

        const isGoalMissionActive = this.settingEnabled(constants.VOLUNTEERING_GOAL_MISSION);
        const isTimeMissionActive = this.settingEnabled(constants.VOLUNTEERING_TIME_MISSION);
        const isVolunteeringSettingEnabled = this.settingEnabled(constants.SETTING_VOLUNTEERING);
        const displayVolunteeringPages = isVolunteeringSettingEnabled
            && (isGoalMissionActive || isTimeMissionActive);
        if (!displayVolunteeringPages) {
            this.items.splice(1, 2)
        }

        if (!this.settingEnabled(constants.MESSAGE)) {
            this.items.splice(3, 1);
        }

        if (!this.settingEnabled(constants.MISSION_COMMENTS)) {
            this.items.splice(4, 1);
        }

        if (!this.settingEnabled(constants.STORIES_ENABLED)) {
            this.items.splice(5, 1);
        }
    }
};
</script>
