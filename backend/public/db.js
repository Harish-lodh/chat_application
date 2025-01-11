// db.js
const { MongoClient } = require('mongodb');

const url = 'mongodb://localhost:27017';
const dbName = 'chat_application';
let db;

const connectDB = async () => {
  if (db) return db;
  try {
    const client = new MongoClient(url);
    await client.connect();
    console.log("Connected successfully to MongoDB");
    global.db = client.db(dbName);
    return db;
  } catch (error) {
    console.error("Error connecting to MongoDB:", error);
    throw error;
  }
};

module.exports =  connectDB ;
