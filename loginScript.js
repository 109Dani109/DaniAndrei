const express = require("express");
const mysql = require("mysql2/promise");
const bcrypt = require("bcrypt");
const cors = require("cors");

const app = express();
app.use(express.json());
app.use(cors());

// Conexiune DB
const db = mysql.createPool({
    host: "mysql",       // numele containerului
    user: "root",
    password: "parola_ta",
    database: "nume_baza"
});

// Ruta de login
app.post("/login", async (req, res) => {
    const { username, password } = req.body;

    try {
        const [users] = await db.query(
            "SELECT * FROM users WHERE username = ?",
            [username]
        );

        if (users.length === 0) {
            return res.status(400).json({ message: "User invalid" });
        }

        const user = users[0];

        const ok = await bcrypt.compare(password, user.password);
        if (!ok) {
            return res.status(400).json({ message: "Parola greșită" });
        }

        res.json({ message: "Login reușit" });
    } catch (err) {
        console.error(err);
        res.status(500).json({ message: "Eroare server" });
    }
});

app.listen(3000, () => console.log("Server pornit pe portul 3000"));
