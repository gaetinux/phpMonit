<?php
//////////////////////////////////////////////////////////////////////////
//
// Connexion à la BDD
//
//////////////////////////////////////////////////////////////////////////

include 'includes/db.php';

//////////////////////////////////////////////////////////////////////////
//
// Add new items
//
//////////////////////////////////////////////////////////////////////////

// Date d'ajout
$tz = 'Europe/Paris';
$timestamp = time();
$currentDate = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
$currentDate->setTimestamp($timestamp); //adjust the object to correct timestamp
$date = $currentDate->format('Y-m-d H:i:s');

if (isset($_GET["websiteName"]) || isset($_GET["url"])) {
    $name = $_GET["websiteName"];
    $url = $_GET["url"];

    $sql = "INSERT INTO websites (name, url, lastChange) VALUES (:name, :url, :date)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':url', $url);
    $stmt->bindValue(':date', $date);
    $stmt->execute();
}

if (isset($_GET["serverName"]) || isset($_GET["ip"])) {
    $name = $_GET["serverName"];
    $ip = $_GET["ip"];

    $sql = "INSERT INTO servers (name, ip, lastChange) VALUES (:name, :ip, :date)";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':ip', $ip);
    $stmt->bindValue(':date', $date);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>phpMonit - add</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">phpMonit</a>
                <?php if (isset($_GET['search'])) {
                    $search = $_GET["search"];
                    if (preg_match('/[A-Z\'^£$%&*()}{@#~!?><>,|=_+¬-]/', $search)) {
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        exit;
                    } else {
                        echo '<div></div>';
                    }
                } else { ?>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="index.php">home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="websites.php">websites</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="servers.php">servers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="add.php">add</a>
                            </li>
                        </ul>
                        <form class="d-flex" action="index.php" method="get">
                            <input class="form-control me-2" type="text" placeholder="website or server name" id="search" name="search" required pattern="^[a-z ]+$" maxlength="20">
                            <button class="btn btn-outline-primary" type="submit">search</button>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </nav>
    </header>
    <main>
        <?php
        if (isset($_GET['search'])) {
            $search = $_GET["search"];
            if (preg_match('/[A-Z\'^£$%&*()}{@#~!?><>,|=_+¬-]/', $search)) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
        ?>
                <div class="primary" style="display: none;">
            <?php }
        } ?>
            <div class="container my-5">
                <h3>add website</h3>
                <form class="row gy-2 gx-3 align-items-center" action="add.php" method="get">
                    <div class="col-auto">
                        <label class="visually-hidden" for="websiteName">website name</label>
                        <input type="text" class="form-control" id="websiteName" name="websiteName" placeholder="website name">
                    </div>
                    <div class="col-auto">
                        <label class="visually-hidden" for="url">website url</label>
                        <input type="text" class="form-control" id="url" name="url" placeholder="url">
                    </div>
                    <!--<div class="col-auto">
                        <label class="visually-hidden" for="autoSizingSelect">Preference</label>
                        <select class="form-select" id="autoSizingSelect">
                            <option selected>Choose...</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>-->
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">submit</button>
                    </div>
                </form>
            </div>
            <div class="container my-5">
                <h3>add server</h3>
                <form class="row gy-2 gx-3 align-items-center" action="add.php" method="get">
                    <div class="col-auto">
                        <label class="visually-hidden" for="serverName">server name</label>
                        <input type="text" class="form-control" id="serverName" name="serverName" placeholder="server name">
                    </div>
                    <div class="col-auto">
                        <label class="visually-hidden" for="ip">server ip</label>
                        <input type="text" class="form-control" id="ip" name="ip" placeholder="ip">
                    </div>
                    <!--<div class="col-auto">
                        <label class="visually-hidden" for="autoSizingSelect">Preference</label>
                        <select class="form-select" id="autoSizingSelect">
                            <option selected>Choose...</option>
                            <option value="1">One</option>
                            <option value="2">Two</option>
                            <option value="3">Three</option>
                        </select>
                    </div>-->
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">submit</button>
                    </div>
                </form>
            </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>