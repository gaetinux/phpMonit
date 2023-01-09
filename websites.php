<?php
//////////////////////////////////////////////////////////////////////////
//
// Connexion à la BDD
//
//////////////////////////////////////////////////////////////////////////

include 'includes/db.php';

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
    <title>phpMonit - websites</title>
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
                                <a class="nav-link" href="websites.php">sites web</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="servers.php">serveurs</a>
                            </li>
                        </ul>
                        <form class="d-flex" action="index.php" method="get">
                            <input class="form-control me-2" type="text" placeholder="nom site ou serveur" id="search" name="search" required pattern="^[a-z ]+$" maxlength="20">
                            <button class="btn btn-outline-primary" type="submit">recherche</button>
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
                <h3 id="websites" class="mb-5">Sites web</h3>
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
                        <?php
                        // Récupération des sites
                        $stmt = $dbh->prepare("SELECT * FROM websites ORDER BY name ASC");
                        $stmt->execute();
                        $websites = $stmt->fetchAll();

                        foreach ($websites as $website) {
                        ?>
                            <tr>
                                <th scope="row">
                                    <form action="index.php" method="get">
                                        <input type="hidden" id="search" name="search" value="<?php echo $website['name'] ?>" required pattern="^[a-z]$" maxlength="20">
                                        <input type="submit" value="<?php echo $website['name'] ?>">
                                    </form>
                                </th>
                                <td><a href="<?php echo $website['url'] ?>" target="_blank"><?php echo $website['url'] ?></a></td>
                                <td><?php echo $website['status'] ?></td>
                                <td><?php echo $website['lastChange'] ?></td>
                                <td><?php echo $website['certificate'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p><strong>Expiration certificats</strong></p>
                <span class="text-success">vert</span> : + 30 jours /
                <span class="text-warning">orange</span> : - 30 jours /
                <span class="text-danger">rouge</span> : - 10 jours
            </div>
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    </body>
</html>