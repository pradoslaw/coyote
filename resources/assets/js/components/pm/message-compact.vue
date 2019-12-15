<template>
  <div :title="message.excerpt" :class="{'unread': ! isRead}" class="notification">
    <a :href="message.url" class="notification-link">
      <div class="media">
        <div class="media-left">
          <object class="media-object" :data="message.user.photo || '//'" type="image/png">
            <img src="/img/avatar.png">
          </object>
        </div>

        <div class="media-body">
          <header>
            <h4>{{ message.user.name }}</h4>
            <small>{{ message.created_at }}</small>
          </header>

          <p>
            <template v-if="message.folder === SENTBOX">
              <i v-if="message.read_at" class="fas fa-check"></i>
              <span v-else>Ty: </span>
            </template>

            {{ message.excerpt }}
          </p>
        </div>
      </div>
    </a>
  </div>
</template>

<script>
  export default {
    props: {
      message: {
        type: Object
      }
    },
    data() {
      return {
        SENTBOX: 2
      }
    },
    computed: {
      isRead() {
        return this.message.folder !== this.SENTBOX ? (this.message.read_at !== null) : true;
      }
    }
  }
</script>
