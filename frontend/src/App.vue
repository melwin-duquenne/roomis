<script setup lang="ts">
import { onMounted } from 'vue';
import { initWebSocket } from './services/websocket';

onMounted(initWebSocket);
</script>

<template>
  <div class="bg-gray-50 min-h-screen">
    <router-view /> 
  </div>
</template>