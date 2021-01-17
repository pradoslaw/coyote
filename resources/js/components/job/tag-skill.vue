<template>
  <li>
    <span>
      {{ tag.name }}

      <a @click="$emit('delete', tag.name)" class="remove"><i class="fa fa-times"></i></a>
    </span>

    <div class="d-inline" @mouseenter="editable = true" @mouseleave="disableEditing">
      <span
        v-for="i in [1, 2, 3]"
        :aria-label="tooltips[i - 1]"
        @mouseover="highlight = i"
        @click="setPriority(i)"
        data-balloon-pos="down"
      >
        <i class="fas fa-circle" :class="{'text-primary': getHighlight(tag.priority) >= i, 'text-muted': getHighlight(tag.priority) < i}"></i>
      </span>
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

      this.$emit('priority', this.tag);
    },
    disableEditing() {
      this.editable = false;
      this.highlight = null;
    }
  }
}

</script>
