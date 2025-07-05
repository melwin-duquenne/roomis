<script setup lang="ts">
import { ref } from 'vue';

const email = ref('');
const password = ref('');
const message = ref('');
const isSuccess = ref(false);

async function submit() {
  message.value = '';
  isSuccess.value = false;

  try {
    const response = await fetch('http://localhost:8000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/ld+json',
        'Accept': 'application/ld+json'
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    });

    const data = await response.json();

    if (response.ok && data.token) {
      // Place le token dans un cookie sécurisé (expires dans 1h)
      document.cookie = `jwt=${data.token}; Path=/; Secure; SameSite=Strict; Max-Age=3600`;
      message.value = 'Connexion réussie !';
      isSuccess.value = true;
      setTimeout(() => window.location.reload(), 3000); // ferme la modal après 3s

      // Tu peux ici rediriger ou rafraîchir l'app
    } else {
      message.value = data.message || "Erreur lors de la connexion";
      isSuccess.value = false;
    }
  } catch (e) {
    message.value = "Erreur réseau ou serveur";
    isSuccess.value = false;
  }
}
</script>

<template>
  <form @submit.prevent="submit">
    <div class="flex flex-col">
      <label class="text-left">Email</label>
      <input class="bg-gray-100 mt-1 rounded" v-model="email" type="email" required />
    </div>
    <div class="flex flex-col mt-4">
      <label class="text-left">Mot de passe</label>
      <input class="bg-gray-100 mt-1 rounded" v-model="password" type="password" required />
    </div>
    <div v-if="message" class="mt-4 text-center" :class="isSuccess ? 'text-green-600' : 'text-red-600'">
      {{ message }}
    </div>
    <div class="w-full flex justify-end">
      <button class="mt-4 py-2 px-3 bg-blue-700 text-white" type="submit">Se connecter</button>
    </div>
  </form>
</template>