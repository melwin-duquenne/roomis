<script setup lang="ts">
import NameUser from '../profil/nameUser.vue';
import { getJwtFromCookie } from '../../services/IsConnected';
import { onSearchResults, searchRoomsByName } from '../../services/websocket';
import { onMounted, ref } from 'vue';

const searchResults = ref<any[]>([]);

function logout() {
  document.cookie = "jwt=; Path=/; Max-Age=0; Secure; SameSite=Strict";
  window.location.reload();
}

function onSearch(e: Event) {
  const val = (e.target as HTMLInputElement).value.trim();
  if (val.length > 0) {
    searchRoomsByName(val);
  } else {
    searchResults.value = [];
  }
}

onMounted(() => {
  onSearchResults((rooms) => {
    searchResults.value = rooms;
  });
});

const emit = defineEmits(['open-login', 'search']);
</script>

<template>
  <header class="w-full bg-white shadow mb-10">
    <div class="flex items-center justify-between px-6 py-3">
      <div class="flex items-center">
        <img src="/public/images/basic/123.png" alt="Logo" class="h-24 w-auto" />
      </div>
      <div class="relative w-1/3">
        <input
          type="text"
          placeholder="Rechercher une room..."
          @input="onSearch"
          class="w-full px-4 py-2 border rounded"
        />
        <div
          v-if="searchResults.length"
          class="absolute z-10 mt-1 w-full bg-white border rounded shadow max-h-60 overflow-auto"
        >
          <div
            v-for="room in searchResults"
            :key="room._id"
            class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
          >
            {{ room.name }}
          </div>
        </div>
      </div>      
      <button v-if="!getJwtFromCookie()"
        class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 transition"
        @click="$emit('open-login')">
        Connexion
      </button>
      <div v-else class="flex items-center gap-4 mt-6">
        <a href="/profile">
        <NameUser />
        </a>
        <button class="px-4 py-2 rounded bg-red-600 text-white font-semibold hover:bg-red-700 transition"
          @click="logout">
          Déconnexion
        </button>
      </div>
    </div>
    <div class="border-b border-gray-300"></div>
  </header>
</template>