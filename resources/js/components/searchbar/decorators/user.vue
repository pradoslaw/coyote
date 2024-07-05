<template>
  <a :href="item.url" class="d-flex align-content-center text-truncate" tabindex="-1">
    <vue-avatar :photo="item.photo" class="i-16 mr-2"></vue-avatar>

    <span v-html="highlight(item.name, value)"></span>

    <div class="item-options">
      <a :href="item.url" class="ml-3" title="Przejdź do profilu użytkownika"><i class="fas fa-user"></i></a>
      <a :href="'/User/Pm/Submit?to=' + item.name" class="ml-3" title="Napisz wiadomość"><i class="fas fa-comment"></i></a>
      <a :href="'/Search?model=Topic&sort=date&user=' + item.name" class="ml-3" title="Znajdź posty użytkownika"><i class="fas fa-magnifying-glass"></i></a>
    </div>
  </a>
</template>

<script lang="ts">
  import { Component, Mixins } from "vue-property-decorator";
  import DecoratorMixin from '../mixin';
  import VueAvatar from "../../avatar.vue";

  @Component({
    components: { 'vue-avatar': VueAvatar }
  })
  export default class UserDecorator extends Mixins(DecoratorMixin) {
    //
  }
</script>
