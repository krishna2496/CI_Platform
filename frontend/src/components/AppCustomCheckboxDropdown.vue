<template>
	<div v-bind:class="{
        'checkbox-select' :true,
        'select-dropdown':true,
        'dropdown-with-counter' : true,
        'is-invalid' : errorClass
      }">
		<span class="select-text" @click="handleClick">{{filterTitle}}</span>
		<div class="chk-select-wrap dropdown-option-wrap" data-simplebar @click.stop>
			<ul class="chk-select-options dropdown-option-list" v-if="checkList.length > 0">
				<li v-for="(item , i) in checkList" v-bind:data-id="item.value" :key="i">
					<b-form-checkbox name v-model="items" @click.native="filterTable" v-bind:value="item.value">
						{{item.text}}</b-form-checkbox>
				</li>
			</ul>
			<ul class="chk-select-options dropdown-option-list" v-else>
				<li>
					<label class="no-checkbox">{{ languageData.label.no_record_found }}</label>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
	import store from '../store';
	export default {
		name: "AppCheckboxDropdown",
		components: {},
		props: {
			filterTitle: String,
			checkList: {
				type: Array,
				default: () => []
			},
			selectedItem: Array,
			fieldId: Number,
			errorClass: Boolean
		},

		data() {
			return {
				items: this.selectedItem,
				languageData: [],
			};
		},
		mounted() {},
		methods: {
			filterTable() {
				this.$emit("changeParmas");
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
				if (simplebarOffset != null && window.innerWidth > 991) {
					let simplebarOffset_width = parseInt(window.getComputedStyle(simplebarOffset).getPropertyValue(
							"width"));
					let simplebarWrapper = simplebarOffset.parentNode.parentNode;
					simplebarWrapper.style.width = simplebarOffset_width + "px";
					let dropdownList = e.target.parentNode;
					let dropdownListWidth = parseInt(window.getComputedStyle(dropdownList).getPropertyValue("width"));
					let optionlistWrap = dropdownList.querySelector(".dropdown-option-wrap");
					let optionlist = optionlistWrap.querySelector(".dropdown-option-list");
					if (optionlist != null) {
						let optionlistWidth = parseInt(window.getComputedStyle(optionlist).getPropertyValue("width"));
						let minwidth_style = dropdownList.querySelector(".simplebar-offset");
						if (dropdownListWidth > optionlistWidth) {
							minwidth_style.setAttribute("style", "left: 0 !important");
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
					});
				}
			}
		},
		watch: {
			items: function (val) {
				let selectedData = []
				selectedData['selectedVal'] = val.join(',');
				selectedData['fieldId'] = this.fieldId;
				this.$emit("updateCall", selectedData);
			},
			selectedItem: function () {
				this.items = this.selectedItem;
			},
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
		},
	};
</script>