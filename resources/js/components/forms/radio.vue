<template>
  <input :name="name" type="radio" v-model="valueLocal" :value="checkedValue">
</template>

<script>
import {default as mixins} from '../mixins/form';

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
    },
  },
  mixins: [mixins],
};
</script>
