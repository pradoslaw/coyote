<template>
  <div class="thumbnail">
    <div class="position-relative img-thumbnail text-center">
      <img v-if="url" :src="url" class="mw-100">

      <div v-else class="bg-light placeholder-mask">
        <i v-if="!isPending" class="fas fa-plus-circle fa-2x"></i>
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

  isPending = false;

  upload() {
    let form = new FormData();
    form.append(this.name, this.input.files![0]);

    this.isPending = true;

    axios.post(this.uploadUrl, form)
      .then(response => this.$emit('upload', response.data))
      .finally(() => this.isPending = false);
  }

  deleteImage() {
    this.$emit('delete', this.url);
  }

  // this method can be used by other components to open upload dialog
  openDialog() {
    this.input.click();
  }
}

</script>

