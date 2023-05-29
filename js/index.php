<!DOCTYPE html>
<html lang="fr">
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MasterMind</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>MasterMind</h1>
    <div id="game-container">
        <h2>Nouvelle Partie</h2>
        <p>Choisissez 4 couleurs pour jouer :</p>
        <p>Entrez votre pseudo :</p>
        <input class="form-control mb-3" type="text" id="player-name" placeholder="Pseudonyme" required="required" />
        <div id="color-picker">
            <div class="color-box" style="background-color: red;"></div>
            <div class="color-box" style="background-color: blue;"></div>
            <div class="color-box" style="background-color: green;"></div>
            <div class="color-box" style="background-color: yellow;"></div>
            <div class="color-box" style="background-color: orange;"></div>
            <div class="color-box" style="background-color: purple;"></div>
        </div>
        <button type="button" class="btn btn-success" id="guess-button">Soumettre</button>
        <button type="button" class="btn btn-primary" id="reset-button">Nouvelle Partie</button>
        <div id="feedback"></div>
        <div id="guesses"></div>
        <div id="timer">Temps écoulé: 0s</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>

    <script>
        // Couleurs possibles
        var colors = ['red', 'blue', 'green', 'yellow', 'orange', 'purple'];

        // Variable pour stocker les couleurs sélectionnées
        var selectedColors = [];

        // Generation d'un code random
        var code = generateCode();

        // Initialisation des variables
        var gameWon = false; // Indicateur de victoire
        var guesses = [];
        var remainingAttempts = 10;
        var startTime;
        var timerInterval;

        // Récupération des éléments HTML
        var colorBoxes = document.getElementsByClassName('color-box');
        var guessButton = document.getElementById('guess-button');
        var feedbackDiv = document.getElementById('feedback');
        var guessesDiv = document.getElementById('guesses');
        var timerDiv = document.getElementById('timer');

        for (var i = 0; i < colorBoxes.length; i++) {
            colorBoxes[i].addEventListener('click', selectColor);
        }
        guessButton.addEventListener('click', makeGuess);

        // Champs des couleurs possibles
        var colors = ['red', 'blue', 'green', 'yellow', 'orange', 'purple'];

        // Fonction pour générer le code
        function generateCode() {
            var code = [];
            var availableColors = colors.slice();

            for (var i = 0; i < 4; i++) {
                var randomIndex = Math.floor(Math.random() * availableColors.length);
                var randomColor = availableColors[randomIndex];
                code.push(randomColor);
                availableColors.splice(randomIndex, 1);
            }
            console.log("Couleurs :", code);
            return code;
        }

        // Fonction pour sélectionner une couleur
        function selectColor() {
            this.classList.toggle('selected');
            if (this.classList.contains('selected')) {
                selectedColors.push(this.style.backgroundColor);
            } else {
                var index = selectedColors.indexOf(this.style.backgroundColor);
                if (index !== -1) {
                    selectedColors.splice(index, 1);
                }
            }
        }

        // Fonction pour soumettre une réponse
        function makeGuess() {
            if (selectedColors.length !== 4) {
                alert('Veuillez sélectionner exactement 4 couleurs');
                return;
            }

            if (!startTime) {
                startTime = new Date();
                startTimer();
            }

            var guess = selectedColors.slice();

            var result = evaluateGuess(guess);
            guesses.push({
                guess: guess,
                result: result
            });

            remainingAttempts--;

            displayGuesses();
            displayFeedback(result);

            if (result.correctPositions === 4) {
                endGame(true);
            } else if (remainingAttempts === 0) {
                endGame(false);
            }

            // Clear la sélection
            selectedColors = [];
            var selectedBoxes = document.getElementsByClassName('selected');
            while (selectedBoxes.length > 0) {
                selectedBoxes[0].classList.remove('selected');
            }
        }

        // Fonction pour évaluer une réponse
        function evaluateGuess(guess) {
            var result = {
                correctPositions: 0,
                correctColors: 0
            };

            var codeCopy = code.slice();

            // Check si les positions sont correctes
            for (var i = 0; i < guess.length; i++) {
                if (guess[i] === codeCopy[i]) {
                    result.correctPositions++;
                    codeCopy[i] = null;
                }
            }

            // Check si les couleurs sont correctes
            for (var i = 0; i < guess.length; i++) {
                var position = codeCopy.indexOf(guess[i]);
                if (position > -1) {
                    result.correctColors++;
                    codeCopy[position] = null;
                }
            }

            return result;
        }

        // Fonction pour afficher les couleurs soumises
        function displayGuesses() {
            var html = '<p>Soumis :</p>';
            for (var i = 0; i < guesses.length; i++) {
                var guess = guesses[i].guess;
                var result = guesses[i].result;
                html += '<div class="guess">';
                for (var j = 0; j < guess.length; j++) {
                    html += '<div class="color-box" style="background-color:' + guess[j] + ';"></div>';
                }
                html += '<div class="result">' + result.correctPositions + ' / ' + result.correctColors + '</div>';
                html += '</div>';
            }
            guessesDiv.innerHTML = html;
        }

        // Fonction pour afficher les retours de partie
        function displayFeedback(result) {
            feedbackDiv.innerHTML = 'Positions correctes : ' + result.correctPositions +
                '<br>Couleurs correctes (mais mauvaise position) : ' + result.correctColors +
                '<br>Essais restants : ' + remainingAttempts;
        }

        // Fonction de fin de partie
        function endGame(won) {
            stopTimer();

            for (var i = 0; i < colorBoxes.length; i++) {
                colorBoxes[i].removeEventListener('click', selectColor);
            }
            guessButton.removeEventListener('click', makeGuess);

            var codeDisplay = 'Code : ';
            for (var i = 0; i < code.length; i++) {
                codeDisplay += '<div class="color-box" style="background-color:' + code[i] + ';"></div>';
            }

            if (won) {
                feedbackDiv.innerHTML = 'Félicitations, vous avez gagné!<br>' + codeDisplay;
                var playerName = document.getElementById('player-name').value;
                var endTime = new Date();
                var timeTaken = (endTime - startTime) / 1000; // Convert to seconds

                if (!gameWon) { // Vérifier si la partie n'a pas déjà été gagnée
                    gameWon = true; // Marquer la partie comme gagnée pour éviter les doublons de sauvegarde
                    saveScore(playerName, true, guesses.length, timeTaken);
                }
            } else {
                feedbackDiv.innerHTML = 'Désolé, vous avez perdu!<br>' + codeDisplay;
            }
        }

        // Fonction pour sauvegarder le score
        function saveScore(playerName, won, attempts, timeTaken) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    console.log('Score saved!');
                }
            };
            xhttp.open("POST", "save_score.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("playerName=" + playerName + "&won=" + (won ? 1 : 0) + "&attempts=" + attempts + "&timeTaken=" + timeTaken);
        }



        // Fonction pour démarrer le chronomètre
        function startTimer() {
            var startTime = new Date().getTime();

            timerInterval = setInterval(function () {
                var currentTime = new Date().getTime();
                var elapsedTime = Math.floor((currentTime - startTime) / 1000); // Convert to seconds
                timerDiv.innerHTML = 'Temps écoulé: ' + elapsedTime + 's';
            }, 1000);
        }

        // Fonction pour arrêter le chronomètre
        function stopTimer() {
            clearInterval(timerInterval);
        }

        // Bouton pour recommencer une nouvelle partie
        var resetButton = document.getElementById('reset-button');
        resetButton.addEventListener('click', resetGame);

        // Fonction pour réinitialiser le jeu
        function resetGame() {
            code = generateCode();
            guesses = [];
            remainingAttempts = 10;
            startTime = null;
            stopTimer();
            timerDiv.innerHTML = 'Temps écoulé: 0s';
            feedbackDiv.innerHTML = '';
            guessesDiv.innerHTML = '';

            for (var i = 0; i < colorBoxes.length; i++) {
                colorBoxes[i].addEventListener('click', selectColor);
            }
            guessButton.addEventListener('click', makeGuess);
        }
    </script>
</body>

</html>