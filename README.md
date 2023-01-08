# phpMonit
check if websites and web servers are running and check ssl certificates expiration

Installation :

- Création d'une base de données mysql/mariadb
- Enregistrement de informations de la base de données dans le fichier "includes/db.php"
- Ajout des ressources directement en base de données (un formulaire pour l'ajout de nouvelles ressources à venir)

Configuration pour mettre à jour les informations automatiquement :

- Créer une tâche cron pour exécuter les pages checkWebsites.php et checkServers.php

Par exemple pour mettre à jour les informations toutes les 30 secondes :

* * * * * /usr/bin/curl --silent http://localhost/scripts/checkWebsites.php >> /tmp/curl.log
* * * * * sleep 30; /usr/bin/curl --silent http://localhost/scripts/checkWebsites.php >> /tmp/curl.log

* * * * * /usr/bin/curl --silent http://localhost/scripts/checkServers.php >> /tmp/curl.log
* * * * * sleep 30; /usr/bin/curl --silent http://localhost/scripts/checkServers.php >> /tmp/curl.log
