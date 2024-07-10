<template>
  <div class="thumbnail">
    <div class="position-relative img-thumbnail text-center">
      <template v-if="url">
        <img v-if="isImage" :src="url" class="mw-100">

        <div v-else class="placeholder-mask">
          <i class="far fa-file fa-2x"></i>
        </div>
      </template>

      <div v-else class="bg-light placeholder-mask">
        <i v-if="!isProcessing" class="fas fa-circle-plus fa-2x"></i>
      </div>

      <a v-if="url" href="javascript:" class="thumbnail-mask" @click="insertImage"></a>

      <a v-if="url" href="javascript:" class="btn btn-sm btn-danger delete" @click="deleteImage" title="Usuń">
        <i class="fas fa-xmark"></i>
      </a>

      <div v-if="isProcessing" class="spinner-mask">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
      </div>

      <input v-show="!url && !isProcessing" @change="upload" :accept="accept" class="thumbnail-mask" type="file" ref="input">
    </div>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import Vue from 'vue';
import IsImage from '../libs/assets';

export default Vue.extend({
  name: 'VueThumbnail',
  props: {
    url: {
      type: String,
      required: false,
    },
    uploadUrl: {
      type: String,
      default: '/assets',
    },
    name: {
      type: String,
      default: 'photo',
    },
    onlyImage: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isProcessing: false,
    };
  },
  methods: {
    upload() {
      let formData = new FormData();
      formData.append(this.name, this.$refs.input.files[0]);

      this.isProcessing = true;

      const config = {
        onUploadProgress: event => {
          this.$emit('progress', Math.round((event.loaded * 100) / event.total));
        },
      };

      return axios.post(this.uploadUrl, formData, config)
        .then(response => this.$emit('upload', response.data))
        .finally(() => {
          this.isProcessing = false;
        });
    },
    deleteImage() {
      this.$emit('delete', this.url);
    },
    insertImage() {
      this.$emit('insert', this.url);
    },
    openDialog() {
      this.$refs.input.click();
    },
  },
  computed: {
    isImage() {
      return IsImage(this.url);
    },
    accept() {
      return this.onlyImage ? 'image/jpeg,image/png,image/gif' : '';
    },
  },
});

</script>

