// server/server.js
const express = require("express");
const cors = require("cors");
const db = require("./db");

const app = express();
app.use(express.json());
app.use(cors());

app.post("/login", async (req, res) => {
    const { username, password } = req.body;

    try {
        const [users] = await db.query(
            "SELECT * FROM users WHERE username = ?",
            [username]
        );

        if (users.length === 0) return res.status(400).json({ message: "User invalid" });

        const user = users[0];

        if (password !== user.password) return res.status(400).json({ message: "Parola greșită" });

        res.json({ message: "Login reușit" });
    } catch (err) {
        console.error("Eroare server:", err);
        res.status(500).json({ message: "Eroare server" });
    }
});

app.listen(3000, () => console.log("Server Node.js pornit pe portul 3000"));
