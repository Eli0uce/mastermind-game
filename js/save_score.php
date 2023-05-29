<?php
// Récupération des données envoyées par la requête AJAX
$playerName = $_POST['playerName'];
$won = $_POST['won'];
$attempts = $_POST['attempts'];
$timeTaken = $_POST['timeTaken'];

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mastermind";
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}

// Insertion du score dans la table
$sql = "INSERT INTO scores (player_name, won, attempts, time_taken) VALUES ('$playerName', '$won', '$attempts', '$timeTaken')";
if ($conn->query($sql) === TRUE) {
    echo "Score enregistré avec succès.";
} else {
    echo "Erreur lors de l'enregistrement du score : " . $conn->error;
}

// Fermeture de la connexion à la base de données
$conn->close();
?>