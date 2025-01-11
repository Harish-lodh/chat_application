const express = require("express");
const http = require("http");
const socketIo = require("socket.io");
const connectDB = require("./public/db.js");

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});

connectDB();

io.on("connection", (socket) => {
  console.log("A user connected");

  socket.on("loadPastMessages", async (data) => {
    try {
      const messages = await global.db.collection("chat_message")
        .find({
          $or: [
            { sender: data.sender, receiver: data.receiver },
            { sender: data.receiver, receiver: data.sender }
          ]
        })
        .toArray();
      socket.emit("pastMessages", messages);
    } catch (err) {
      console.error("Error fetching past messages:", err.message);
    }
  });

  socket.on("privateMessage", async (msg) => {
    console.log(msg);
    try {
      await global.db.collection("chat_message").insertOne(msg);
      // Emit the message to the recipient
      socket.to(msg.receiver).emit("privateMessage", msg);
    } catch (err) {
      console.error("Error inserting message into the database:", err.message);
    }
  });

  socket.on("join", (username) => {


    socket.join(username);
    console.log(`${username} joined the chat`);
  });

  socket.on("disconnect", () => {
    console.log("A user disconnected");
  });
  
});

server.listen(3001, () => {
  console.log("Server is running on http://localhost:3001");
});
