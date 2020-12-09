<template>
  <div class="thumbnail">
    <div class="position-relative img-thumbnail text-center">
      <template v-if="url">
        <img v-if="isImage" :src="url" class="mw-100">

        <div v-else class="bg-light placeholder-mask">
          <i class="far fa-folder fa-2x"></i>
        </div>
      </template>

      <div v-else class="bg-light placeholder-mask">
        <i v-if="!isProcessing" class="fas fa-plus-circle fa-2x"></i>
      </div>

      <a v-if="url" href="javascript:" class="flush-mask text-decoration-none" @click="deleteImage">
        <i class="fas fa-times fa-2x"></i>
      </a>

      <div v-if="isProcessing" class="spinner-mask">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
      </div>

      <input v-show="!url && !isProcessing" @change="upload" class="thumbnail-mask" type="file" ref="input" >
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import Component from 'vue-class-component';
import { Prop, Ref } from 'vue-property-decorator';
import axios from 'axios';

@Component
export default class VueThumbnail extends Vue {
  @Ref('input')
  readonly input!: HTMLInputElement;

  @Prop()
  readonly url?: string;

  @Prop({required: false, default: '/media'})
  readonly uploadUrl!: string;

  @Prop({default: 'photo'})
  readonly name!: string;

  isProcessing = false;

  upload() {
    let formData = new FormData();
    formData.append(this.name, this.input.files![0]);

    this.isProcessing = true;

    let config = {
      onUploadProgress: event =>  {
        this.$emit('progress', Math.round((event.loaded * 100) / event.total));
      }
    };

    return axios.post(this.uploadUrl, formData, config)
      .then(response => this.$emit('upload', response.data))
      .finally(() => this.isProcessing = false);
  }

  deleteImage() {
    this.$emit('delete', this.url);
  }

  // this method can be used by other components to open upload dialog
  openDialog() {
    this.input.click();
  }

  private get isImage() {
    return this.url?.match(/\.(jpeg|jpg|gif|png)$/) != null;
  }
}

</script>

