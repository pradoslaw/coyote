<template>
  <div class="thumbnail">
    <div class="position-relative img-thumbnail text-center">
      <img v-if="url" :src="url" class="mw-100">

      <div v-else class="bg-light placeholder-mask">
        <i class="fas fa-plus-circle fa-2x"></i>
      </div>

      <a v-if="url" href="javascript:" class="flush-mask text-decoration-none" @click="deleteImage">
        <i class="fas fa-times fa-2x"></i>
      </a>

      <div v-if="isPending" class="spinner-mask">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
      </div>

      <input v-show="!url && !isPending" @change="upload" class="thumbnail-mask" type="file" ref="input" >
    </div>
  </div>
</template>

<script>
  import axios from 'axios';

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
    data() {
      return {
        isPending: false
      }
    },
    methods: {
      upload() {
        let form = new FormData();
        form.append(this.name, this.$refs.input.files[0]);

        this.isPending = true;

        axios.post(this.uploadUrl, form)
          .then(response => this.$emit('upload', response.data))
          .finally(() => this.isPending = false);
      },

      deleteImage() {
        this.$emit('delete', this.file);
      }
    }
  }
</script>

