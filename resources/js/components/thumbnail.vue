<template>
  <div class="thumbnail">
    <div class="position-relative text-center i-95">
      <template v-if="url">
        <img
          v-if="isImage"
          :src="url"
          class="neon-rounded"
          :style="{width:'100%','height':'100%','object-fit':'cover','object-position':'center'}"
        />
        <div v-else class="placeholder-mask neon-rounded" style="font-size:2em;">
          <vue-icon name="thumbnailAssetUploadedFile"/>
        </div>
      </template>
      <div v-else class="bg-light placeholder-mask neon-rounded" style="font-size:2em;">
        <vue-icon v-if="!isProcessing" name="thumbnailAssetAdd"/>
      </div>
      <a v-if="url" href="javascript:" class="thumbnail-mask" @click="insertImage"/>
      <a v-if="url" href="javascript:" class="btn btn-sm btn-danger delete" @click="deleteImage" title="Usuń">
        <vue-icon name="thumbnailAssetRemove"/>
      </a>
      <div v-if="isProcessing" class="spinner-mask" style="font-size:2em;">
        <vue-icon name="thumbnailAssetUploading" spin/>
      </div>
      <input v-show="!url && !isProcessing" @change="upload" :accept="accept" class="thumbnail-mask" type="file" ref="input">
    </div>
  </div>
</template>

<script lang="ts">
import axios from 'axios';
import IsImage from '../libs/assets';
import VueIcon from "./icon";

export default {
  name: 'VueThumbnail',
  components: {
    'vue-icon': VueIcon,
  },
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
    openOnMount: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      isProcessing: false,
    };
  },
  mounted() {
    if (this.$props.openOnMount) {
      this.openDialog();
    }
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
};

</script>

