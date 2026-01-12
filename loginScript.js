(async function () {
  const authBtn = document.getElementById("authBtn");
  const authLink = document.getElementById("authLink");
  const logoutBtn = document.getElementById("logoutBtn");
  const logoutLink = document.getElementById("logoutLink");

  if (!authBtn || !authLink || !logoutBtn || !logoutLink) return;

  try {
    // API-ul tau ESTE aici
    const res = await fetch("api/me.php", { credentials: "include" });

    if (!res.ok) {
      // NU e logat
      authBtn.style.display = "inline-block";
      logoutBtn.style.display = "none";
      authLink.textContent = "Login";
      authLink.href = "login.html";
      authLink.classList.remove("user-badge");
      return;
    }

    const data = await res.json();
    if (!data.ok || !data.user) throw new Error();

    // ESTE LOGAT
    const username =
      data.user.nume ||
      data.user.username ||
      data.user.email;

    // 🔹 Login dispare
    authBtn.style.display = "inline-block";
    authLink.textContent = username;
    authLink.href = "contulmeu.php";
    authLink.classList.add("user-badge");

    // 🔹 Logout apare
    logoutBtn.style.display = "inline-block";

    // 🔹 Logout action
    logoutLink.onclick = async (e) => {
      e.preventDefault();
      await fetch("logout.php", { credentials: "include" });
      window.location.href = "index.html";
    };

  } catch (e) {
    // fallback: ca nelogat
    authBtn.style.display = "inline-block";
    logoutBtn.style.display = "none";
    authLink.textContent = "Login";
    authLink.href = "login.html";
    authLink.classList.remove("user-badge");
  }
})();
