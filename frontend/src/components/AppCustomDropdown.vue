<template>
	<div v-if="optionList != null && optionList.length > 0" v-bind:class="{
        'custom-dropdown' :true,
        'select-dropdown':true,
		'no-list-item' : noListItem
      }">
		<span class="select-text"  @click="handleClick">{{defaultText}}</span>
		<div class="option-list-wrap dropdown-option-wrap " data-simplebar>
			<ul class="option-list dropdown-option-list" v-if="translationEnable == 'false'">
				<li v-for="(item, index) in optionList" v-bind:data-id="item[0]" :key="index" @click="handleSelect">{{item[1]}}</li>
			</ul>
			<ul class="option-list dropdown-option-list" v-else>
				<li v-for="(item, index) in optionList" v-bind:data-id="item[0]" :key="index" @click="handleSelect">
					{{languageData.label[item[1]]}}</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import store from '../store';
	export default {
		name: "AppCustomDropdown",
		components: {},
		props: {
			optionList: Array,
			defaultText: String,
			translationEnable: String,
			noListItem: {
				type: Boolean,
				default: false
			},
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
				let profileToggle = document.querySelector(
						".profile-menu .dropdown-toggle"
				);
				let profileMenu = document.querySelector(".profile-menu");
				if (profileMenu != null) {
					if (profileMenu.classList.contains("show")) {
						profileToggle.click();
					}
				}
				let notificationBtn = document.querySelector(
						".notification-menu .nav-link .btn-notification"
				);
				let notificationPopover = document.querySelector(
						".notification-popover"
				);
				if (notificationPopover != null) {
					notificationBtn.click();
				}

				e.target.parentNode.classList.toggle("dropdown-open");
				let simplebarScrollTop = e.target.parentNode.querySelector(
						".simplebar-content-wrapper"
				);
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

					let dropdownList = e.target.parentNode;
					let dropdownListWidth = parseInt(window.getComputedStyle(dropdownList).getPropertyValue("width"));
					let optionlist_wrap = dropdownList.querySelector(".dropdown-option-wrap");
					let optionlist = optionlist_wrap.querySelector(".dropdown-option-list");
					if (optionlist != null) {
						let optionlistWidth = parseInt(window.getComputedStyle(optionlist).getPropertyValue("width"));
						let minWidthStyle = dropdownList.querySelector(".simplebar-offset");
						if (dropdownListWidth > optionlistWidth) {
							minWidthStyle.setAttribute("style", "left: 0 !important");
						}
					}
					setTimeout(() => {
						let dropdownListChild = dropdownList.childNodes[1];
						let optionlistHeight = parseInt(window.getComputedStyle(optionlist).getPropertyValue("height"));
						let dropdownListHeight = parseInt(window.getComputedStyle(dropdownListChild).getPropertyValue("height"));
						let minHeightStyle = dropdownList.querySelector(".dropdown-option-wrap");
						if (dropdownListHeight > optionlistHeight){
							minHeightStyle.setAttribute("style", "overflow-x:hidden");
						}
					},500);
				}
			}
		},
		beforeDestroy() {
			document.removeEventListener("click", this.onClick);
		},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			setTimeout( () => {
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