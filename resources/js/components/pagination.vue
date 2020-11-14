<template>
  <ul v-if="totalPages > 1" class="pagination">
    <li class="page-item" v-if="currentPage > 1">
      <a :href="url(currentPage - 1)" class="page-link" aria-label="Previous" @click.prevent="change(currentPage - 1)">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>

    <li v-for="element in slider" class="page-item" :class="{'active': element === currentPage, 'disabled': element === '...'}">
      <span v-if="element === currentPage" class="page-link">{{ element }}</span>
      <a v-else-if="element !== '...'" :href="url(element)" @click.prevent="change(element)" class="page-link">{{ element }}</a>
      <span v-else class="page-link">...</span>
    </li>

    <li v-if="currentPage < totalPages" class="page-item">
      <a :href="url(currentPage + 1)" class="page-link" aria-label="Next" @click.prevent="change(currentPage + 1)">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
  </ul>
</template>

<script>
  export default {
    props: {
      totalPages: {
        type: Number,
        required: true
      },
      currentPage: {
        type: Number,
        required: true
      },
      offset: {
        type: Number,
        default: 3
      },
      baseUrl: {
        type: String,
        default: ''
      }
    },
    computed: {
      slider() {
        if (!this.totalPages) {
          return [];
        }

        const begin = [1, 2, '...'];
        let end = () => ['...', this.totalPages - 1, this.totalPages];

        if (this.totalPages < (this.offset * 2) + 6) {
          return this.range(1, this.totalPages);
        }

        let window = this.offset * 2;

        if (this.currentPage <= window) {
          return this.range(1, window + 2).concat(end());
        }

        if (this.currentPage > (this.totalPages - window)) {
          return begin.concat(this.range(this.totalPages - (window + 2), this.totalPages));
        }

        let slider = begin.concat(this.range(this.currentPage - this.offset, this.currentPage + this.offset));
        return slider.concat(end());
      }
    },
    methods: {
      change(page) {
        this.$emit('change', page);
      },
      range(start, end) {
        let arr = [];

        for (let i = start; i <= end; i++) {
          arr.push(i);
        }

        return arr;
      },
      url(page) {
        return `${this.baseUrl}?page=${page}`;
      }
    }
  };
</script>
