<?php
function getLastWebsitesCheck($date) {
	include '../includes/db.php';
	$sql = "UPDATE checks SET date=:lastCheck WHERE name='websites'";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(':lastCheck', $date);
	$stmt->execute();
}

function getLastServersCheck($date) {
	include '../includes/db.php';
	$sql = "UPDATE checks SET date=:lastCheck WHERE name='servers'";
	$stmt = $dbh->prepare($sql);
	$stmt->bindValue(':lastCheck', $date);
	$stmt->execute();
}
