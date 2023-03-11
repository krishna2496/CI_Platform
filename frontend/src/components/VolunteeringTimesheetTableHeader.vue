<template>
	<div class="tab-with-picker">
		<div class="table-header">
			<h2>{{languageData.label[currentMonthName]}} {{currentYearNumber}}</h2>
			<div class="inner-wrap">
				<div class="picker-btn-wrap table-action-btn">
					<button
            class="prev-btn picker-btn"
            v-bind:class="{disabled :previousButtonDisable}"
            v-b-tooltip.hover
            :title="languageData.label.previous +' '+languageData.label.week.toLowerCase()"
            @click.stop="goPrevWeek"
          >
						<img
              :src="$store.state.imagePath+'/assets/images/back-arrow-black.svg'"
              :alt="languageData.label.previous"
            />
					</button>

					<button
            class="next-btn picker-btn"
            v-b-tooltip.hover
            :title="languageData.label.next+' '+languageData.label.week.toLowerCase()"
            v-bind:class="{disabled :disableNextWeek}"
            @click.stop="goNextWeek"
          >
						<img
              :src="$store.state.imagePath+'/assets/images/next-arrow-black.svg'"
              :alt="languageData.label.next"
            />
					</button>
				</div>
				<div class="picker-btn-wrap">
					<button
            class="prev-btn picker-btn"
            v-b-tooltip.hover
            v-bind:class="{disabled :previousButtonDisable}"
            :title="languageData.label.previous+' '+languageData.label.month.toLowerCase()"
            @click.stop="goPrev"
          >
						<img
              :src="$store.state.imagePath+'/assets/images/back-arrow-black.svg'"
              :alt="languageData.label.previous"
            />
					</button>

					<span>{{languageData.label[currentMonthName]}}</span>
					<button
            class="next-btn picker-btn"
            v-b-tooltip.hover
            :title="languageData.label.next+' '+languageData.label.month.toLowerCase()"
            v-bind:class="{disabled :isPreviousButtonDisable}"
            @click.stop="goNext"
          >
						<img
              :src="$store.state.imagePath+'/assets/images/next-arrow-black.svg'"
             :alt="languageData.label.next"
            />
					</button>
				</div>
				<div>
					<AppCustomDropdown
            :optionList="yearListing"
            @updateCall="changeYear"
            :defaultText="defaultYear"
            translationEnable="false"
          />
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import store from '../store';
	import moment from 'moment'
	import AppCustomDropdown from "../components/AppCustomDropdownToolTip";

	export default {
		name: "VolunteeringTimesheetHeader",
		components: {
			AppCustomDropdown
		},
		props: {
			currentWeek: Number
		},
		data: function () {
			return {
				time1: "",
				value2: "",
				currentWeak : this.currentWeek,
				lang: {
					days: [" Sun ", " Mon ", " Tue ", " Wed ", " You ", " Fri ", " Sat "],
					months: [
						"Jan",
						"Feb",
						"Mar",
						"Apr",
						"May",
						"Jun",
						"Jul",
						"Aug",
						"Sep",
						"Oct",
						"Nov",
						"Dec"
					],
					pickers: [
						"next 7 days",
						"next 30 days",
						"previous 7 days",
						"previous 30 days"
					],
					placeholder: {
						date: "mm/dd/yy",
						dateRange: "Select Date Range"
					},
				},
				defaultYear: "",
				yearListing: [],
				languageData: [],
				currentMonth: '',
				daysInCurrentMonth: 0,
				currentMonthName: '',
				currentMonthNumber: '',
				currentYearNumber: '',
				dayName: "",
				sortNameOfMonth: "",
				weekNameArray: [],
				daysArray : [],
				isPreviousButtonDisable: false,
				currentMonthFix: moment().startOf('date'),
				currentFixWeek : moment().week(),
				disableNextWeek : false,
				yearArray : [],
				monthArray : [],
				previousButtonDisable : false,
				lastYear : ''
			}
		},
		watch: {
			currentWeek: function(newVal, oldVal) { // watch it
				this.currentWeak = newVal
				let payload = moment().startOf('date').week(this.currentWeak)
				this.changeMonth(payload);
			}
		},
		mounted() {
			let currentYear = new Date().getFullYear();
			let yearsList = [];
			let yearDiff  = 5;
			if (store.state.timesheetFromYear && store.state.timesheetFromYear !== '') {
				let lastYear = store.state.timesheetFromYear;
				if ((currentYear - lastYear) + 1 > 0) {
					yearDiff = (currentYear - lastYear) + 1;
				}
			}
			for (let index = currentYear; index > (currentYear - yearDiff); index--) {
				yearsList.push([index, index]);
			}
			this.yearListing = yearsList;
			this.lastYear = parseInt(yearsList[yearsList.length -1][1]);
		},
		methods: {
			goPrevWeek() {
				let payload = moment(this.currentMonth).year(this.currentYearNumber).subtract(7, 'days').startOf('week')
				this.currentWeak = moment(this.currentMonth).year(this.currentYearNumber).subtract(7, 'days').week()
				this.changeMonth(payload);
				this.$root.$emit('bv::hide::tooltip');
			},
			goNextWeek() {
				let payload = moment(this.currentMonth).year(this.currentYearNumber).add(7, 'days').startOf('week')
				this.currentWeak = moment(this.currentMonth).year(this.currentYearNumber).add(7, 'days').week()
				this.changeMonth(payload);
				this.$root.$emit('bv::hide::tooltip');
			},
			getWeekDayNameOfMonth(month, year) {
				//stating date of week
				let start = moment().day("Monday").year(this.currentYearNumber).week(this.currentWeak);

				this.weekNameArray = []
				this.daysArray = []
				let i=0;
				let j = 1;
				for (let end = moment(start).add(1, 'week'); start.isBefore(end); start.add(1, 'day')) {
					let dayName = start.format('dddd').toLowerCase();
					this.weekNameArray[j] = this.languageData.label[dayName];
					this.daysArray[i] = start.format('D')-1
					this.yearArray[i] = start.format('YYYY')
					this.monthArray[i] = start.format('M')
					i++;
					j++;
				}
			},
			goPrev() {
				let payload = moment(this.currentMonth).year(this.currentYearNumber).subtract(1, 'months').startOf(
						'month');
				this.currentWeak= moment(this.currentMonth).year(this.currentYearNumber).subtract(1, 'months').startOf(
						'month').week()
				this.$root.$emit('bv::hide::tooltip');
				this.changeMonth(payload);
			},
			goNext() {
				let payload = moment(this.currentMonth).year(this.currentYearNumber).add(1, 'months').startOf('month');
				this.currentWeak= moment(this.currentMonth).year(this.currentYearNumber).add(1, 'months').startOf(
						'month').week()
				this.changeMonth(payload);
				this.$root.$emit('bv::hide::tooltip');
			},
			changeYear(year) {
				let payload = moment(this.currentMonth).year(year.selectedId)
				if ((parseInt(this.currentMonthFix.format('M')) <= parseInt(payload.format('M'))) && (parseInt(this.currentMonthFix.format(
						'YYYY')) <= parseInt(payload.format('YYYY')))) {
					payload = moment().startOf('date');
					this.currentWeak= this.currentFixWeek;
				} else {
					this.currentWeak= moment(this.currentMonth).year(this.currentYearNumber).startOf('month').week()
				}
				this.changeMonth(payload);
			},
			changeMonth(payload) {
				this.currentMonth = payload;
				this.daysInCurrentMonth = this.currentMonth.daysInMonth();
				this.currentMonthName = this.currentMonth.format('MMMM').toLowerCase();
				this.currentMonthNumber = this.currentMonth.format('M');
				this.currentYearNumber = this.currentMonth.format('Y');
				this.sortNameOfMonth = this.currentMonth.format('MMM')
				this.defaultYear = this.currentMonth.format('Y');

				if ((parseInt(this.currentMonthFix.format('M')) <= parseInt(this.currentMonth.format('M'))) && (parseInt(this.currentMonthFix.format(
						'YYYY')) <= parseInt(this.currentMonth.format('YYYY')))) {
					this.isPreviousButtonDisable = true;

					// previousButtonDisable
				} else {
					this.isPreviousButtonDisable = false;
				}

				if(this.currentFixWeek  <= this.currentWeak && (parseInt(this.currentMonthFix.format(
						'YYYY')) <= parseInt(this.currentMonth.format('YYYY'))) ) {
					this.disableNextWeek = true
					if (this.currentFixWeek == 1 && this.currentFixWeek  < this.currentWeak) {
						this.disableNextWeek = false
					}
				} else {
					this.disableNextWeek = false
				}

				if(this.lastYear == parseInt(this.currentYearNumber) && (this.currentMonthNumber <= 1)) {
					this.previousButtonDisable = true
				} else {
					this.previousButtonDisable = false
				}

				this.getWeekDayNameOfMonth(this.sortNameOfMonth, this.currentYearNumber)
				let selectedData = []
				selectedData['month'] = this.currentMonthNumber;
				selectedData['year'] = this.currentYearNumber;
				selectedData['weekdays'] = this.weekNameArray;
				selectedData['days'] = this.daysArray;
				selectedData['yearArray'] = this.yearArray;
				selectedData['monthArray'] = this.monthArray;
				this.$emit("updateCall", selectedData);

			},
		},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
			this.currentMonth = moment().startOf('date').week(this.currentWeak);
			this.changeMonth(this.currentMonth);

		}
	};
</script>