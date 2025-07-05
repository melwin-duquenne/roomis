<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Room : {{ roomName }}</h2>
    <button class="mt-4 text-blue-600 hover:underline" @click="leaveRoom">Quitter la room</button>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue'

const emit = defineEmits(['leave-room'])

const props = defineProps<{
  roomId: number | string,
  roomName: string
}>()

function leaveRoom() {
  emit('leave-room', props.roomId)
}
</script>