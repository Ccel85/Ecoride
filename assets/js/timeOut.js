if (app.user) {

  (function timeOut() {
    let timeoutDuration = 2 * 60 * 1000; // 30 minutes
    let warningDuration = 1 * 60 * 1000;  // 1 minute avant déconnexion
    let timeout;
    let warningTimeout;

    function resetTimer() {
        clearTimeout(timeout);
        clearTimeout(warningTimeout);

        // Affiche un avertissement 1 min avant déconnexion
        warningTimeout = setTimeout(showWarning, timeoutDuration - warningDuration);

        // Déconnexion après timeout complet
        timeout = setTimeout(logoutUser, timeoutDuration);
    }

    function showWarning() {
        const continueSession = confirm("Votre session va expirer dans une minute. Voulez-vous rester connecté ?");
        if (continueSession) {
            resetTimer(); // Redémarre le timer si l'utilisateur veut rester connecté
        }
    }

    function logoutUser() {
        window.location.href = "{{ path('app_logout') }}"; // Redirige vers la route de déconnexion Symfony
    }

    // Réinitialise le timer sur chaque interaction
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
        document.addEventListener(evt, resetTimer, false);
    });

    resetTimer(); // Démarre le timer à l'ouverture de la page
})();

}
