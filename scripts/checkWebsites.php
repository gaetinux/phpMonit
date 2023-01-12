<?php
include '../includes/db.php';

// Récupération des sites
$stmt = $dbh->prepare("SELECT * FROM websites ORDER BY name ASC");
$stmt->execute();
$websites = $stmt->fetchAll();

//////////////////////////////////////////////////////////////////////////
//
// Check si un site réponds au cURL
//
//////////////////////////////////////////////////////////////////////////

function checkWebsite($url)
{
    // Check, if a valid url is provided
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        exit;
    }

    // Initialize cURL
    $curlInit = curl_init($url);

    // Set options
    curl_setopt($curlInit, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curlInit, CURLOPT_HEADER, true);
    curl_setopt($curlInit, CURLOPT_NOBODY, true);
    curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);

    // Bypass ssl error if certificate expired
    curl_setopt($curlInit, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curlInit, CURLOPT_SSL_VERIFYPEER, 0);

    // Get response
    $response = curl_exec($curlInit);

    // Close a cURL session
    curl_close($curlInit);

    // Récupération des sites
    include '../includes/db.php';
    $stmt = $dbh->prepare("SELECT * FROM websites WHERE url=:url ORDER BY name ASC");
    $stmt->bindValue('url', $url);
    $stmt->execute();
    $websites = $stmt->fetchAll();

    // Récupération de la date
    $tz = 'Europe/Paris';
    $timestamp = time();
    $currentDate = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
    $currentDate->setTimestamp($timestamp); //adjust the object to correct timestamp
    $date = $currentDate->format('Y-m-d H:i:s');

    foreach ($websites as $website) {

        // Exécution de checkSSL
        $showCertificate = checkSSL($website['url']);

        if ($response) {
            $siteStatus = '<span class="text-success">OK</span>';

            if (($siteStatus != $website['status']) || ($showCertificate != $website['certificate'])) {
                $sql = "UPDATE websites SET status=:status, certificate=:certificate WHERE name=:name";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $website['name']);
                $stmt->bindValue(':status', $siteStatus);
                $stmt->bindValue(':certificate', $showCertificate);
                $stmt->execute();

                // Update feed
                $sql = "INSERT INTO feed (name, status, date, certificate) VALUES (:name, :status, :date, :certificate)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $website['name']);
                $stmt->bindValue(':status', $siteStatus);
                $stmt->bindValue(':date', $date);
                $stmt->bindValue(':certificate', $showCertificate);
                $stmt->execute();
            }
        } else {
            $siteStatus = '<span class="text-danger">DOWN</span>';

            if (($siteStatus != $website['status']) || ($showCertificate != $website['certificate'])) {
                $sql = "UPDATE websites SET status=:status, certificate=:certificate WHERE name=:name";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $website['name']);
                $stmt->bindValue(':status', $siteStatus);
                $stmt->bindValue(':certificate', $showCertificate);
                $stmt->execute();

                // Update feed
                $sql = "INSERT INTO feed (name, status, date, certificate) VALUES (:name, :status, :date, :certificate)";
                $stmt = $dbh->prepare($sql);
                $stmt->bindValue(':name', $website['name']);
                $stmt->bindValue(':status', $siteStatus);
                $stmt->bindValue(':date', $date);
                $stmt->bindValue(':certificate', $showCertificate);
                $stmt->execute();
            }
        }
    }
}

//////////////////////////////////////////////////////////////////////////
//
// Check date d'expiration d'un certificat SSL
//
//////////////////////////////////////////////////////////////////////////

function checkSSL($url)
{
    // Initialize cURL
    $curlInit = curl_init($url);

    // Set options
    curl_setopt($curlInit, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curlInit, CURLOPT_VERBOSE, true);
    curl_setopt($curlInit, CURLOPT_CERTINFO, true);
    curl_exec($curlInit);

    // Get informations
    $certInfo = curl_getinfo($curlInit, CURLINFO_CERTINFO);

    // Récupération des informations du certificat pour les afficher
    include '../includes/db.php';
    $stmt = $dbh->prepare("SELECT * FROM websites WHERE url=:url ORDER BY name ASC");
    $stmt->bindValue('url', $url);
    $stmt->execute();
    $websites = $stmt->fetchAll();
    foreach($websites as $website) {
        if($certInfo) {
            $sslInfos = "Subject : " . $certInfo[0]['Subject'] . "<br>" . "Start date : " . $certInfo[0]['Start date'] . "<br>Expire date : " . $certInfo[0]['Expire date'] . "<br>Issuer : " . $certInfo[0]['Issuer'] . "<br>SSL certificate verify ok.";
            $sql = "UPDATE websites SET certificateInfos=:certificateInfos WHERE name=:name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':name', $website['name']);
            $stmt->bindValue(':certificateInfos', $sslInfos);
            $stmt->execute();
        } else {
            $sslInfos = "Aucune information.";
            $sql = "UPDATE websites SET certificateInfos=:certificateInfos WHERE name=:name";
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':name', $website['name']);
            $stmt->bindValue(':certificateInfos', $sslInfos);
            $stmt->execute();
        }
    }

    // Si des informations sont récupérées
    if ($certInfo) {
        if ($certInfo[0]['Expire date']) {
            // On récupère la date d'expiration du certificat
            $result = print_r($certInfo[0]['Expire date'], true);

            // On explose la string récupérée pour pouvoir modifier son affichage
            $certDateExplode = explode(' ', $result);

            foreach ($certDateExplode as $x) {
                if (strlen($x) == 3 && $x != 'GMT') {
                    $month = $x;
                } elseif (strlen($x) == 8) {
                    $hour = $x;
                } elseif (strlen($x) == 4) {
                    $year = $x;
                } elseif (strlen($x) == 1 || strlen($x) == 2) {
                    $day = $x;
                }
            }

            $listMonth = array(
                'Jan' => '01',
                'Feb' => '02',
                'Mar' => '03',
                'Apr' => '04',
                'May' => '05',
                'Jun' => '06',
                'Jul' => '07',
                'Aug' => '08',
                'Sep' => '09',
                'Oct' => '10',
                'Nov' => '11',
                'Dec' => '12',
            );

            foreach ($listMonth as $key => $value) {
                if ($month == $key) {
                    $certMonth = $value;
                }
            }

            // Rajoute un 0 devant le jour si c'est un chiffre
            if (strlen($day) == 1) {
                $day = '0' . $day;
            }

            // Concaténation
            $certDate = $year . '-' . $certMonth . '-' . $day . ' ' . $hour;
            $today = time();
            $interval = strtotime($certDate) - $today;
            $days = floor($interval / 86400); // 1 day

            if ($days > 30) {
                $showCertificate = '<span class="text-success">' . $certDate . '</span>';
                return $showCertificate;
            } elseif ($days < 30) {
                $showCertificate = '<span class="text-warning">' . $certDate . '</span>';
                return $showCertificate;
            } elseif ($days < 10) {
                $showCertificate = '<span class="text-danger">' . $certDate . '</span>';
                return $showCertificate;
            } elseif ($days < 0) {
                $showCertificate = '<span class="text-danger">Le certificat est expiré.</span>';
                return $showCertificate;
            }
        }
    } else {
        $showCertificate = '<span class="text-danger">Impossible de vérifier le certificat.</span>';
        return $showCertificate;
    }

    // Close a cURL session
    curl_close($curlInit);
}

foreach ($websites as $website) {
    checkWebsite($website['url']);
}
