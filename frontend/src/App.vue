<template>
  <div id="app">
    <router-view name="header" />
    <div class="wrapper">
      <router-view  />
    </div>
    <router-view name="footer" />
  </div>
</template>
<script>

export default {
   data() {
    return {
    }
  },
  mounted() {
        },
  methods: {
        }
};
</script>
<style lang="scss">
</style>
