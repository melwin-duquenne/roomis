<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { joinRoom, onChessMove, sendChessMove, getSocketId, onRoomPlayers } from '../../services/websocket';
import 'vue3-chessboard/style.css';
import { TheChessboard, type BoardApi } from 'vue3-chessboard';

const currentTurn = ref<'w' | 'b'>('w');
const playerRole = ref<'player1' | 'player2' | 'spectator'>('spectator');
const playerColor = ref<'w' | 'b' | null>(null);
let boardAPI: BoardApi | null = null;

const props = defineProps<{ roomId: number | string }>();

function handleCheckmate(isMated: string) {
  if (isMated === 'w') alert('Black wins!');
  else alert('White wins!');
}
const isSpectator = computed(() => playerRole.value === 'spectator');
const boardConfig = computed(() => ({
  orientation: playerColor.value || 'w',
  // viewOnly: isSpectator.value, // désactive les déplacements
  highlight: true,
  coordinates: true,
}));

function onMove(move: any) {
  // Refuser si spectateur
  if (playerRole.value === 'spectator') {
    alert("Vous êtes spectateur, vous ne pouvez pas jouer.");
    return false;
  }

  // Refuser si mauvaise couleur
  if (move.color !== playerColor.value) {
    alert("Ce n'est pas votre couleur !");
    return false;
  }

  // Refuser si ce n'est pas le tour
  if (move.color !== currentTurn.value) {
    alert("Ce n'est pas votre tour !");
    return false;
  }

  // Envoi move valide
  const moveStr = move.san || move.uci || '';
  sendChessMove(props.roomId.toString(), moveStr);

  // Changer tour localement (ou attendre confirmation serveur si tu veux)
  currentTurn.value = currentTurn.value === 'w' ? 'b' : 'w';
}
const hasJoined = ref(false);

onMounted(async () => {
  if (!hasJoined.value) {
    joinRoom(props.roomId.toString());
    hasJoined.value = true;
  }

  const socketId = getSocketId();

  onRoomPlayers(({ players }) => {
    if (socketId === players.player1) {
      playerRole.value = 'player1';
      playerColor.value = 'w';
    } else if (socketId === players.player2) {
      playerRole.value = 'player2';
      playerColor.value = 'b';
    } else {
      playerRole.value = 'spectator';
      playerColor.value = null;
    }
  });

  onChessMove(({ roomId, move }) => {
    if (roomId.toString() === props.roomId.toString() && boardAPI) {
      boardAPI.move(move);
      // changer le tour car adversaire a joué
      currentTurn.value = currentTurn.value === 'w' ? 'b' : 'w';
    }
  });
});
</script>

<template>
  <section>
    <div>
      <button @click="boardAPI?.toggleOrientation()">
        Toggle orientation
      </button>
      <button @click="boardAPI?.resetBoard()">Reset</button>
      <button @click="boardAPI?.undoLastMove()">Undo</button>
      <button @click="boardAPI?.toggleMoves()">Threats</button>
    </div>
    <p>Est spectateur ? {{ isSpectator }}</p>
    <TheChessboard
      :board-config="boardConfig"
      @board-created="(api) => (boardAPI = api)"
      @move="onMove"
      @checkmate="handleCheckmate"
    />
  </section>
</template>