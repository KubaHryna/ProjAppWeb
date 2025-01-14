function startclock() {
    var today = new Date();
    
    // Pobieranie godzin, minut i sekund
    var h = today.getHours();
    var m = today.getMinutes();
    var s = today.getSeconds();
    
    // Dodawanie zer przed godzinami, minutami i sekundami, gdy są jednocyfrowe
    m = checkTime(m);
    s = checkTime(s);
    
    // Wyświetlanie czasu w formacie HH:MM:SS
    document.getElementById('zegarek').innerHTML = h + ":" + m + ":" + s;

    // Pobieranie daty
    var day = today.getDate();
    var month = today.getMonth() + 1; // Miesiące zaczynają się od 0
    var year = today.getFullYear();
    
    // Wyświetlanie daty w formacie DD/MM/YYYY
    document.getElementById('data').innerHTML = day + "/" + month + "/" + year;

    // Odświeżanie zegara co sekundę
    setTimeout(startclock, 1000);
}

function checkTime(i) {
    // Funkcja dodaje zero przed liczbami jednocyfrowymi
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}

// Uruchomienie zegara po załadowaniu strony
window.onload = startclock;
