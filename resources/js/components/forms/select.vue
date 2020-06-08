<template>
  <select :name="name" v-model="valueLocal" class="form-control" :class="{'is-invalid': isInvalid}">
    <option v-if="placeholder" :value="null">{{ placeholder }}</option>

    <option v-for="(value, key) in options" :value="key" v-html="value"></option>
  </select>
</template>

<script>
  export default {
    props: {
      name: {
        type: String,
        require: true
      },
      value: {
        type: [String, Number, Array]
      },
      options: {
        type: [Object, Array],
        require: true
      },
      placeholder: {
        type: [String],
        require: false
      },
      isInvalid: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      valueLocal: {
        get: function () {
          return this.value !== undefined ? this.value : null;
        },
        set: function (value) {
          this.$emit('update:value', value);
        }
      }
    }
  };
</script>
