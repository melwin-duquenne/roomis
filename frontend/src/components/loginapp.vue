<script setup lang="ts">
import { ref } from 'vue';
import Login from './profil/login.vue';
import Register from './profil/register.vue';

const mode = ref<'login' | 'register'>('login');
</script>

<template>
 <div class="flex items-center justify-center fixed inset-0 bg-black bg-opacity-40 z-50">
    <div class="loginapp flex flex-col items-center justify-center w-1/3 bg-white p-8 rounded-xl shadow-xl mb-8 relative">
      <button class="absolute top-2 right-2 text-gray-500" @click="$emit('close')">✕</button>
      <div class="flex gap-4 mb-6">
        <button :class="[
          'px-6 py-2 rounded-lg transition-transform duration-200 font-semibold shadow-xl',
          mode === 'login'
            ? 'bg-blue-600 text-white shadow-lg scale-105'
            : 'bg-white text-gray-800 hover:shadow-lg hover:scale-105'
        ]" @click="mode = 'login'">
          Connexion
        </button>
        <button :class="[
          'px-6 py-2 rounded-lg transition-transform duration-200 font-semibold shadow-xl',
          mode === 'register'
            ? 'bg-blue-600 text-white shadow-lg scale-105'
            : 'bg-white text-gray-800 hover:shadow-lg hover:scale-105'
        ]" @click="mode = 'register'">
          Inscription
        </button>
      </div>

      <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-6">
        <Login v-if="mode === 'login'" />
        <Register v-else />
      </div>
    </div>
  </div>
</template>
