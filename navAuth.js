(async function () {
  const authBtn = document.getElementById("authBtn");       // butonul Login
  const logoutBtn = document.getElementById("logoutBtn");   // butonul Logout
  const logoutLink = document.getElementById("logoutLink");

  if (!authBtn || !logoutBtn || !logoutLink) return;

  // daca exista deja badge, nu-l mai duplicam
  function removeExistingBadge() {
    const existing = document.getElementById("userBadge");
    if (existing) existing.remove();
  }

  function showLoggedOut() {
    removeExistingBadge();
    authBtn.style.display = "inline-block";   // arata Login
    logoutBtn.style.display = "none";         // ascunde Logout
  }

  function showLoggedIn(username) {
    // ascunde Login complet
    authBtn.style.display = "none";

    // arata Logout
    logoutBtn.style.display = "inline-block";

    // creeaza badge negru cu text alb in stanga butonului Logout
    removeExistingBadge();
    const badge = document.createElement("a");
    badge.id = "userBadge";
    badge.className = "user-badge";
    badge.href = "contulmeu.php";
    badge.textContent = username;

    // il punem INAINTE de logoutBtn (deci in stanga)
    logoutBtn.parentNode.insertBefore(badge, logoutBtn);
  }

  // default (ca sa nu dispara tot)
  showLoggedOut();

  try {
    const res = await fetch("api/me.php", { credentials: "include" });
    if (!res.ok) {
      showLoggedOut();
      return;
    }

    const data = await res.json().catch(() => null);
    if (!data || !data.ok || !data.user) {
      showLoggedOut();
      return;
    }

    const username = data.user.display_name || "User";
    showLoggedIn(username);

    // logout
    logoutLink.onclick = async (e) => {
      e.preventDefault();
      try {
        await fetch("logout.php", { credentials: "include" });
      } catch (err) {}
      window.location.href = "index.html";
    };

  } catch (err) {
    showLoggedOut();
  }
})();
