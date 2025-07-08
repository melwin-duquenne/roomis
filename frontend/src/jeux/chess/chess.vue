<script setup lang="ts">
import 'vue3-chessboard/style.css';
import { TheChessboard, type BoardApi, type BoardConfig } from 'vue3-chessboard';
import { joinRoom, onChessMove, sendChessMove } from '../../services/websocket';
import { onMounted } from 'vue';

let boardAPI: BoardApi | null = null;
const props = defineProps<{ roomId: number | string }>();
const boardConfig: BoardConfig = { coordinates: true };

function handleCheckmate(isMated: string) {
  if (isMated === 'w') alert('Black wins!');
  else alert('White wins!');
}

// Quand un coup est joué localement
function onMove(move: any) {
  const moveStr = move.san || move.uci || '';
  console.log('envoi chess-move', moveStr, move);
  sendChessMove(props.roomId.toString(), moveStr);
}

onMounted(() => {
  onChessMove(({ roomId, move }) => {
    console.log('reçu chess-move', roomId, move);
    if (roomId.toString() === props.roomId.toString() && boardAPI) {
      boardAPI.move(move);
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
    <TheChessboard
      :board-config="boardConfig"
      @board-created="(api) => (boardAPI = api)"
         @move="onMove"
      @checkmate="handleCheckmate"
    />
  </section>
</template>