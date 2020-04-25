<template>
  <div class="img-thumbnail editable-thumbnail">
    <img v-if="url" :src="url" class="mw-100">
    <div v-else class="d-block bg-light img-placeholder"></div>

    <a v-if="url" class="flush" @click="remove">
      <i class="fas fa-times fa-2x"></i>
    </a>

    <div v-if="isPending" class="spinner">
      <i class="fas fa-spinner fa-spin fa-2x"></i>
    </div>

    <a v-if="!url && !isPending" class="upload" @click="openDialog">
      <i class="fas fa-plus-circle fa-2x"></i>
    </a>

    <input type="file" ref="input" @change="upload">

    <vue-modal ref="error">
      {{ error }}
    </vue-modal>
  </div>
</template>

<script>
  import axios from 'axios';
  import VueModal from './modal.vue';

  export default {
    props: {
      url: {
        type: String
      },
      file: {
        type: String
      },
      uploadUrl: {
        type: String
      },
      name: {
        type: String,
        default: 'photo'
      }
    },
    components: {
      'vue-modal': VueModal
    },
    data() {
      return {
        isPending: false,
        error: null
      }
    },
    methods: {
      openDialog() {
        this.$refs.input.click();
      },

      upload() {
        let form = new FormData();
        form.append(this.name, this.$refs.input.files[0]);

        this.isPending = true;

        axios.post(this.uploadUrl, form)
          .then(response => this.$emit('upload', response.data))
          .catch(error => {
            this.error = error.response.data.message;

            this.$refs.error.open();

          })
          .finally(() => this.isPending = false);
      },

      remove() {
        this.$emit('delete', this.file);
      }
    }
  }

</script>
