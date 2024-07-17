<template>
  <a :href="item.url" class="d-flex align-content-center text-truncate" tabindex="-1">
    <vue-avatar :photo="item.photo" class="i-16 me-2"></vue-avatar>

    <span v-html="highlight(item.name, value)"></span>

    <div class="item-options">
      <a :href="item.url" class="ms-3" title="Przejdź do profilu użytkownika"><i class="fas fa-user"></i></a>
      <a :href="'/User/Pm/Submit?to=' + item.name" class="ms-3" title="Napisz wiadomość"><i class="fas fa-comment"></i></a>
      <a :href="'/Search?model=Topic&sort=date&user=' + item.name" class="ms-3" title="Znajdź posty użytkownika"><i class="fas fa-magnifying-glass"></i></a>
    </div>
  </a>
</template>

<script lang="ts">
import Vue from 'vue';
import VueAvatar from '../../avatar.vue';
import DecoratorMixin from '../mixin';

export default Vue.extend({
  mixins: [DecoratorMixin],
  components: {
    'vue-avatar': VueAvatar,
  },
});
</script>
