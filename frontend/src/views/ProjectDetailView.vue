<script setup lang="ts">
const props = defineProps<{ id: string }>()
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold">Projeto {{ props.id }}</h1>
  </div>
</template>