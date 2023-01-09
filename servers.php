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
    <title>phpMonit - servers</title>
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
                <h3 id="servers" class="mb-5">Serveurs</h3>
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
                        <?php
                        // Récupération des serveurs
                        $stmt = $dbh->prepare("SELECT * FROM servers ORDER BY name ASC");
                        $stmt->execute();
                        $servers = $stmt->fetchAll();

                        foreach ($servers as $server) {
                        ?>
                            <tr>
                                <th scope="row">
                                    <form action="index.php" method="get">
                                        <input type="hidden" id="search" name="search" value="<?php echo $server['name'] ?>" required pattern="^[a-z]$" maxlength="20">
                                        <input type="submit" value="<?php echo $server['name'] ?>">
                                    </form>
                                </th>
                                <td><?php echo $server['ip'] ?></td>
                                <td><?php echo $server['status'] ?></td>
                                <td><?php echo $server['lastChange'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>

</html>