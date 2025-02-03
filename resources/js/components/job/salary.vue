<template>
  <p class="salary" :class="options.class">
    <strong class="neon-color-link">
      <template v-if="salary_from === salary_to">
        {{ toLocale(salary_from) }}
      </template>

      <template v-else-if="salary_from && salary_to">
        {{ toLocale(salary_from) }} - {{ toLocale(salary_to) }}
      </template>

      <template v-else-if="salary_from">
        od {{ toLocale(salary_from) }}
      </template>

      <template v-else-if="salary_to">
        do {{ toLocale(salary_to) }}
      </template>

      <template v-if="salary_from || salary_to">
        {{ currency_symbol }}
      </template>
    </strong>
    {{ ' ' }}
    <small v-if="salary_from && salary_to" class="text-muted">{{ is_gross ? 'brutto' : 'netto' }}</small>
    <small v-if="rate" class="text-muted">{{ rate }}</small>
  </p>
</template>

<script>
export default {
  props: {
    salary_from: {
      type: Number,
    },
    salary_to: {
      type: Number,
    },
    currency_symbol: {
      type: String,
    },
    rate: {
      type: String,
    },
    is_gross: {
      type: Boolean,
    },
    options: {
      type: Object,
      default: () => {
        return {};
      },
    },
  },
  methods: {
    toLocale(number) {
      if (number !== null) {
        // ugly fix: polish locale use space separator but not with values lower than 10000 (don't know why)
        return number.toLocaleString('en-US').replace(',', ' ');
      }

      return number;
    },
  },
};
</script>
