const { Server } = require("socket.io");
const mongoose = require("mongoose");
const Room = require("./models/room");
const Message = require("./models/message");
const express = require("express");
const app = express();

const MONGO_URL = "mongodb://mongodb:27017/all";

mongoose.connect(MONGO_URL, {
  useNewUrlParser: true,
  useUnifiedTopology: true,
})
.then(() => {
  console.log("✅ Connecté à MongoDB via Mongoose");

  // serveur HTTP pour l'API historique
  const httpServer = app.listen(3002, () => {
    console.log("🌐 Serveur HTTP API sur le port 3002");
  });

  // Middleware CORS
  app.use((req, res, next) => {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Methods", "GET,POST");
    res.header("Access-Control-Allow-Headers", "Content-Type, Authorization");
    if (req.method === "OPTIONS") return res.sendStatus(200);
    next();
  });

  

  // Endpoint historique
  app.get('/history', async (req, res) => {
    try {
      const messages = await Message.find({ roomId: "all" }).sort({ timestamp: 1 });
      res.json(messages);
    } catch (err) {
      res.status(500).json({ error: "Erreur récupération historique" });
    }
  });

  // Endpoint rooms
  app.get('/rooms', async (req, res) => {
    try {
      const rooms = await Room.find({});
      res.json(rooms);
    } catch (err) {
      res.status(500).json({ error: "Erreur récupération des rooms" });
    }
  });

  // SOCKET.IO
  const io = new Server(3001, {
    cors: {
      origin: "*",
      methods: ["GET", "POST"],
    },
  });

  const roomPlayers = {};

  io.on("connection", (socket) => {
    console.log("🔌 Client connecté :", socket.id);
    
    // search rooms
    socket.on('search_rooms', async ({ name }) => {
      try {
        const rooms = await Room.find({
          name: { $regex: name, $options: 'i' }
        }).limit(20);

        socket.emit('search_results', { rooms });
      } catch (err) {
        console.error('Erreur recherche room :', err);
        socket.emit('search_results', { rooms: [] });
      }
    });

    socket.on("join-room", (roomId) => {
      roomPlayers[roomId] = roomPlayers[roomId] || [];
      if (!roomPlayers[roomId].includes(socket.id)) {
        roomPlayers[roomId].push(socket.id);
      }
      const room = roomPlayers[roomId];

      // Attribution du rôle
      console.log(`🕹️ Requête de join pour la room ${roomId} par ${socket.id}`);
      if (!room.player1) {
        room.player1 = socket.id;
        console.log(`🕹️ Joueur 1 rejoint la room ${roomId}`);
      } else if (!room.player1 && !room.player2) {
        room.player2 = socket.id;
        console.log(`🕹️ Joueur 2 rejoint la room ${roomId}`);
      } else {
        room.spectators = socket.id;
        console.log(`👀 Spectateur rejoint la room ${roomId}`);
      }

      socket.join(roomId);
      io.to(roomId).emit("room-players", { roomId, count: roomPlayers[roomId].length, players: {
        player1: room.player1,
        player2: room.player2,
        spectators: room.spectators,
      }, });
    });

    socket.on("disconnect", () => {
      for (const roomId in roomPlayers) {
        const idx = roomPlayers[roomId].indexOf(socket.id);
        if (idx !== -1) {
          roomPlayers[roomId].splice(idx, 1);
          io.to(roomId).emit("room-players", { roomId, count: roomPlayers[roomId].length });
        }
      }
      console.log("❌ Client déconnecté :", socket.id);
    });

    socket.on("create-room", async (room) => {
      io.emit("room-created", room);
      try {
        await Room.create(room);
        console.log("💾 Room sauvegardée");
      } catch (err) {
        console.error("❌ Erreur sauvegarde room :", err);
      }
    });

    socket.on("delete-room", async ({ roomId, gameId }) => {
      io.emit("room-deleted", { roomId, gameId });
      try {
        await Room.deleteOne({ id: roomId, gameId });
        console.log("🗑️ Room supprimée");
      } catch (err) {
        console.error("❌ Erreur suppression room :", err);
      }
    });

    socket.on("chess-move", ({ roomId, move }) => {
      io.to(roomId).emit("chess-move", { roomId, move });
    });

    socket.on("messageAll", async (messageAll) => {
      io.emit("messageAll", {
        sender: socket.id,
        content: messageAll.content,
        pseudo: messageAll.pseudo,
        imageUrl: messageAll.imageUrl,
      });

      try {
        await Message.create({
          roomId: "all",
          sender: socket.id,
          content: messageAll.content,
          pseudo: messageAll.pseudo,
          imageUrl: messageAll.imageUrl,
          timestamp: new Date(),
        });
        console.log("💾 Message global sauvegardé");
      } catch (err) {
        console.error("❌ Erreur sauvegarde messageAll :", err);
      }
    });

    socket.on("message", ({ roomId, message, pseudo, imageUrl }) => {
      io.to(roomId).emit("message", {
        roomId,
        content: message,
        sender: socket.id,
        pseudo,
        imageUrl
      });
    });
  });
})
.catch((err) => {
  console.error("❌ Erreur connexion MongoDB :", err);
});
