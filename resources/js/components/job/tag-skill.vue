<template>
  <li>
    <a href="javascript:" @click="$emit('delete', tag.name)" class="remove">{{ tag.name }}</a>

    <div class="d-inline" @mouseenter="editable = true" @mouseleave="disableEditing">
      <i
        v-for="i in [0, 1, 2]"
        class="fa fa-circle"
        :title="tooltips[i]"
        :class="{'text-primary': getHighlight(tag.priority) >= i, 'text-muted': getHighlight(tag.priority) < i}"
        @mouseover="highlight = i"
        @click="setPriority(i)"
        data-toggle="tooltip"
      ></i>
    </div>
  </li>
</template>

<script>
export default {
  props: {
    tag: {
      type: Object
    },
    tooltips: {
      type: Array
    }
  },
  data() {
    return {
      editable: false,
      highlight: null
    }
  },
  methods: {
    getHighlight(_default) {
      return this.highlight !== null ? this.highlight : _default;
    },
    setPriority(priority) {
      this.tag.priority = priority;
      this.disableEditing();
    },
    disableEditing() {
      this.editable = false;
      this.highlight = null;
    }
  }
}

</script>
