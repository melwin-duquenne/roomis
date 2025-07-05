const { Server } = require("socket.io");
const { MongoClient } = require("mongodb");

const uri = process.env.MONGO_URL || "mongodb://mongodb:27017"; // mongodb si dans docker-compose
const client = new MongoClient(uri);

const express = require('express');
const app = express();
// serveur http pour l'api historique
const httpServer = app.listen(3002, () => {
  console.log('🌐 Serveur HTTP pour l\'API historique sur le port 3002');
});

client.connect()
  .then(() => {
    console.log("✅ Connecté à MongoDB");

    const db = client.db("all");
    const messagesCollection = db.collection("messages");
// autorisation cors pour l'api
    app.use((req, res, next) => {
    res.header("Access-Control-Allow-Origin", "*"); // autorise toutes les origines
    res.header("Access-Control-Allow-Methods", "GET,POST");
    res.header("Access-Control-Allow-Headers", "Content-Type, Authorization");
    if (req.method === "OPTIONS") {
      return res.sendStatus(200);
    }
    next();
  });
// historique des messageALL
    app.get('/history', async (req, res) => {
        try {
          const messages = await messagesCollection
            .find({ roomId: "all" })
            .sort({ timestamp: 1 })
            .toArray();
          res.json(messages);
        } catch (err) {
          res.status(500).json({ error: "Erreur lors de la récupération de l'historique" });
        }
      });

      // récupération des rooms
    app.get('/rooms', async (req, res) => {
      try {
        const rooms = await db.collection("rooms").find({}).toArray();
        res.json(rooms);
      } catch (err) {
        res.status(500).json({ error: "Erreur lors de la récupération des rooms" });
      }
    });
// serveur socket.io 
    const io = new Server(3001, {
      cors: {
        origin: "*",
        methods: ["GET", "POST"],
      },
    });
//connecté au serveur socket.io
    io.on("connection", (socket) => {
      console.log("🔌 Client connecté :", socket.id);
//ROOM
      socket.on("create-room", (room) => {
        io.emit("room-created", room);
      });

      // Suppression d'une room
      socket.on("delete-room", async ({ roomId, gameId }) => {
        io.emit("room-deleted", { roomId, gameId });
        try {
          await db.collection("rooms").deleteOne({ id: roomId, gameId: gameId });
          console.log("🗑️ Room supprimée de la DB");
        } catch (err) {
          console.error("❌ Erreur suppression room :", err);
        }
      });
      
      socket.on("join-room", (roomId) => {
        socket.join(roomId);
        console.log(`${socket.id} a rejoint la room ${roomId}`);
      });

      //ROOM MONGODB
      socket.on("create-room", async (room) => {
        io.emit("room-created", room);
        try {
          // Ajoute la room en BDD
          await db.collection("rooms").insertOne({
            ...room,
            createdAt: new Date(),
          });
          console.log("💾 Room sauvegardée en DB");
        } catch (err) {
          console.error("❌ Erreur sauvegarde room :", err);
        }
      });
//MESSAGE
      socket.on("messageAll", (messageAll) => {
        io.emit("messageAll", {
          sender: socket.id,
          content: messageAll.content,
          pseudo: messageAll.pseudo,
          imageUrl: messageAll.imageUrl,
        });

         try {
          messagesCollection.insertOne({
            roomId: "all", // si tu veux marquer que c’est global
            sender: socket.id,
            content: messageAll.content,
            pseudo: messageAll.pseudo,
            imageUrl: messageAll.imageUrl,
            timestamp: new Date(),
          });
          console.log("💾 messageAll sauvegardé en DB");
        } catch (err) {
          console.error("❌ erreur sauvegarde messageAll :", err);
        }
      });

      // Envoi de message dans une room spécifique
      socket.on("message", ({ roomId, message, pseudo, imageUrl }) => {
        io.to(roomId).emit("message", {
          roomId,
          content: message,
          sender: socket.id,
          pseudo,
          imageUrl
        });
      });

      socket.on("disconnect", () => {
        console.log("❌ Client déconnecté :", socket.id);
      });
    });
  })
  .catch(err => {
    console.error("❌ Erreur connexion MongoDB :", err);
  });
