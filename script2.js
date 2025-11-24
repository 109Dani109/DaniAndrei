
window.addEventListener("load", function () {
    const detalii = document.getElementById("detalii");
    const dataProdus = document.getElementById("dataProdus");


    detalii.classList.add("ascuns");

    
    const luni = [
        "Ianuarie", "Februarie", "Martie", "Aprilie", "Mai", "Iunie",
        "Iulie", "August", "Septembrie", "Octombrie", "Noiembrie", "Decembrie"
    ];

    const d = new Date();
    const zi = d.getDate();
    const luna = luni[d.getMonth()];
    const an = d.getFullYear();


    dataProdus.textContent = zi + " " + luna + " " + an;
});



document.getElementById("btnDetalii").addEventListener("click", function () {
    const detalii = document.getElementById("detalii");

    detalii.classList.toggle("ascuns");

    if (detalii.classList.contains("ascuns")) {
        this.textContent = "Afișează detalii";
    } else {
        this.textContent = "Ascunde detalii";
    }
});