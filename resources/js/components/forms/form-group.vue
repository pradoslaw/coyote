<template>
  <div class="form-group">
    <label v-if="label" class="col-form-label">{{ label }}</label>

    <slot></slot>

    <span class="invalid-feedback" v-if="error">{{ error }}</span>
    <span class="form-text" v-else-if="help">{{ help }}</span>
  </div>
</template>

<script>
  export default {
    props: {
      name: {
        type: String,
        require: true
      },
      error: {
        type: String
      },
      help: {
        type: String
      },
      label: {
        type: String
      }
    },

  }
</script>
