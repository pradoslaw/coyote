<template>
  <div class="thumbnail">
    <div class="position-relative img-thumbnail">
      <img v-if="url" :src="url" class="mw-100">
      <div v-else class="d-block bg-light img-placeholder"></div>

      <a v-if="url" href="javascript:" class="flush text-decoration-none" @click="remove">
        <i class="fas fa-times fa-2x"></i>
      </a>

      <div v-if="isPending" class="spinner">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
      </div>

      <a v-if="!url && !isPending" href="javascript:" class="upload text-decoration-none" @click="openDialog">
        <i class="fas fa-plus-circle fa-2x"></i>
      </a>
    </div>

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

<style lang="scss" scoped>
  @import "@/sass/helpers/_variables.scss";

  .thumbnail:hover {
    .flush {
      display: flex;
    }
  }

  .img-placeholder {
    height: 100px; // fixed height
  }

  .flush,
  .spinner,
  .upload {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    vertical-align: middle;
    text-align: center;
    justify-content: center;
    align-items: center;
  }

  .flush {
    display: none;
    background-color: #000;
    opacity: .5;
    color: #fff;
  }

  .spinner, .upload {
    color: $gray;
    display: flex;
  }

  input {
    visibility: hidden;
    height: 1px;
  }
</style>
