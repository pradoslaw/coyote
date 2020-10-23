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

      <input v-show="!url && !isPending" @change="upload" type="file" ref="input" >
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

<style lang="scss" scoped>
  @import "@/sass/helpers/_variables.scss";

  .placeholder-mask {
    min-height: 100px;
    min-width: 100px;
  }

  .flush-mask,
  .placeholder-mask,
  .spinner-mask,
  input {
    display: flex;
    vertical-align: middle;
    text-align: center;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
  }

  .flush-mask,
  .spinner-mask,
  input {
    position: absolute;
    top: 0;
    left: 0;
  }

  .flush-mask {
    display: none;
    background-color: #000;
    opacity: .5;
    color: #fff;
  }

  .thumbnail:hover {
    .flush-mask {
      display: flex;
    }
  }

  .spinner-mask,
  input {
    color: $gray;
  }

  input {
    appearance: none;
    cursor: pointer;
    opacity: 0;
  }
</style>
