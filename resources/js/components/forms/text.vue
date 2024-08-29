<template>
  <input :type="type" :name="name" v-model="valueLocal" class="form-control" :class="{'is-invalid': isInvalid}">
</template>

<script>
import {default as mixins} from '../mixins/form';

export default {
  props: {
    name: {
      type: String,
      require: true,
    },
    value: {
      type: [String, Number],
    },
    isInvalid: {
      type: Boolean,
      default: false,
    },
    type: {
      type: String,
      default: 'text',
    },
  },
  mixins: [mixins],
};
</script>
