<script setup lang="ts">
import { ref } from 'vue';

const pseudo = ref('');
const email = ref('');
const password = ref('');
const confirm = ref('');
const message = ref('');
const isSuccess = ref(false);

function isPasswordValid(pwd: string) {
  // Au moins 8 caractères, une majuscule, une minuscule, un chiffre, un caractère spécial
  return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(pwd);
}
async function submit() {
  message.value = '';
  isSuccess.value = false;

  if (password.value !== confirm.value) {
    message.value = 'Les mots de passe ne correspondent pas';
    isSuccess.value = false;
    return;
  }
  if (!isPasswordValid(password.value)) {
      message.value = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
      return;
  }
  try {
    const response = await fetch('http://localhost:8000/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/ld+json',
        'Accept': 'application/ld+json'
      },
      body: JSON.stringify({
        pseudo: pseudo.value,
        email: email.value,
        password: password.value
      })
    });

    const data = await response.json();

    if (response.ok && data.success) {
      message.value = 'Inscription réussie !';
      isSuccess.value = true;
      pseudo.value = '';
      email.value = '';
      password.value = '';
      confirm.value = '';
    } else {
      message.value = data.message || "Erreur lors de l'inscription";
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
      <label class="text-left">Pseudo</label>
      <input class="bg-gray-100 mt-1 rounded" v-model="pseudo" required />
    </div>
    <div class="flex flex-col mt-4">
      <label class="text-left">Email</label>
      <input class="bg-gray-100 mt-1 rounded" v-model="email" type="email" required />
    </div>
    <div class="flex flex-col mt-4">
      <label class="text-left">Mot de passe</label>
      <input class="bg-gray-100 mt-1 rounded" v-model="password" type="password" required />
    </div>
    <div class="flex flex-col mt-4">
      <label class="text-left">Confirmer le mot de passe</label>
      <input class="bg-gray-100 mt-1 rounded" v-model="confirm" type="password" required />
    </div>
    <div v-if="message" class="mt-4 text-center" :class="isSuccess ? 'text-green-600' : 'text-red-600'">
      {{ message }}
    </div>
    <div class="w-full flex justify-end">
      <button class="mt-4 py-2 px-3 bg-blue-700 text-white" type="submit">S'inscrire</button>
    </div>
  </form>
</template>