<?php
//////////////////////////////////////////////////////////////////////////
//
// Connexion à la BDD
//
//////////////////////////////////////////////////////////////////////////

include 'includes/db.php';

//////////////////////////////////////////////////////////////////////////
//
// Création des tables
//
//////////////////////////////////////////////////////////////////////////

// Création de la table servers
$sql = "CREATE table if not exists servers(
    id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR( 50 ) NOT NULL, 
    name VARCHAR( 50 ) NOT NULL,
    status VARCHAR( 50 ) NULL,
    lastChange DATETIME);";
$dbh->exec($sql);

// Création de la table websites
$sql = "CREATE table if not exists websites(
    id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR( 250 ) NOT NULL, 
    name VARCHAR( 50 ) NOT NULL,
    status VARCHAR( 50 ) NULL,
    certificate VARCHAR( 250 ) NULL,
    certificateInfos TEXT NULL,
    lastChange DATETIME);";
$dbh->exec($sql);

// Création de la table feed
$sql = "CREATE table if not exists feed(
    id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR( 50 ) NOT NULL,
    status VARCHAR( 50 ) NOT NULL,
    date DATETIME,
    certificate VARCHAR( 250 ) NULL);";
$dbh->exec($sql);

// Création de la table checks
$sql = "CREATE table if not exists checks(
    id INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR( 50 ) UNIQUE,
    date DATETIME);";
$dbh->exec($sql);
// Insert websites and servers in checks
$sql = "INSERT IGNORE INTO checks (name) VALUES ('websites'), ('servers');";
$stmt = $dbh->prepare($sql);
$stmt->execute();

//////////////////////////////////////////////////////////////////////////
//
// Récupération de la table feed
//
//////////////////////////////////////////////////////////////////////////

$stmt = $dbh->prepare("SELECT * FROM feed ORDER BY date DESC LIMIT 100");
$stmt->execute();
$completeFeed = $stmt->fetchAll();

//////////////////////////////////////////////////////////////////////////
//
// Refresh page every 30 sec
//
//////////////////////////////////////////////////////////////////////////

