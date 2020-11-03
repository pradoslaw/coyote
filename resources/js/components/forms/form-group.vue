<template>
  <div class="form-group">
    <label v-if="label" class="col-form-label">{{ label }}</label>

    <slot></slot>

    <vue-error v-if="errors" :message="errors"></vue-error>
    <span class="form-text" v-else-if="help">{{ help }}</span>
  </div>
</template>

<script>
  import VueError from './error';

  export default {
    components: { 'vue-error': VueError },
    props: {
      name: {
        type: String,
        require: true
      },
      errors: {
        type: [Array, String]
      },
      help: {
        type: String
      },
      label: {
        type: String
      }
    }
  }
</script>
