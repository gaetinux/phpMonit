<?php

//////////////////////////////////////////////////////////////////////////
//
// Connexion à la BDD
//
//////////////////////////////////////////////////////////////////////////

$username = "";
$password = "";
$database = "";

try {
    // Connexion à la bdd
    $dbh = new PDO('mysql:host=localhost;dbname=' . $database, $username, $password);

    // Gestion de l'erreur
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}