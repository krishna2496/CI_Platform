<template>
    <div>
        <canvas v-show='data' ref="chartRefs"></canvas>
        <div v-show="!data">
            <h5>Loading...</h5>
        </div>
    </div>
</template>
<script>
  import Chart from "chart.js";
  export default {
    name: 'horizontal-chart',
    props: {
      labels: {
        type: Array,
        default: []
      },
      data: {
        type: Array,
        default: []
      },
      fontColor: {
        type: String,
        default: "#414141"
      },
      backgroundColor: {
        type: String,
        default: "#dc3545"
      },
      borderColor: {
        type: String,
        default: "#dc3545"
      }
    },
    data() {
      return {
        themeChart: null
      }
    },
    mounted() {
      this.renderChart();
    },
    methods: {
      renderChart() {
        this.themeChart = null;
        let chartRefs = this.$refs.chartRefs;
        let chartContent = chartRefs.getContext("2d");
        chartContent.clearRect(0, 0, chartContent.width, chartRefs.height);
        this.themeChart = new Chart(chartContent, {
          type: "horizontalBar",
          data: {
            labels: this.labels,
            datasets: [{
              data: this.data,
              backgroundColor: this.backgroundColor,
              borderColor: this.borderColor,
              hoverBackgroundColor: this.backgroundColor,
              borderWidth: 1
            }]
          },
          options: {
            legend: {
              display: false
            },
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true
                }
              }],
              xAxes: [{
                ticks: {
                  fontColor: this.fontColor,
                  fontSize: 14,
                  beginAtZero: true
                }
              }]
            }
          }
        });
      }
    },
    watch: {
      $props: {
        handler() {
          this.themeChart.data.labels = [];
          this.themeChart.data.labels = this.labels;
          this.themeChart.data.datasets[0].data = this.data;
          this.themeChart.update();
        },
        deep: true,
      }
    }
  }
</script>