<template>
  <div class="post-review py-2" :class="reviewClasses">
    <div class="row">
      <div class="col-lg-2 d-none d-lg-block"/>
      <div class="col-12 col-lg-10">
        <slot/>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import VueIcon from "../icon";

type ReviewStyle = 'body'|'info'|'success'|'warning'|'error';

export default {
  name: 'VuePostReview',
  components: {VueIcon},
  props: ['reviewStyle'],
  computed: {
    reviewClasses(): string {
      const styles: { [keyof: ReviewStyle]: string } = {
        body: '',
        success: 'alert alert-card-header-flush alert-success',
        warning: 'alert alert-card-header-flush alert-warning',
        error: 'alert alert-card-header-flush alert-danger',
        info: 'alert alert-card-header-flush alert-info',
      };
      return styles[this.$props.reviewStyle];
    },
  },
};
</script>
