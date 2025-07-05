<script setup lang="ts">
import { computed } from 'vue';
import { getCookie } from '../../services/IsConnected';

const user = computed(() => {
  return {
    name: getCookie('pseudo') || 'Utilisateur',
    image: getCookie('image') || '/images/default-avatar.png'
  };
});
</script>

<template>
  <div class="flex items-center gap-3">
    <img :src="'http://localhost:8000/' + user.image" alt="avatar" class="w-10 h-10 rounded-full object-cover border" />
    <span class="font-semibold text-gray-800">{{ user.name }}</span>
  </div>
</template>