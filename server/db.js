// server/db.js
const mysql = require("mysql2/promise");

const db = mysql.createPool({
    host: "lamp_mysql",   // numele containerului MySQL
    port: 3306,
    user: "user",
    password: "password",
    database: "studenti"
});

module.exports = db;
