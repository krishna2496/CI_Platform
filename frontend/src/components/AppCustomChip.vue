<template>
    <div class="custom-chip">
        <span class="chip-content">
            <i class="document-icon">
                <img :src="url" alt="document" />
            </i>
            {{textVal}}
            <i class="chip-close" v-bind:data-id="tagId" v-bind:data-type="type" @click="handleSelect" v-if="!(type == 'country' && tagId  == defaultCountry)"> 
                <img v-bind:data-id="tagId"
                     v-bind:data-type="type" :src="$store.state.imagePath+'/assets/images/cross-ic.svg'" alt="close" />
            </i>
        </span>
    </div>
</template>
<script>
  import store from '../store';
  export default {
    name: "AppCustomChip",
    props: {
      textVal: String,
      tagId: String,
      type: String,
      url: String,
    },
    data() {
      return {
        defaultCountry: 0
      };
    },
    methods: {
      handleSelect(e) {
        let selectedData = []
        selectedData['selectedId'] = e.target.dataset.id;
        selectedData['selectedType'] = e.target.dataset.type;
        this.$emit("updateCall", selectedData);
      }
    },
    created() {
      this.defaultCountry = store.state.defaultCountryId
    }
  };
</script>