header("refresh: 30");
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>phpMonit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">phpMonit</a>
                <?php if (isset($_GET['search'])) {
                    $search = $_GET["search"];
                    if (preg_match('/[A-Z\'^£$%&*()}{@#~!?><>,|=+¬-]/', $search)) {
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
                            <input class="form-control me-2" type="text" placeholder="website or server name" id="search" name="search" required pattern="^[a-z ._]+$" maxlength="50">
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
            if (preg_match('/[A-Z\'^£$%&*()}{@#~!?><>,|=+¬-]/', $search)) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
        ?>
                <div class="primary" style="display: none;">
            <?php }
        } ?>
            <div class="container my-5">
                <?php
                        // Récupération des derniers checks
                        $stmt = $dbh->prepare("SELECT * FROM checks");
                        $stmt->execute();
                        $checks = $stmt->fetchAll();

			foreach ($checks as $check) {
		?>
		<p>
			<span>Last <?php echo $check['name']?> check : </span>
			<span><?php echo $check['date']?></span>
		</p>
		<?php } ?>
	    </div>
            <div class="container my-5">
                <h3 id="websites" class="mb-5">Problems</h3>
                <table class="table table-primary my-5">
                    <thead>
                        <tr>
                            <th scope="col">nom</th>
                            <th scope="col">status</th>
                            <th scope="col">date</th>
                            <th scope="col">certificat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $issue = '<span class="text-danger">DOWN</span>';
                        // Récupération des sites
                        $stmt = $dbh->prepare("SELECT * FROM websites WHERE status=:status OR certificate NOT LIKE '%text-success%' ORDER BY name ASC");
                        $stmt->bindValue(':status', $issue);
                        $stmt->execute();
                        $websitesIssue = $stmt->fetchAll();

                        // Récupération des serveurs
                        $stmt = $dbh->prepare("SELECT * FROM servers WHERE status=:status ORDER BY name ASC");
                        $stmt->bindValue(':status', $issue);
                        $stmt->execute();
                        $serversIssue = $stmt->fetchAll();

                        foreach ($websitesIssue as $websiteIssue) {
                        ?>
                            <tr>
                                <th>
                                    <form action="index.php" method="get">
                                        <input type="hidden" id="search" name="search" value="<?php echo $websiteIssue['name'] ?>" required pattern="^[a-z]$" maxlength="20">
                                        <input type="submit" value="<?php echo $websiteIssue['name'] ?>">
                                    </form>
                                </th>
                                <td><?php echo $websiteIssue['status'] ?></td>
                                <td><?php echo $websiteIssue['lastChange'] ?></td>
                                <td><?php echo $websiteIssue['certificate'] ?></td>
                            </tr>
                        <?php }
                        foreach ($serversIssue as $serverIssue) {
                        ?>
                            <tr>
                                <th>
                                    <form action="index.php" method="get">
                                        <input type="hidden" id="search" name="search" value="<?php echo $serverIssue['name'] ?>" required pattern="^[a-z]$" maxlength="20">
                                        <input type="submit" value="<?php echo $serverIssue['name'] ?>">
                                    </form>
                                </th>
                                <td><?php echo $serverIssue['status'] ?></td>
                                <td><?php echo $serverIssue['lastChange'] ?></td>
                                <td><span>null</span></td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="container my-5">
                <p><strong>Expiration certificats</strong></p>
                <span class="text-success">vert</span> : + 30 jours /
                <span class="text-warning">orange</span> : - 30 jours /
                <span class="text-danger">rouge</span> : - 10 jours
            </div>
            <div class="container my-5">
                <h3 id="feed" class="mb-5">Feed</h3>
                <table class="table table-primary my-5">
                    <thead>
                        <tr>
                            <th scope="col">nom</th>
                            <th scope="col">status</th>
                            <th scope="col">date</th>
                            <th scope="col">certificat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completeFeed as $item) { ?>
                            <tr>
                                <th scope="row">
                                    <form action="index.php" method="get">
                                        <input type="hidden" id="search" name="search" value="<?php echo $item['name'] ?>" required pattern="^[a-z]$" maxlength="20">
                                        <input type="submit" value="<?php echo $item['name'] ?>">
                                    </form>
                                </th>
                                <td><?php echo $item['status'] ?></td>
                                <td><?php echo $item['date'] ?></td>
                                <td><?php echo $item['certificate'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
                </div>
                <?php
                if (isset($_GET["search"])) {
                    $search = $_GET["search"];
                    if (preg_match('/[A-Z\'^£$%&*()}{@#~!?><>,|=+¬-]/', $search)) {
                        header('Location: ' . $_SERVER['HTTP_REFERER']);
                        exit;
                    } else {
                ?>
                        <div class="secondary">
                        <?php }
                } else { ?>
                        <div class="secondary" style="display: none;">
                        <?php } ?>
                        <?php
                        if (isset($_GET["search"])) {
                            $search = $_GET["search"];
                            if (preg_match('/[A-Z\'^£$%&*()}{@#~!?><>,|=+¬-]/', $search)) {
                                header('Location: ' . $_SERVER['HTTP_REFERER']);
                                exit;
                            } else {

                                // Récupération du feed
                                $stmt = $dbh->prepare("SELECT * FROM feed WHERE name=:name ORDER BY date DESC");
                                $stmt->bindValue(':name', $search);
                                $stmt->execute();
                                $feed = $stmt->fetchAll();

                                // Récupération des sites
                                $stmt = $dbh->prepare("SELECT * FROM websites WHERE name=:name");
                                $stmt->bindValue(':name', $search);
                                $stmt->execute();
                                $websites = $stmt->fetchAll();

                                // Récupération des serveurs
                                $stmt = $dbh->prepare("SELECT * FROM servers WHERE name=:name");
                                $stmt->bindValue(':name', $search);
                                $stmt->execute();
                                $servers = $stmt->fetchAll();
                            }
                        }

                        ?>
                        <div class="container my-5">
                            <h3>Recherche</h3>
                            <?php
                            foreach ($websites as $website) {
                            ?>
                                <table class="table table-primary my-5">
                                    <thead>
                                        <tr>
                                            <th scope="col">nom</th>
                                            <th scope="col">url</th>
                                            <th scope="col">status</th>
                                            <th scope="col">depuis</th>
                                            <th scope="col">certificat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row"><?php echo $website['name'] ?></th>
                                            <td><a href="<?php echo $website['url'] ?>" target="_blank"><?php echo $website['url'] ?></a></td>
                                            <td><?php echo $website['status'] ?></td>
                                            <td><?php echo $website['lastChange'] ?></td>
                                            <td><?php echo $website['certificate'] ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="my-5">
                                    <h5>Server certificate</h5>
                                    <p>
                                        <?php echo $website['certificateInfos'] ?>
                                    </p>
                                </div>
                            <?php
                            }
                            foreach ($servers as $server) {
                            ?>
                                <table class="table table-primary my-5">
                                    <thead>
                                        <tr>
                                            <th scope="col">nom</th>
                                            <th scope="col">ip</th>
                                            <th scope="col">status</th>
                                            <th scope="col">depuis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row"><?php echo $server['name'] ?></th>
                                            <td><?php echo $server['ip'] ?></td>
                                            <td><?php echo $server['status'] ?></td>
                                            <td><?php echo $server['lastChange'] ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="container my-5">
                            <h3>Feed associé à la recherche</h3>
                            <table class="table table-primary my-5">
                                <thead>
                                    <tr>
                                        <th scope="col">nom</th>
                                        <th scope="col">status</th>
                                        <th scope="col">date</th>
                                        <th scope="col">certificat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($feed as $item) {
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $item['name'] ?></th>
                                            <td><?php echo $item['status'] ?></td>
                                            <td><?php echo $item['date'] ?></td>
                                            <td><?php echo $item['certificate'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>
