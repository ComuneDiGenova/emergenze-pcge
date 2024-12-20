document.addEventListener("DOMContentLoaded", function () {
    const feedbackMessage = document.getElementById("feedbackMessage");
    if (feedbackMessage) {
        // Nasconde il messaggio dopo 10 secondi
        setTimeout(function () {
            feedbackMessage.style.transition = "opacity 1s";
            feedbackMessage.style.opacity = "0";
            setTimeout(() => feedbackMessage.remove(), 1000); // Rimuove il messaggio dal DOM
        }, 10000);

        // Rimuove i parametri dall'URL (così non ricompaiono se premi F5)
        const url = new URL(window.location);
        url.searchParams.delete("status");
        url.searchParams.delete("message");
        window.history.replaceState({}, document.title, url.toString());
    }
});
