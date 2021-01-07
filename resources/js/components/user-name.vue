<template>
  <component
    :is="tagName"
    v-profile="user.id"
    :class="{'badge badge-primary': owner}"
    :style="{textDecoration: this.user.is_blocked ? 'line-through' : ''}"
  >{{ user.name }}</component>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { default as mixins } from './mixins/user';
  import { Prop } from "vue-property-decorator";
  import { User } from "@/types/models";
  import Component from "vue-class-component";

  @Component({
    name: 'user-name',
    mixins: [mixins]
  })
  export default class VueUserName extends Vue {
    @Prop(Object)
    user!: User;

    @Prop({default: false})
    owner!: boolean;

    get tagName() {
      return this.user.deleted_at ? 'del' : 'a';
    }
  }
</script>

