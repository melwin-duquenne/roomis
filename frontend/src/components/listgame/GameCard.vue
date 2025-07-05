<template>
  <div
    class="flex flex-col items-center bg-white rounded-lg shadow p-4 hover:shadow-lg transition cursor-pointer"
    @click="$emit('select', game)"
  >
    <img :src="game.image" :alt="game.title" class="w-24 h-24 object-cover rounded mb-2" />
    <div class="text-center font-semibold text-gray-800">{{ game.title }}</div>
  </div>
</template>

<script setup lang="ts">
defineProps<{ game: { id: number; title: string; image: string } }>();
defineEmits(['select']);
</script>