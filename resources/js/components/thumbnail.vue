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
import Component from 'vue-class-component';
import {Emit, Prop, Ref} from 'vue-property-decorator';
import IsImage from '../libs/assets';

@Component
export default class VueThumbnail extends Vue {
  @Ref('input')
  readonly input!: HTMLInputElement;

  @Prop()
  readonly url?: string;

  @Prop({required: false, default: '/assets'})
  readonly uploadUrl!: string;

  @Prop({default: 'photo'})
  readonly name!: string;

  @Prop({default: false})
  readonly onlyImage!: boolean;

  isProcessing = false;

  upload() {
    let formData = new FormData();
    formData.append(this.name, this.input.files![0]);

    this.isProcessing = true;

    const config = {
      onUploadProgress: event => {
        this.$emit('progress', Math.round((event.loaded * 100) / event.total));
      },
    };

    return axios.post(this.uploadUrl, formData, config)
      .then(response => this.$emit('upload', response.data))
      .finally(() => this.isProcessing = false);
  }

  @Emit('delete')
  deleteImage() {
    return this.url;
  }

  @Emit('insert')
  insertImage() {
    return this.url;
  }

  // this method can be used by other components to open upload dialog
  openDialog() {
    this.input.click();
  }

  private get isImage() {
    return IsImage(this.url!);
  }

  get accept() {
    return this.onlyImage ? 'image/jpeg,image/png,image/gif' : '';
  }
}

</script>

