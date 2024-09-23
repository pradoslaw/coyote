<template>
  <input :name="name" type="checkbox" v-model="valueLocal" :value="checkedValue">
</template>

<script>
import {default as mixins} from '../mixins/form.js';

export default {
  props: {
    name: {
      type: String,
      require: true,
    },
    modelValue: {
      type: [String, Number, Boolean],
    },
    checkedValue: {
      require: false,
      default: 1,
    },
  },
  mixins: [mixins],
};
</script>
