<?php
session_start();

// Couleurs possibles
$colors = ['red', 'blue', 'green', 'yellow', 'orange', 'purple'];

// Variable pour stocker les couleurs sélectionnées
if (!isset($_SESSION['selectedColors'])) {
    $_SESSION['selectedColors'] = [];
}

// Generation d'un code random
if (!isset($_SESSION['code'])) {
    $_SESSION['code'] = generateCode();
}

// Initialisation des variables
if (!isset($_SESSION['guesses'])) {
    $_SESSION['guesses'] = [];
}

if (!isset($_SESSION['remainingAttempts'])) {
    $_SESSION['remainingAttempts'] = 10;
}

// Récupération de l'action soumise
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'selectColor' && isset($_POST['color'])) {
        $color = $_POST['color'];
        selectColor($color);
    } elseif ($action === 'makeGuess') {
        makeGuess();
    } elseif ($action === 'resetGame') {
        resetGame();
    }
}

// Fonction pour générer le code
function generateCode()
{
    global $colors;
    $code = [];
    $availableColors = $colors;

    for ($i = 0; $i < 4; $i++) {
        $randomIndex = rand(0, count($availableColors) - 1);
        $randomColor = $availableColors[$randomIndex];
        $code[] = $randomColor;
        array_splice($availableColors, $randomIndex, 1);
    }

    return $code;
}

// Fonction pour sélectionner une couleur
function selectColor($color)
{
    $_SESSION['selectedColors'][] = $color;
}

// Fonction pour soumettre une réponse
function makeGuess()
{
    if (count($_SESSION['selectedColors']) !== 4) {
        echo 'Veuillez sélectionner exactement 4 couleurs';
        return;
    }

    $guess = $_SESSION['selectedColors'];

    $result = evaluateGuess($guess);
    $_SESSION['guesses'][] = [
        'guess' => $guess,
        'result' => $result
    ];

    $_SESSION['remainingAttempts']--;

    if ($result['correctPositions'] === 4) {
        endGame(true);
    } elseif ($_SESSION['remainingAttempts'] === 0) {
        endGame(false);
    }

    // Clear la sélection
    $_SESSION['selectedColors'] = [];
}

// Fonction pour évaluer une réponse
function evaluateGuess($guess)
{
    global $code;
    $result = [
        'correctPositions' => 0,
        'correctColors' => 0
    ];

    $codeCopy = $code;

    // Check si les positions sont correctes
    for ($i = 0; $i < count($guess); $i++) {
        if ($guess[$i] === $codeCopy[$i]) {
            $result['correctPositions']++;
            $codeCopy[$i] = null;
        }
    }

    // Check si les couleurs sont correctes
    for ($i = 0; $i < count($guess); $i++) {
        $position = array_search($guess[$i], $codeCopy);
        if ($position !== false) {
            $result['correctColors']++;
            $codeCopy[$position] = null;
        }
    }

    return $result;
}

// Fonction de fin de partie
function endGame($won)
{
    global $code;
    echo '<script>';
    echo 'var colorBoxes = document.getElementsByClassName("color-box");';
    echo 'var guessButton = document.getElementById("guess-button");';
    echo 'var feedbackDiv = document.getElementById("feedback");';
    echo 'var codeDisplay = "Code : ";';
    foreach ($code as $color) {
        echo 'codeDisplay += \'<div class="color-box" style="background-color:' . $color . ';"></div>\';';
    }

    if ($won) {
        echo 'feedbackDiv.innerHTML = \'Félicitations, vous avez gagné!<br>\' + codeDisplay;';
    } else {
        echo 'feedbackDiv.innerHTML = \'Game over! Vous avez perdu.<br>\' + codeDisplay;';
    }

    echo 'guessButton.disabled = true;';
    echo '</script>';

    session_destroy();
}

// Fonction pour afficher les couleurs soumises
function displayGuesses()
{
    $html = '<p>Soumis :</p>';
    foreach ($_SESSION['guesses'] as $guessData) {
        $guess = $guessData['guess'];
        $result = $guessData['result'];
        $html .= '<div class="guess">';
        foreach ($guess as $color) {
            $html .= '<div class="color-box" style="background-color:' . $color . ';"></div>';
        }
        $html .= '<div class="result">' . $result['correctPositions'] . ' / ' . $result['correctColors'] . '</div>';
        $html .= '</div>';
    }
    echo $html;
}

// Reset Bouton
function resetGame()
{
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>MasterMind</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>MasterMind</h1>
    <div id="game-container">
        <h2>Nouvelle Partie</h2>
        <p>Choisis 4 couleurs pour jouer :</p>
        <div id="color-picker">
            <div class="color-box" style="background-color: red;"></div>
            <div class="color-box" style="background-color: blue;"></div>
            <div class="color-box" style="background-color: green;"></div>
            <div class="color-box" style="background-color: yellow;"></div>
            <div class="color-box" style="background-color: orange;"></div>
            <div class="color-box" style="background-color: purple;"></div>
        </div>
        <button id="guess-button" onclick="makeGuess()">Soumettre</button>
        <button id="reset-button" onclick="resetGame()">Nouvelle Partie</button>
        <div id="feedback"></div>
        <div id="guesses">
            <?php displayGuesses(); ?>
        </div>
    </div>

    <script>
        // Requête AJAX pour sélectionner une couleur
        function selectColor(color) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            xhr.send('action=selectColor&color=' + color);
        }

        // Requête AJAX pour soumettre une réponse
        function makeGuess() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var feedbackDiv = document.getElementById('feedback');
                    feedbackDiv.innerHTML = xhr.responseText;

                    var guessesDiv = document.getElementById('guesses');
                    guessesDiv.innerHTML = '';

                    var guesses = JSON.parse(xhr.responseText);
                    guesses.forEach(function (guessData) {
                        var guess = guessData.guess;
                        var result = guessData.result;

                        var guessDiv = document.createElement('div');
                        guessDiv.className = 'guess';

                        guess.forEach(function (color) {
                            var colorBox = document.createElement('div');
                            colorBox.className = 'color-box';
                            colorBox.style.backgroundColor = color;
                            guessDiv.appendChild(colorBox);
                        });

                        var resultDiv = document.createElement('div');
                        resultDiv.className = 'result';
                        resultDiv.textContent = result.correctPositions + ' / ' + result.correctColors;

                        guessDiv.appendChild(resultDiv);
                        guessesDiv.appendChild(guessDiv);
                    });
                }
            };
            xhr.send('action=makeGuess');
        }

        // Requête AJAX pour réinitialiser la partie
        function resetGame() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'index.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    location.reload();
                }
            };
            xhr.send('action=resetGame');
        }

        // Attachement des événements de clic pour les couleurs
        var colorBoxes = document.getElementsByClassName('color-box');
        for (var i = 0; i < colorBoxes.length; i++) {
            colorBoxes[i].addEventListener('click', function () {
                var color = this.style.backgroundColor;
                selectColor(color);
            });
        }
    </script>
</body>

</html>