<script setup>
import Header from '@/components/layout/Header.vue'
</script>

<template>
  <div class="min-h-screen">
    <Header />
    <main>
      <slot />
    </main>
  </div>
</template>

<style scoped></style>