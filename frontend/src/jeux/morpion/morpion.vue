<template>
  <h1>Le jeu du morpion</h1>
  <div id="Jeu">
    <div v-for="row in 3" :key="row">
      <button
        v-for="col in 3"
        :key="3 * (row - 1) + (col - 1)"
        :disabled="board[3 * (row - 1) + (col - 1)] || gameOver || myId !== currentPlayerId"
        @click="play(3 * (row - 1) + (col - 1))"
      >
        {{ board[3 * (row - 1) + (col - 1)] }}
      </button>
    </div>
    <div id="StatutJeu" v-html="status"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { getSocketId, offMessage, onMessage, sendMessage } from '../../services/websocket'

const board = ref<(string | null)[]>(Array(9).fill(null))
const players = ['X', 'O']
const turn = ref(0)
const gameOver = ref(false)
const winner = ref<string | null>(null)
const props = defineProps<{ roomId: number | string }>()
const myId = ref<string | undefined>()
const playerIds = ref<string[]>([])
const currentPlayerId = ref<string | null>(null)

function play(index: number) {
  if (gameOver.value || board.value[index]) return
  // Permet au premier joueur de jouer, mais bloque ensuite tant qu'il est seul
  if (playerIds.value.length === 1 && myId.value !== currentPlayerId.value) return
  if (playerIds.value.length > 1 && myId.value !== currentPlayerId.value) return
  sendMessage(
    props.roomId.toString(),
    JSON.stringify({ type: 'move', index, player: players[turn.value], playerId: myId.value })
  )
}
function checkWinner(player: string): boolean {
  const b = board.value
  const wins = [
    [0,1,2],[3,4,5],[6,7,8], // rows
    [0,3,6],[1,4,7],[2,5,8], // cols
    [0,4,8],[2,4,6]          // diags
  ]
  return wins.some(line => line.every(i => b[i] === player))
}

const status = computed(() => {
  if (winner.value) {
    return `Le joueur <b>${winner.value}</b> a gagné ! <br /><a href="" @click.prevent="reset">Rejouer</a>`
  }
  if (gameOver.value) {
    return `Match Nul ! <br /><a href="" @click.prevent="reset">Rejouer</a>`
  }
  if (!currentPlayerId.value) {
    return `En attente d'un deuxième joueur...`
  }
  return `Le jeu peut commencer !<br /> Joueur <b>${players[turn.value]}</b> (${currentPlayerId.value === myId.value ? 'Vous' : 'Adversaire'}) c'est votre tour.`
})

function reset(e?: Event) {
  if (e) e.preventDefault()
  sendMessage(props.roomId.toString(), JSON.stringify({ type: 'reset' }))
  board.value = Array(9).fill(null)
  turn.value = 0
  gameOver.value = false
  winner.value = null
  playerIds.value = []
  currentPlayerId.value = null
}

onMounted(() => {
  myId.value = getSocketId()
  if (!playerIds.value.includes(myId.value!)) {
    playerIds.value.push(myId.value!)
  }
  // Si on est le premier joueur, on attend un adversaire
  if (playerIds.value.length === 1 && myId.value) {
    currentPlayerId.value = myId.value
  }
  const handler = (data: any) => {
    if (data.roomId?.toString() !== props.roomId.toString()) return
    try {
      const payload = JSON.parse(data.content)
      if (payload.type === 'move') {
        applyMove(payload.index, payload.player, payload.playerId)
      }
      if (payload.type === 'reset') {
        board.value = Array(9).fill(null)
        turn.value = 0
        gameOver.value = false
        winner.value = null
        playerIds.value = []
        currentPlayerId.value = null
        }
    } catch {}
  }
  onMessage(handler)
  onUnmounted(() => offMessage(handler))
})

function applyMove(index: number, player: string, playerId: string) {
  if (gameOver.value || board.value[index]) return
  board.value[index] = player
  // Ajoute le joueur à la liste si besoin
  if (!playerIds.value.includes(playerId)) {
    playerIds.value.push(playerId)
  }
  if (playerIds.value.length < 2) {
  // Si tu es seul, tu ne peux pas rejouer tant qu'un autre joueur n'est pas arrivé
  currentPlayerId.value = null
    } else {
    // Alterne le joueur courant
    const otherId = playerIds.value.find(id => id !== playerId)
    currentPlayerId.value = otherId || playerId
    }
  if (checkWinner(player)) {
    winner.value = player
    gameOver.value = true
    return
  }
  if (board.value.every(cell => cell)) {
    gameOver.value = true
    return
  }
  turn.value = 1 - players.indexOf(player)
}
</script>

<style>
#Jeu {
  display: inline-block;
  background-color: SteelBlue;
  margin: 0 auto;
  padding: 10px;
}
#Jeu div {
  margin: 0 auto;
  overflow: hidden;
}
#Jeu button {
  width: 100px;
  height: 100px;
  font-weight: bold;
  font-size: 50px;
  margin: 5px;
  float: left;
}
#StatutJeu {
  color: black;
  background: #eee;
  padding: 10px 0;
  text-align: center;
  font-size: 20px;
}
</style>