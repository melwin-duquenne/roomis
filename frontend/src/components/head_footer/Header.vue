<script setup lang="ts">
import NameUser from '../profil/nameUser.vue';
import { getJwtFromCookie } from '../../services/IsConnected';

function logout() {
  document.cookie = "jwt=; Path=/; Max-Age=0; Secure; SameSite=Strict";
  window.location.reload();
}

</script>

<template>
  <header class="w-full bg-white shadow mb-10">
    <div class="flex items-center justify-between px-6 py-3">
      <div class="flex items-center">
        <img src="#" alt="Logo" class="h-10 w-auto" />
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