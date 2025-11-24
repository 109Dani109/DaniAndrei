document.getElementById("btnAdauga").addEventListener("click", function () {
    const input = document.getElementById("inputActivitate");
    const activitate = input.value.trim();

    if (activitate !== "") {
        const lista = document.getElementById("listaActivitati");
        const li = document.createElement("li");

        // luni în limba română
        const luni = [
            "Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie",
            "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"
        ];

        const d = new Date();
        const zi = d.getDate();
        const luna = luni[d.getMonth()];
        const an = d.getFullYear();

        li.textContent = activitate + " - adăugată la: " + zi + " " + luna + " " + an;
        lista.appendChild(li);

        input.value = "";
    }
});