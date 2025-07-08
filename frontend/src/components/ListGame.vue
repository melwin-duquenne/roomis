<script setup lang="ts">
import { ref, onMounted } from 'vue';
import GameCard from './listgame/GameCard.vue';
import RoomList from './listgame/RoomList.vue';
import { emitCreateRoom, emitDeleteRoom, fetchRooms, joinRoom, onRoomCreated, onRoomDeleted } from '../services/websocket';
import { getCookie, getJwtFromCookie } from '../services/IsConnected';
import Room from './listgame/Room.vue';
import Morpion from '../jeux/morpion/morpion.vue';
import chess  from '../jeux/chess/chess.vue' ; 
import { onRoomPlayers, onRoomFull } from '../services/websocket';
const myPseudo = getCookie('pseudo');

const games = ref([
  { id: 1, title: 'Echec', image: '/images/game/echec.jpg' },
  { id: 2, title: 'Morpion', image: '/images/game/mortpion.png' },
  { id: 3, title: 'Jeu 3', image: '/images/game3.jpg' },
]);

const selectedGame = ref<null | { id: number; title: string; image: string }>(null);
const roomsByGame = ref<{ [gameId: number]: { id: number; name: string; creator: string; playersCount?: number }[] }>({});

function selectGame(game: { id: number; title: string; image: string }) {
  selectedGame.value = game;
}

function createRoom(name: string) {
  if (!selectedGame.value) return;
  const gameId = selectedGame.value.id;
  const room = { id: Date.now(), name, gameId, creator: myPseudo };
  emitCreateRoom(room); // Émet au serveur
}

function deleteRoom(roomId: number) {
  if (!selectedGame.value) return;
  const gameId = selectedGame.value.id;
  emitDeleteRoom(roomId, gameId); // Émet au serveur
}

// Synchronisation en temps réel
onMounted( async () => {
  onRoomCreated(room => {
    if (!roomsByGame.value[room.gameId]) {
      roomsByGame.value[room.gameId] = [];
    }
    // Évite les doublons
    if (!roomsByGame.value[room.gameId].some(r => r.id === room.id)) {
      roomsByGame.value[room.gameId].push({ id: room.id, name: room.name, creator: room.creator, playersCount: 0 });
    }
  });
  onRoomDeleted(({ roomId, gameId }) => {
    if (roomsByGame.value[gameId]) {
      roomsByGame.value[gameId] = roomsByGame.value[gameId].filter(room => room.id !== roomId);
    }
  });
  try {
    const rooms = await fetchRooms();
    rooms.forEach((room: { id: number; name: string; gameId: number; creator: string }) => {
      if (!roomsByGame.value[room.gameId]) {
        roomsByGame.value[room.gameId] = [];
      }
      if (!roomsByGame.value[room.gameId].some(r => r.id === room.id)) {
        roomsByGame.value[room.gameId].push({
          id: room.id,
          name: room.name,
          creator: room.creator,
          playersCount: 0
        });
      }
    });
  } catch (e) {
    console.error('Erreur chargement rooms', e);
  }

  onRoomPlayers(({ roomId, count }) => {
    for (const gameId in roomsByGame.value) {
      const idx = roomsByGame.value[gameId].findIndex(r => r.id === Number(roomId));
      if (idx !== -1) {
        // Remplace l'objet pour déclencher la réactivité
        const oldRoom = roomsByGame.value[gameId][idx];
        roomsByGame.value[gameId][idx] = { ...oldRoom, playersCount: count };
      }
    }
  });

  onRoomFull((roomId) => {
    alert("Cette salle est pleine !");
  });
});

const emit = defineEmits(['join-room'])
const joinedRoom = ref<{ id: number, name: string } | null>(null)

function handleJoinRoom(roomId: number, roomName: string) {
  joinedRoom.value = { id: roomId, name: roomName }
  joinRoom(roomId.toString())
  emit('join-room', roomId, roomName) // <-- ajoute la room dans le chat flottant
}

function handleLeaveRoom() {
  joinedRoom.value = null
}
</script>

<template>
  <div class="w-full flex flex-col items-center">
    <div v-if="joinedRoom">
      <Room :room-id="joinedRoom.id" :room-name="joinedRoom.name" @leave-room="handleLeaveRoom" />    
      <Morpion v-if="selectedGame && selectedGame.title === 'Morpion'" :room-id="joinedRoom.id" />
      <chess v-else-if="selectedGame && selectedGame.title === 'Echec'" :room-id="joinedRoom.id" />
    </div>
    <div v-else-if="!selectedGame" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 gap-4 w-1/2">
      <GameCard
        v-for="game in games"
        :key="game.id"
        :game="game"
        @select="selectGame"
      />
    </div>
    <RoomList
      v-else-if="getJwtFromCookie()"
      :game="selectedGame"
      :rooms="roomsByGame[selectedGame.id] || []"
      @back="selectedGame = null"
      @create-room="createRoom"
      @delete-room="deleteRoom"
      @join-room="handleJoinRoom"
    />
  </div>
</template>