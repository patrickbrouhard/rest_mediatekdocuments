# Présentation de l'API

Cette API, écrite en PHP, est basée sur la structure de l'API présentée dans le dépôt suivant :  
[https://github.com/CNED-SLAM/rest_mediatekdocuments](https://github.com/CNED-SLAM/rest_mediatekdocuments)  
Le readme de ce dépôt présente la structure de la base de l'API (rôle de chaque fichier) et comment l'exploiter.  
Les ajouts faits dans cette API ne concernent que les fichiers `.env` (qui contient les données sensibles d'authentification et d'accès à la BDD) et `MyAccessBDD.php` (dans lequel de nouvelles fonctions ont été ajoutées pour répondre aux demandes de l'application).  
Cette API permet d'exécuter des requêtes SQL sur la BDD Mediatek86 créée avec le SGBDR MySQL.  
Elle est accessible via une authentification "basique" (avec login="admin", pwd="adminpwd").  
Sa vocation actuelle est de répondre aux demandes de l'application MediaTekDocuments, mise en ligne sur le dépôt :  
[https://github.com/patrickbrouhard/mediatekdocuments](https://github.com/patrickbrouhard/mediatekdocuments)

# Installation de l'API en local

Pour tester l'API REST en local, voici le mode opératoire (similaire à celui donné dans le dépôt d'API de base) :

- Installer les outils nécessaires (WampServer ou équivalent, NetBeans ou équivalent pour gérer l'API dans un IDE, Postman pour les tests).
- Télécharger le zip du code de l'API et le dézipper dans le dossier `www` de wampserver (renommer le dossier en `rest_mediatekdocuments`, donc en enlevant `_master`).
- Si *Composer* n'est pas installé, le télécharger avec ce lien et l'installer : [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
- Dans une fenêtre de commandes ouverte en mode admin, aller dans le dossier de l'API et taper `composer install` puis valider pour recréer le vendor.
- Récupérer le script `metiak86.sql` en racine du projet puis, avec phpMyAdmin, créer la BDD `mediatek86` et, dans cette BDD, exécuter le script pour remplir la BDD.
- Ouvrir l'API dans NetBeans pour pouvoir analyser le code et le faire évoluer suivant les besoins.
- Pour tester l'API avec Postman, ne pas oublier de configurer l'authentification (onglet "Authorization", Type "Basic Auth", Username "admin", Password "adminpwd").

# Exploitation de l'API

Adresse de l'API (en local) :  
http://localhost/rest_mediatekdocuments/

Voici les différentes possibilités de sollicitation de l'API, afin d'agir sur la BDD, en ajoutant des informations directement dans l'URL (visible) et éventuellement dans le body (invisible) suivant les besoins :

## Récupérer un contenu (select)

Méthode HTTP : **GET**  
`http://localhost/rest_mediatekdocuments/table/champs` (champs optionnel)

- `table` doit être remplacé par un nom de table (caractères acceptés : alphanumériques et `_`)
- `champs` (optionnel) doit être remplacé par la liste des champs (nom/valeur) qui serviront à la recherche (au format JSON)

## Insérer (insert)

Méthode HTTP : **POST**  
`http://localhost/rest_mediatekdocuments/table`

`table` doit être remplacé par un nom de table (caractères acceptés : alphanumériques et `_`)

Dans le body (Dans Postman, onglet "Body", cocher `x-www-form-urlencoded`), ajouter :

- Key : `champs`
- Value : liste des champs (nom/valeur) qui serviront à l'insertion (au format JSON)

## Modifier (update)

Méthode HTTP : **PUT**  
`http://localhost/rest_mediatekdocuments/table/id` (id optionnel)

- `table` doit être remplacé par un nom de table (caractères acceptés : alphanumériques et `_`)
- `id` (optionnel) doit être remplacé par l'identifiant de la ligne à modifier (caractères acceptés : alphanumériques)

Dans le body (Dans Postman, onglet "Body", cocher `x-www-form-urlencoded`), ajouter :

- Key : `champs`
- Value : liste des champs (nom/valeur) qui serviront à la modification (au format JSON)

## Supprimer (delete)

Méthode HTTP : **DELETE**  
`http://localhost/rest_mediatekdocuments/table/champs` (champs optionnel)

- `table` doit être remplacé par un nom de table (caractères acceptés : alphanumériques et `_`)
- `champs` (optionnel) doit être remplacé par la liste des champs (nom/valeur) qui serviront déterminer les lignes à supprimer (au format JSON)

# Les fonctionnalités ajoutées

Dans `MyAccessBDD.php`, des traitements spécifiques ont été ajoutés (au-delà du CRUD générique) pour répondre aux besoins de l’application C# **MediaTekDocuments** :

- **selectTableSimple** : récupère le contenu des tables simples `genre`, `public`, `rayon`, `etat` (triées par `libelle`) pour alimenter les listes/combos.
- **selectAllLivres / selectAllDvd / selectAllRevues** : retourne les listes de livres/DVD/revues avec les informations “enrichies” (jointures avec `document`, `genre`, `public`, `rayon`) afin d’afficher des libellés plutôt que des identifiants.
- **selectExemplairesDocument** (remplace l’ancien `selectExemplairesRevue`) : récupère les exemplaires d’un document à partir de son `id` (avec l’état associé).
- **Gestion des documents (livre / dvd / revue) en transaction** :
    - création avec **génération d’identifiant** suivant la convention Mediatek (préfixe `0` livre, `1` revue, `2` dvd),
    - mise à jour en séparant les champs `document` et les champs spécifiques,
    - suppression protégée (refus si des exemplaires/commandes/abonnements existent).
- **Commandes de documents (`commandedocument`)** : requêtes dédiées pour lister les commandes (avec filtrage possible par type `livre`/`dvd`) et gérer l’ajout/la modification/la suppression avec cohérence entre les tables `commande` et `commandedocument`.
- **Abonnements (`abonnement`)** :
    - liste des abonnements (commandes de revues),
    - création/suppression avec cohérence via la table `commande`,
    - requête dédiée **abonnements expirant dans X jours**.
- **Endpoint d’authentification applicative** : vérification d’un utilisateur (login/mot de passe hashé) via `password_verify` (utilisé par `POST /authentification`).

---
