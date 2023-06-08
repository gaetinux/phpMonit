<?php
include '../includes/db.php';
require './getLastCheck.php';

// Récupération des serveurs
$stmt = $dbh->prepare("SELECT * FROM servers ORDER BY name ASC");
$stmt->execute();
$servers = $stmt->fetchAll();

//////////////////////////////////////////////////////////////////////////
//
// Check si un serveur réponds au PING
//
//////////////////////////////////////////////////////////////////////////

function checkServer($ip)
{
    include '../includes/db.php';
    // Récupération des serveurs
    $stmt = $dbh->prepare("SELECT * FROM servers ORDER BY name ASC");
    $stmt->execute();
    $servers = $stmt->fetchAll();

    // Exécution du ping
    // -n for windows
    // -c for linux
    exec("ping -c 2 -w 2 " . $ip, $output, $result);

    foreach ($servers as $server) {

        $currentStatus = $server['status'];

        // Update feed
        $tz = 'Europe/Paris';
        $timestamp = time();
        $currentDate = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $currentDate->setTimestamp($timestamp); //adjust the object to correct timestamp
        $date = $currentDate->format('Y-m-d H:i:s');

	// store last check date
        getLastServersCheck($date);


        if ($result == 0) {
            $newStatus = '<span class="text-success">OK</span>';
            if($currentStatus != $newStatus) {
                $sql = "UPDATE servers SET status=:status WHERE name=:name";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $server['name']);
                $stmt->bindValue(':status', $newStatus);
                $stmt->execute();

                $sql = "INSERT INTO feed (name, status, date, certificate) VALUES (:name, :status, :date, :certificate)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $server['name']);
                $stmt->bindValue(':status', $newStatus);
                $stmt->bindValue(':date', $date);
                $stmt->bindValue(':certificate', "<span>null</span>");
                $stmt->execute();
            }
        } else {
            $newStatus = '<span class="text-danger">DOWN</span>';
            if($currentStatus != $newStatus) {
                $sql = "UPDATE servers SET status=:status WHERE name=:name";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $server['name']);
                $stmt->bindValue(':status', $newStatus);
                $stmt->execute();

                $sql = "INSERT INTO feed (name, status, date, certificate) VALUES (:name, :status, :date, :certificate)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $server['name']);
                $stmt->bindValue(':status', $newStatus);
                $stmt->bindValue(':date', $date);
                $stmt->bindValue(':certificate', "<span>null</span>");
                $stmt->execute();
            }
        }
    }
}

foreach ($servers as $server) {
    checkServer($server['ip']);
}
