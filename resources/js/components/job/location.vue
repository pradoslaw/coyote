<template>
  <div v-if="remote.enabled || locations.length > 0" class="location d-inline text-muted">
    <vue-icon name="jobOfferLocation"/>
    <ul>
      <li v-for="location in locations">
        <a v-if="clickable" :href="location.url" :title="'Znajdź oferty z miasta ' + location.city">
          {{ label(location) }}
        </a>
        <template v-else>{{ label(location) }}</template>
      </li>
    </ul>
    <a v-if="remote.enabled && clickable" :href="remote.url">
      ({{ remoteLabel }})
    </a>
    <template v-else-if="!clickable">
      ({{ remoteLabel }})
    </template>
  </div>
</template>

<script>
import VueIcon from '../icon';

export default {
  components: {VueIcon},
  props: {
    remote: {type: Object},
    locations: {type: Array},
    clickable: {type: Boolean, default: true},
    shortened: {type: Boolean, default: false},
  },
  methods: {
    label(location) {
      if (this.shortened) {
        return location.city;
      }

      function strip(value) {
        return value !== null ? value : '';
      }

      const s = strip(location.street) + ' ' + strip(location.street_number);
      return [s.trim(), location.city]
        .filter(item => item !== '')
        .join(', ');
    },
  },
  computed: {
    remoteLabel() {
      return this.remote.range ? `${this.remote.range}% pracy zdalnej` : 'praca zdalna';
    },
  },
};
</script>
