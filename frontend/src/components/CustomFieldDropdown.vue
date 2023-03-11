<template>
	<div v-if="optionList != null && optionList.length > 0" v-bind:class="{
        'custom-dropdown' :true,
        'select-dropdown':true,
        'is-invalid' : errorClass
      }">
		<span class="select-text" @click="handleClick">{{defaultText}}</span>
		<div class="option-list-wrap dropdown-option-wrap " data-simplebar>
			<ul class="option-list dropdown-option-list" v-if="translationEnable == 'false'">
				<li v-for="(item, key) in optionList" :key="key" v-bind:data-id="item[0]" @click="handleSelect">
					{{item[1]}}</li>
			</ul>
			<ul class="option-list dropdown-option-list" v-else>
				<li v-for="(item ,key) in optionList" v-bind:data-id="item[0]" @click="handleSelect" :key="key">
					{{languageData.label[item[1]]}}</li>
			</ul>
		</div>

	</div>
	<div v-else v-bind:class="{
        'custom-dropdown' :true,
        'select-dropdown':true,
        'is-invalid' : errorClass
      }">
		<span class="select-text" @click="handleClick">{{defaultText}}</span>

	</div>
</template>

<script>
	import store from '../store';
	export default {
		name: "CustomFieldDropdown",
		components: {},
		props: {
			optionList: Array,
			defaultText: String,
			translationEnable: String,
			errorClass: Boolean,
		},
		data() {
			return {
				defaultTextVal: this.defaultText,
				languageData: []
			};
		},
		mounted() {},
		methods: {
			handleSelect(e) {
				let selectedData = []
				selectedData['selectedVal'] = e.target.innerHTML;
				selectedData['selectedId'] = e.target.dataset.id;
				this.$emit("updateCall", selectedData);
			},
			handleClick(e) {
				e.stopPropagation();
				let profile_toggle = document.querySelector(
						".profile-menu .dropdown-toggle"
				);
				let profile_menu = document.querySelector(".profile-menu");
				if (profile_menu != null) {
					if (profile_menu.classList.contains("show")) {
						profile_toggle.click();
					}
				}
				let notification_btn = document.querySelector(
						".notification-menu .nav-link .btn-notification"
				);
				let notification_popover = document.querySelector(
						".notification-popover"
				);
				if (notification_popover != null) {
					notification_btn.click();
				}

				e.target.parentNode.classList.toggle("dropdown-open");
				let simplebarScrollTop = e.target.parentNode.querySelector(".simplebar-content-wrapper");
				if(simplebarScrollTop) {
					simplebarScrollTop.scrollTop = 0;
				}
				let dropdownList = document.querySelectorAll(".dropdown-open");
				for (let i = 0; i < dropdownList.length; ++i) {
					if (dropdownList[i] != e.target.parentNode) {
						dropdownList[i].classList.remove("dropdown-open");
					}
				}
				let simplebarOffset = e.target.parentNode.querySelector(".simplebar-offset");
				if (simplebarOffset != null && window.innerWidth > 1024) {
					let simplebarOffset_width = parseInt(window.getComputedStyle(simplebarOffset).getPropertyValue(
							"width"));
					let simplebarWrapper = simplebarOffset.parentNode.parentNode;
					simplebarWrapper.style.width = simplebarOffset_width + "px";
					let dropdown_list = e.target.parentNode;
					let dropdown_list_width = parseInt(window.getComputedStyle(dropdown_list).getPropertyValue("width"));
					let optionlist_wrap = dropdown_list.querySelector(".dropdown-option-wrap");
					let optionlist = optionlist_wrap.querySelector(".dropdown-option-list");
					if (optionlist != null) {
						let optionlist_width = parseInt(window.getComputedStyle(optionlist).getPropertyValue("width"));
						let minwidth_style = dropdown_list.querySelector(".simplebar-offset");
						if (dropdown_list_width > optionlist_width) {
							minwidth_style.setAttribute("style", "left: 0 !important");
						}
					}
					setTimeout(() => {
						let dropdown_list_child = dropdown_list.childNodes[1];
						let optionlist_height = parseInt(window.getComputedStyle(optionlist).getPropertyValue(
								"height"));
						let dropdown_list_height = parseInt(window.getComputedStyle(dropdown_list_child)
								.getPropertyValue("height"));
						let minheight_style = dropdown_list.querySelector(".dropdown-option-wrap");
						if (dropdown_list_height > optionlist_height) {
							minheight_style.setAttribute("style", "overflow-x:hidden");
						}
					}, 500);
				}
			}
		},
		beforeDestroy() {
			document.removeEventListener("click", this.onClick);
		},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			setTimeout(() => {
				let selectDropdown = document.querySelectorAll('.select-dropdown');
				window.addEventListener("resize", function () {
					for (let i = 0; i < selectDropdown.length; i++) {
						selectDropdown[i].classList.remove('dropdown-open');
					}
				});
			})
		}
	};
</script>