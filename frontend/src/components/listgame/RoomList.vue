<template>
  <div class="w-full max-w-lg mt-8">
    <button class="mb-4 text-blue-600 hover:underline" @click="$emit('back')">← Retour aux jeux</button>
    <h2 class="text-xl font-bold mb-4 text-center">Salles pour {{ game.title }}</h2>
    <ul class="mb-6">
      <li v-for="room in rooms" :key="room.id" class="flex items-center justify-between bg-gray-100 rounded p-2 mb-2">
        <span>{{ room.name }} ({{ room.playersCount || 0 }}/2)</span>
        <button
          class="text-blue-600 hover:underline ml-2"
            :disabled="room.playersCount >= 2"
          @click="$emit('join-room', room.id, room.name)"
        >Entrer</button>
        <button
        v-if="room.creator === getCookie('pseudo') && getJwtFromCookie()"
          class="text-red-600 hover:underline ml-2"
          @click="$emit('delete-room', room.id)"
        >Supprimer</button>
      </li>
    </ul>
    <form @submit.prevent="createRoom" class="flex gap-2">
      <input v-model="newRoomName" class="flex-1 border rounded px-2 py-1" placeholder="Nom de la salle" required />
      <button class="bg-blue-600 text-white px-4 py-1 rounded" type="submit">Créer</button>
    </form>
    <div v-if="roomMessage" class="mt-2 text-center text-sm text-red-600">{{ roomMessage }}</div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { getCookie, getJwtFromCookie } from '../../services/IsConnected';

const props = defineProps<{
  game: { id: number; title: string; image: string };
  rooms: { id: number; name: string; creator: string; playersCount?: number }[];
}>();
const emit = defineEmits(['back', 'create-room', 'join-room', 'delete-room']);
console.log(props.rooms);
const newRoomName = ref('');
const roomMessage = ref('');

function createRoom() {
  if (!newRoomName.value.trim()) {
    roomMessage.value = "Le nom de la salle est requis.";
    return;
  }
  emit('create-room', newRoomName.value);
  newRoomName.value = '';
  roomMessage.value = '';
}
</script>