<template>
	<div class="signin-footer">
		<div class="footer-menu" v-if="isDynamicFooterItemsSet">
			<b-list-group>
				<b-list-group-item v-for="(item,key) in footerItems" v-bind:key=key :to="'/'+item.slug" :title="getTitle(item)">
					{{getTitle(item)}}
				</b-list-group-item>
			</b-list-group>
		</div>
		<div class="copyright-text">
			<p>
				{{ languageData.label.powered_by }}
				<b-link title="Optimy" href="https://www.optimy.com/">
					Optimy</b-link>
			</p>
		</div>
	</div>
</template>

<script>
	import store from "../../store";
	import {
		cmsPages
	} from "../../services/service";

	export default {
		components: {},
		name: "ThePrimaryFooter",
		data() {
			return {
				footerItems: [],
				isDynamicFooterItemsSet: false,
				languageData: []
			};
		},
		mounted() {},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			// Fetching footer CMS pages
			this.getPageListing();
			this.isLabelChange = true;
		},
		methods: {
			async getPageListing() {
				await cmsPages().then(response => {
					this.footerItems = response;
					this.isDynamicFooterItemsSet = true;
				});
			},

			getTitle(items) {
				//Get title according to language
				items = items.pages;
				if (items) {
					let filteredObj = items.filter((item) => {
						if (item.language_id == store.state.defaultLanguageId) {
							return item;
						}
					});
					if (filteredObj[0]) {
						return filteredObj[0].title;
					}
				}
			}
		}
	};
</script>