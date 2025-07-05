<script setup lang="ts">
import { onMounted, ref } from 'vue';
import {joinRoom, sendMessage } from '../services/websocket';
import Loginapp from '../components/loginapp.vue';
import Header from '../components/head_footer/Header.vue';
import ListGame from '../components/ListGame.vue';
import { getSpeudoImage } from '../services/TakeSpeudoImage';
import ChatTextuel from '../components/chat/ChatTextuel.vue';
import { getJwtFromCookie } from '../services/IsConnected';
const showLogin = ref(false);

const joinedRooms = ref<{ id: number|string, name: string }[]>([])
const chatTextuelRef = ref<any>(null)

function handleJoinRoom(roomId: number|string, roomName: string) {
  if (!joinedRooms.value.some(r => r.id === roomId)) {
    joinedRooms.value = [...joinedRooms.value, { id: roomId, name: roomName }] // <-- correction
    console.log('joinedRooms:', joinedRooms.value)

  }
  joinRoom(roomId.toString())
  chatTextuelRef.value?.openRoomTab(roomId)

}
// Quand tu quittes une room depuis ChatTextuel
function handleLeaveRoom(roomId: string) {
  if (roomId === 'ALL') {
    joinedRooms.value = []
  } else {
    joinedRooms.value = joinedRooms.value.filter(r => r.id.toString() !== roomId)
  }
}
function openLogin() {
  showLogin.value = true;
}
function closeLogin() {
  showLogin.value = false;
}
onMounted(() => {
  getSpeudoImage();
  joinRoom('home'); 
  sendMessage('home', 'Hello depuis Home.vue');
});
</script>

<template>
<Header @open-login="openLogin" />
  <div>
    <ListGame @join-room="handleJoinRoom" />
    <ChatTextuel
    ref="chatTextuelRef"
    v-if="getJwtFromCookie()" 
    :joined-rooms="joinedRooms"
    @leave-room="handleLeaveRoom"/>
    <Loginapp v-if="showLogin" @close="closeLogin" />
  </div>
</template>