<template>
    <div class="table-col-inner">
        <div class="table-outer timesheet-table-outer">
            <div class="table-inner">
                <h3>{{headerLable}}</h3>

                <b-table-simple  class="volunteery-table" responsive v-if="items.length > 0">
                    <b-thead>
                        <b-tr>
                            <b-th>{{languageData.label.mission}}</b-th>
                            <b-th v-if="requestType =='time'">{{languageData.label.time}}</b-th>
                            <b-th v-if="requestType =='time'">{{languageData.label.hours}}</b-th>
                            <b-th v-if="requestType == 'goal' ">{{languageData.label.action}}</b-th>
                            <b-th>{{languageData.label.organisation}}</b-th>
                        </b-tr>
                    </b-thead>
                    <b-tbody >
                        <b-tr v-for="(item,key) in items" v-bind:key="key">
                            <b-td>
                                <a target="_blank" class="table-link"
                                   :href="`mission-detail/${item.mission_id}`">{{item.mission}}</a>
                            </b-td>
                            <b-td  v-if="requestType =='time'">
                                {{item.time}}
                            </b-td>
                            <b-td  v-if="requestType =='time'">
                                {{item.hours}}
                            </b-td>
                            <b-td  v-if="requestType =='goal'">
                                {{item.action}}
                            </b-td>
                            <b-td>
                                {{item.organisation}}
                            </b-td>

                        </b-tr>
                    </b-tbody>
                </b-table-simple>

                <div class="text-center" v-else>
                    <h5>{{languageData.label.no_data_available}}</h5>
                </div>
            </div>
            <div class="btn-block" v-if="items.length > 0">
                <b-button class="btn-bordersecondary ml-auto" @click="exportFile">{{languageData.label.export}}
                </b-button>
            </div>
        </div>
        <div class="pagination-block" v-if="items.length > 0 && totalPages > 1">
            <b-pagination
                    :hide-ellipsis="hideEllipsis"
                    v-model="page" :total-rows="totalRow" :per-page="perPage" align="center" @change="pageChange">
            </b-pagination>
        </div>
    </div>
</template>

<script>
  import store from '../store';
  import ExportFile from "../services/ExportFile";

  export default {
    name: "VolunteeringRequest",
    components: {},
    props: {
      items: Array,
      headerField: Array,
      headerLable: String,
      currentPage: Number,
      totalRow: Number,
      exportUrl: String,
      fileName: String,
      perPage: Number,
      nextUrl: String,
      totalPages: Number,
      requestType : String
    },
    data: function () {
      return {
        languageData: [],
        page: this.currentPage,
        hideEllipsis:true
      }
    },
    directives: {},
    computed: {

    },
    methods: {
      pageChange(page) {
        this.$emit("updateCall", page);
      },
      exportFile() {
        ExportFile(this.exportUrl, this.fileName);
      }
    },
    created() {
      this.languageData = JSON.parse(store.state.languageLabel)
    }
  };
</script>