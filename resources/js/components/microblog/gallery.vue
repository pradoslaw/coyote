<template>
  <vue-lightbox :items="lightboxImages" :index="index" @close="close"/>
</template>

<script>
import VueLightbox from 'vue-cool-lightbox';

export default {
  components: {
    'vue-lightbox': VueLightbox,
  },
  props: {
    images: {type: Array, required: true},
    index: {type: [Number, null]},
  },
  methods: {
    close() {
      this.$emit('close');
    },
  },
  computed: {
    lightboxImages() {
      return this.$props.images.map(image => ({src: image.url, thumb: image.thumbnail}));
    },
  },
};
</script>
