// services/websocket.ts

import { io, type Socket } from "socket.io-client";


let socket: Socket;

export function initWebSocket() {
  socket = io('http://localhost:3001', {
  });

  socket.on('connect', () => {
    console.log('✅ Connecté au WebSocket :', socket.id);
  });

  socket.on('disconnect', () => {
    console.log('❌ Déconnecté du WebSocket');
  });

  socket.on('message', (data) => {
    console.log('📨 Message reçu :', data);
  });
}

// api management
// history messages
export async function fetchHistory() {
  const res = await fetch('http://localhost:3002/history');
  if (!res.ok) throw new Error('Erreur lors du chargement de l\'historique');
  return await res.json();
}

export async function fetchRooms() {
  const res = await fetch('http://localhost:3002/rooms');
  if (!res.ok) throw new Error('Erreur lors du chargement des rooms');
  return await res.json();
}
export function getSocketId() {
  return socket?.id;
}


// message management

export function sendMessageAll(messageAll: string, pseudo: string, imageUrl:string) {
  if (socket) {
    socket.emit('messageAll', { content: messageAll, pseudo, imageUrl });
  }
}

export function onMessageAll(callback: (data: any) => void) {
  if (socket) {
    socket.on('messageAll', callback);
  }
}

export function offMessageAll(callback: (data: any) => void) {
  if (socket) {
    socket.off('messageAll', callback);
  }
}

// room messages

// ...existing code...

export function onMessage(callback: (data: any) => void) {
  if (socket) {
    socket.on('message', callback);
  }
}
export function offMessage(callback: (data: any) => void) {
  if (socket) {
    socket.off('message', callback);
  }
}

export function sendMessage(roomId: string, message: string, pseudo?: string, imageUrl?: string) {
  if (socket) {
    socket.emit('message', { roomId, message, pseudo, imageUrl });
  }
}

// room management

export function emitCreateRoom(room: { id: number; name: string; gameId: number }) {
  if (socket) {
    socket.emit("create-room", room);
  }
}

export function emitDeleteRoom(roomId: number, gameId: number) {
  if (socket) {
    socket.emit("delete-room", { roomId, gameId });
  }
}

export function onRoomCreated(callback: (room: { id: number; name: string; gameId: number }) => void) {
  if (socket) {
    socket.on("room-created", callback);
  }
}

export function onRoomDeleted(callback: (data: { roomId: number; gameId: number }) => void) {
  if (socket) {
    socket.on("room-deleted", callback);
  }
}

export function joinRoom(roomId: string) {
  if (socket) {
    socket.emit('join-room', roomId);
  }
}
