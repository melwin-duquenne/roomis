<template>
  <div class="max-w-3xl mx-auto p-6 bg-white rounded-2xl shadow-md space-y-6">
    <div class="flex items-center space-x-6">
      <img
        v-if="previewImage"
        :src="'http://localhost:8000/' + previewImage"
        alt="Photo de profil"
        class="w-24 h-24 rounded-full object-cover border-2 border-gray-300"
      />
      <div v-else class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-3xl">
        {{ form.pseudo?.charAt(0).toUpperCase() || "?" }}
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Prénom</label>
          <input data-testid="update-prenom" v-model="form.prenom" class="mt-1 w-full border rounded p-2" type="text" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Nom</label>
          <input data-testid="update-Nom" v-model="form.nom" class="mt-1 w-full border rounded p-2" type="text" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Pseudo</label>
          <input data-testid="update-pseudo" v-model="form.pseudo" class="mt-1 w-full border rounded p-2" type="text" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Photo</label>
          <input type="file" @change="handleImageUpload" class="mt-1 w-full border rounded p-2" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Nouveau mot de passe</label>
          <input data-testid="update-new-password" v-model="form.password" type="password" class="mt-1 w-full border rounded p-2" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
          <input data-testid="update-confirm-password" v-model="form.passwordConfirm" type="password" class="mt-1 w-full border rounded p-2" />
        </div>
      </div>

      <div v-if="message" :class="success ? 'text-green-600' : 'text-red-600'">
        {{ message }}
      </div>
      <div class="flex justify-between mt-4">
        <button
          type="submit"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          Mettre à jour
        </button>
        <button
          type="button"
          data-testid="delete-account"
          @click="deleted"
          class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
          Suprimer le compte
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'

interface UserForm {
  prenom: string
  nom: string
  pseudo: string
  image?: string | null
  password: string
  passwordConfirm: string
}

const props = defineProps<{ user: Partial<UserForm> }>()

const form = reactive<UserForm>({
  prenom: props.user.prenom ?? '',
  nom: props.user.nom ?? '',
  pseudo: props.user.pseudo ?? '',
  image: props.user.image ?? null,
  password: '',
  passwordConfirm: ''
})

const imageFile = ref<File | null>(null)
const previewImage = ref<string | null>(form.image)
const message = ref<string>('')
const success = ref<boolean>(false)
const token = getJwtFromCookie()
function getJwtFromCookie(): string | null {
  const match = document.cookie.match(new RegExp('(^| )jwt=([^;]+)'))
  return match ? match[2] : null
}

const handleImageUpload = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  imageFile.value = file
  previewImage.value = URL.createObjectURL(file)
}

// Fonction d’upload qui renvoie le chemin de l’image uploadée côté serveur
const uploadImage = async (file: File): Promise<string> => {
  
  if (!token) throw new Error("Utilisateur non authentifié.")

  const uploadForm = new FormData()
  uploadForm.append('image', file)

  const res = await fetch('http://localhost:8000/api/upload/user-image', {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`
    },
    body: uploadForm
  })

  if (!res.ok) {
    const err = await res.json()
    throw new Error(err.error || 'Erreur lors de l\'upload')
  }

  const data = await res.json()
  return data.imagePath
}

const deleted = async () => { 
  await fetch('http://localhost:8000/api/me/deleted', {
  method: 'DELETE',
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
}
const submit = async () => {
  message.value = ''
  success.value = false

  if (form.password && form.password !== form.passwordConfirm) {
    message.value = "Les mots de passe ne correspondent pas."
    return
  }

  try {
    // Upload de l'image seulement si un fichier a été sélectionné
    if (imageFile.value) {
      form.image = await uploadImage(imageFile.value)
    }

    const token = getJwtFromCookie()
    if (!token) {
      message.value = "Utilisateur non authentifié."
      success.value = false
      return
    }

    const body = {
      prenom: form.prenom,
      nom: form.nom,
      pseudo: form.pseudo,
      image: form.image, // c’est un string (chemin)
      password: form.password || null
    }

    const res = await fetch('http://localhost:8000/api/me/edit', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/merge-patch+json',
        Authorization: `Bearer ${token}`
      },
      body: JSON.stringify(body)
    })

    if (!res.ok) {
      const err = await res.json()
      throw new Error(err.detail || 'Erreur lors de la mise à jour')
    }

    message.value = 'Profil mis à jour avec succès !'
    success.value = true
  } catch (e: any) {
    message.value = e.message
    success.value = false
  }
}
</script>

