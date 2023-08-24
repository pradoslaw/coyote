<template>
    <iframe v-if="isLoaded"
      src="https://ghbtns.com/github-btn.html?user=pradoslaw&repo=coyote&type=star&count=true&size=large"
      frameborder="0"
      scrolling="0"
      width="120"
      height="30"
      title="GitHub"
    ></iframe>
</template>

<script lang="js">

export default {
  data: () => ({
    isLoaded: false,
    isMounted: false,
  }),
  mounted() {
    if (this.isMounted) {
      return;
    }

    window.addEventListener('load', () => {
      this.isLoaded = true;
    })

    this.isMounted = true;
  }
}
</script>
