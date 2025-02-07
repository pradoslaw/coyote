<template>
  <select
    :name="name"
    v-model="valueLocal"
    class="form-control form-select"
    :class="{'is-invalid': isInvalid}"
    :disabled="$props.disabled"
    ref="select"
  >
    <option v-if="placeholder" :value="null" v-text="placeholder"/>
    <option v-for="(value, key) in options" :value="key" v-html="value"/>
  </select>
</template>

<script>
export default {
  model: {
    prop: 'modelValue',
    event: 'update:modelValue',
  },
  props: {
    name: {type: String, require: true},
    modelValue: {type: [String, Number, Array]},
    options: {type: [Object, Array], require: true},
    placeholder: {type: [String], require: false},
    isInvalid: {type: Boolean, default: false},
    disabled: {type: Boolean, default: false},
  },
  computed: {
    valueLocal: {
      get() {
        return this.modelValue !== undefined ? this.modelValue : null;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      },
    },
  },
  methods: {
    focus() {
      this.$refs.select.focus();
    },
  },
};
</script>
