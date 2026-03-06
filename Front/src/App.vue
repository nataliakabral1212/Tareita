<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import MenuLateral from './components/MenuLateral.vue'

const router = useRouter()
const activeRoute = ref('')

function navigate(route: string) {
  activeRoute.value = route
  router.push(route)
}
</script>

<template>
  <div style="display:flex;width:100%;height:100%;">
    <MenuLateral :activeRoute="activeRoute" @navigate="navigate" />
    <div style="flex:1;overflow:auto;">
      <RouterView />
    </div>
  </div>
</template>

<style>
html, body, #app { margin:0; padding:0; width:100%; height:100%; overflow:hidden; }
</style>
