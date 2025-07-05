<script setup lang="ts">
import { ref, reactive, onMounted, onUnmounted, watch, nextTick, defineProps, defineEmits } from 'vue'
import { fetchHistory, getSocketId, offMessage, offMessageAll, onMessage, onMessageAll, sendMessage, sendMessageAll } from '../../services/websocket'
import { getCookie } from '../../services/IsConnected'

const props = defineProps<{
  joinedRooms?: { id: number|string, name: string }[]
}>()
const emit = defineEmits(['leave-room'])

const user = getCookie('pseudo')
const img = getCookie('image')
const isOpen = ref(false)
const tabs = ref(['All'])
const selectedTab = ref('All')
const newMessage = ref('')
const myPseudo = ref('')
const myImageUrl = ref('')
const messages = reactive<{ [key: string]: { text: string; from: 'user' | 'bot' | 'other'; pseudo?: string; imageUrl?: string; }[] }>({
  'All': []
})

let handlerAll: ((data: any) => void) | null = null
let handlerRoom: ((data: any) => void) | null = null
// Met à jour les onglets quand joinedRooms change
watch(
  () => props.joinedRooms,
  (rooms) => {
    console.log('props.joinedRooms:', rooms)
    tabs.value = ['All', ...(rooms?.map(r => r.id.toString()) || [])]
    rooms?.forEach(room => {
      if (!messages[room.id.toString()]) messages[room.id.toString()] = []
    })
    if (!tabs.value.includes(selectedTab.value)) selectedTab.value = 'All'
  },
  { immediate: true }
)

onMounted(async () => {
  myPseudo.value = user || 'Anonyme'
  myImageUrl.value = img || 'localhost:8000/cf4a3c1b44e3fe154641.png'

  // Charge l'historique pour "All"
  if (selectedTab.value === 'All') {
    try {
      const history = await fetchHistory();
      messages['All'].splice(0, messages['All'].length, ...history.map(msg => ({
        text: msg.content,
        from: msg.sender === getSocketId() ? 'user' : 'other',
        pseudo: msg.pseudo,
        imageUrl: msg.imageUrl
      })));
    } catch (e) {
      console.error('Erreur chargement historique', e);
    }
  }
})

onMounted(() => {
  // Handler pour All
  handlerAll = (data: any) => {
    messages['All'].push({
      text: data.content,
      from: data.sender === getSocketId() ? 'user' : 'other',
      pseudo: data.pseudo,
      imageUrl: data.imageUrl
    })
  }
  onMessageAll(handlerAll)

  handlerRoom = (data: any) => {
    try {
    const payload = JSON.parse(data.content)
    if (payload.type === 'move' || payload.type === 'reset') return
    } catch {}
    if (!data.roomId) return
    if (!messages[data.roomId]) messages[data.roomId] = []
    messages[data.roomId].push({
      text: data.content,
      from: data.sender === getSocketId() ? 'user' : 'other',
      pseudo: data.pseudo,
      imageUrl: data.imageUrl
    })
  }
  onMessage(handlerRoom)

})


onUnmounted(() => {
  if (handlerAll) offMessageAll(handlerAll)
  if (handlerRoom) offMessage(handlerRoom)
})

function sendMessageHandler() {
  const text = newMessage.value.trim()
  if (!text) return
  if (selectedTab.value === 'All') {
    sendMessageAll(text, myPseudo.value, myImageUrl.value)
  } else {
    // Envoie dans la room (le nom de l'onglet = roomId)
    sendMessage(selectedTab.value, text, myPseudo.value, myImageUrl.value)
    if (!messages[selectedTab.value]) messages[selectedTab.value] = []
    messages[selectedTab.value].push({
      text,
      from: 'user',
      pseudo: myPseudo.value,
      imageUrl: myImageUrl.value
    })
  }
  newMessage.value = ''
}

function leaveRoom(tabName: string) {
  emit('leave-room', tabName)
  selectedTab.value = 'All'
}

const messagesContainer = ref<HTMLElement | null>(null)

watch(
  () => messages[selectedTab.value]?.length,
  async () => {
    await nextTick()
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  }
)


function openRoomTab(roomId: number|string) {
  isOpen.value = true
  selectedTab.value = roomId.toString()
}
defineExpose({ openRoomTab })
</script>

<template>
  <div>
    <!-- Fixed Chat Button -->
    <button
      @click="isOpen = true"
      class="fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-4 rounded-full shadow-lg hover:bg-blue-700 transition"
    >
      💬
    </button>

    <!-- Chat Modal -->
    <div v-if="isOpen" class="fixed bottom-20 right-4 z-50 w-96 max-w-full bg-white rounded-xl shadow-xl flex flex-col border border-gray-300">
      <!-- Modal Header with Tabs -->
      <div class="flex border-b border-gray-200">
        <button
          v-for="tab in tabs"
          :key="tab"
          @click="selectedTab = tab"
          class="flex-1 py-2 text-sm font-medium hover:bg-gray-100"
          :class="{ 'border-b-2 border-blue-600 text-blue-600': selectedTab === tab }"
        >
          {{ tab === 'All' ? 'All' : (props.joinedRooms?.find(r => r.id.toString() === tab)?.name || tab) }}
          <span
            v-if="tab !== 'All'"
            @click.stop="leaveRoom(tab)"
            class="ml-2 text-red-500 cursor-pointer"
            title="Quitter la room"
          >✖</span>
        </button>
        <button @click="isOpen = false" class="px-3 text-gray-400 hover:text-red-500">✖</button>
      </div>

      <!-- Modal Body -->
      <div ref="messagesContainer"
        class="flex-1 p-4 max-h-80 overflow-y-auto space-y-2">
        <div
          v-for="(msg, index) in messages[selectedTab]"
          :key="index"
          class="p-2 rounded-lg"
          :class="msg.from === 'user' || msg.pseudo === user ? 'bg-blue-100 self-end text-right' : 'bg-gray-100 self-start text-left'"
        >
          <div class="flex items-center w-full gap-2 mb-1"
            :class="msg.from === 'user' || msg.pseudo === user ? 'justify-start' : 'justify-end'">
            <img v-if="msg.imageUrl" :src="'http://localhost:8000' + msg.imageUrl" alt="avatar" class="w-8 h-8 rounded-full object-cover mb-1" />
            <span v-if="msg.pseudo"><b>{{ msg.pseudo }}</b></span>
          </div>
          {{ msg.text }}
        </div>
      </div>

      <!-- Modal Input -->
      <form @submit.prevent="sendMessageHandler" class="p-3 border-t border-gray-200 flex gap-2">
        <input
          v-model="newMessage"
          type="text"
          placeholder="Écrire un message..."
          class="flex-1 px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Envoyer</button>
      </form>
    </div>
  </div>
</template>