# Documentation de l’API REST MediaTekDocuments

## URL de l’API

Adresse locale de l’API :

```txt
http://localhost/rest_mediatekdocuments/
```

Selon la configuration de l’application C#, l’API peut être appelée :

* en local, pendant le développement ;
* en ligne, lorsque l’application est compilée en mode release.

L’URL utilisée par l’application C# est définie dans son fichier de configuration.

## Objectif de cette documentation

Cette documentation décrit principalement la façon dont l’application C# **MediaTekDocuments** communique avec l’API REST PHP.

Certaines variantes peuvent fonctionner, notamment en transmettant `champs` dans le body pour des requêtes `GET` ou `DELETE`. Cependant, la forme mise en avant ici est celle utilisée par l’application C#, car elle correspond au fonctionnement réel du client applicatif.

## Authentification

L’API utilise deux mécanismes d’authentification distincts.

### Authentification d’accès à l’API

L’accès technique aux endpoints de l’API est protégé par une authentification HTTP Basic Auth.

Dans Postman :

* aller dans l’onglet `Authorization` ;
* choisir le type `Basic Auth` ;
* renseigner les identifiants d’accès à l’API.

Identifiants utilisés pour les tests :

```txt
Username : admin
Password : adminpwd
```

Cette authentification permet d’autoriser l’accès à l’API elle-même.

### Authentification applicative

L’application C# MediaTekDocuments possède également son propre mécanisme d’authentification.

Lorsqu’un utilisateur se connecte depuis l’application, ses identifiants sont envoyés à l’API via l’endpoint :

```http
POST /authentification
```

L’API vérifie alors :

* le login de l’utilisateur ;
* le mot de passe fourni ;
* le hash stocké en base de données, avec `password_verify()` ;
* le service auquel appartient l’utilisateur.

Ce mécanisme permet à l’application C# d’adapter les droits d’accès selon le profil de l’utilisateur connecté.

La requête utilise la méthode `POST` car les identifiants sont transmis dans le body de la requête, et non directement dans l’URL. Cela évite d’exposer le login et le mot de passe dans l’historique, les logs ou les traces réseau visibles au niveau de l’URL.

## Principe général des requêtes

L’API repose sur des endpoints REST construits autour :

* d’une méthode HTTP ;
* d’un nom de ressource ;
* éventuellement d’un identifiant ;
* éventuellement d’un objet JSON contenant les paramètres.

Les principales méthodes utilisées sont :

* `GET` pour récupérer des données ;
* `POST` pour insérer des données ;
* `PUT` pour modifier des données ;
* `DELETE` pour supprimer des données.

## Format général utilisé par l’application C#

L’application C# utilise principalement les formats suivants.

### Récupération simple

Pour récupérer toutes les données d’une ressource :

```http
GET /table
```

Exemple :

```http
GET /livre
```

### Récupération filtrée

Pour récupérer des données avec paramètres, l’application C# transmet un objet JSON directement dans l’URL :

```http
GET /table/{"champ":"valeur"}
```

Exemple :

```http
GET /exemplaire/{"id":"00002"}
```

En pratique, le JSON peut être encodé dans l’URL.

Exemple équivalent encodé :

```http
GET /exemplaire/%7B%22id%22%3A%2200002%22%7D
```

### Insertion

Pour insérer des données, l’application C# envoie une requête `POST` avec les données dans le body, sous la forme `x-www-form-urlencoded`.

```http
POST /table
```

Body :

```txt
champs={"champ":"valeur"}
```

### Modification

Pour modifier une donnée existante, l’identifiant est placé dans l’URL et les champs à modifier sont envoyés dans le body.

```http
PUT /table/id
```

Body :

```txt
champs={"champ":"nouvelle valeur"}
```

### Suppression

Pour supprimer une donnée, l’application C# transmet les critères de suppression sous forme de JSON dans l’URL.

```http
DELETE /table/{"id":"valeur"}
```

En pratique, le JSON peut être encodé dans l’URL.

Exemple équivalent encodé :

```http
DELETE /livre/%7B%22id%22%3A%2200030%22%7D
```
## Endpoints de consultation

### Récupérer les livres

```http
GET http://localhost/rest_mediatekdocuments/livre
```

Retourne la liste des livres avec les informations associées issues des tables liées.

### Récupérer les DVD

```http
GET http://localhost/rest_mediatekdocuments/dvd
```

Retourne la liste des DVD avec les informations associées issues des tables liées.

### Récupérer les revues

```http
GET http://localhost/rest_mediatekdocuments/revue
```

Retourne la liste des revues avec les informations associées issues des tables liées.

### Récupérer les genres

```http
GET http://localhost/rest_mediatekdocuments/genre
```

Retourne les genres disponibles.

### Récupérer les publics

```http
GET http://localhost/rest_mediatekdocuments/public
```

Retourne les publics disponibles.

### Récupérer les rayons

```http
GET http://localhost/rest_mediatekdocuments/rayon
```

Retourne les rayons disponibles.

### Récupérer les états d’exemplaires

```http
GET http://localhost/rest_mediatekdocuments/etat
```

Retourne les états disponibles pour les exemplaires.

### Récupérer les états de suivi des commandes

```http
GET http://localhost/rest_mediatekdocuments/suivi
```

Retourne les états de suivi utilisés pour les commandes.

## Endpoints liés aux exemplaires

### Récupérer les exemplaires d’un document

```http
GET http://localhost/rest_mediatekdocuments/exemplaire/{"id":"00002"}
```

Version encodée :

```http
GET http://localhost/rest_mediatekdocuments/exemplaire/%7B%22id%22%3A%2200002%22%7D
```

Cette requête retourne les exemplaires associés au document dont l’identifiant est fourni.

### Modifier un exemplaire

```http
PUT http://localhost/rest_mediatekdocuments/exemplaire/00002
```

Body `x-www-form-urlencoded` :

```txt
champs={"id":"00002","numero":"1","idEtat":"00002"}
```

La modification d’un exemplaire se fait à partir de l’identifiant du document et du numéro d’exemplaire.

### Supprimer un exemplaire

```http
DELETE http://localhost/rest_mediatekdocuments/exemplaire/{"id":"00002","numero":"1"}
```

Version encodée :

```http
DELETE http://localhost/rest_mediatekdocuments/exemplaire/%7B%22id%22%3A%2200002%22%2C%22numero%22%3A%221%22%7D
```

## Endpoints liés aux documents

Les documents sont répartis entre une table commune `document` et des tables spécialisées :

* `livre` ;
* `dvd` ;
* `revue`.

Pour les livres et les DVD, une table intermédiaire `livres_dvd` est également utilisée.

### Ajouter un livre

```http
POST http://localhost/rest_mediatekdocuments/livre
```

Body `x-www-form-urlencoded` :

```txt
champs={"titre":"Test livre","idRayon":"LV001","idPublic":"00002","idGenre":"10006","ISBN":"123456789","auteur":"Moi","collection":"Test"}
```

### Modifier un livre

```http
PUT http://localhost/rest_mediatekdocuments/livre/00027
```

Body `x-www-form-urlencoded` :

```txt
champs={"titre":"Test livre modifié","collection":"Nouvelle collection"}
```

### Supprimer un livre

```http
DELETE http://localhost/rest_mediatekdocuments/livre/{"id":"00030"}
```

Version encodée :

```http
DELETE http://localhost/rest_mediatekdocuments/livre/%7B%22id%22%3A%2200030%22%7D
```

### Ajouter un DVD

```http
POST http://localhost/rest_mediatekdocuments/dvd
```

Body `x-www-form-urlencoded` :

```txt
champs={"titre":"Test DVD","idRayon":"DV001","idPublic":"00002","idGenre":"10006","realisateur":"Réalisateur test","duree":"120","synopsis":"Synopsis de test"}
```

### Modifier un DVD

```http
PUT http://localhost/rest_mediatekdocuments/dvd/20001
```

Body `x-www-form-urlencoded` :

```txt
champs={"titre":"Titre DVD modifié","duree":"130"}
```

### Supprimer un DVD

```http
DELETE http://localhost/rest_mediatekdocuments/dvd/{"id":"20001"}
```

Version encodée :

```http
DELETE http://localhost/rest_mediatekdocuments/dvd/%7B%22id%22%3A%2220001%22%7D
```

### Ajouter une revue

```http
POST http://localhost/rest_mediatekdocuments/revue
```

Body `x-www-form-urlencoded` :

```txt
champs={"titre":"Test revue","idRayon":"PR001","idPublic":"00002","idGenre":"10006","periodicite":"mensuel","delaiMiseADispo":"15"}
```

### Modifier une revue

```http
PUT http://localhost/rest_mediatekdocuments/revue/10001
```

Body `x-www-form-urlencoded` :

```txt
champs={"titre":"Titre revue modifié","periodicite":"hebdomadaire"}
```

### Supprimer une revue

```http
DELETE http://localhost/rest_mediatekdocuments/revue/{"id":"10001"}
```

Version encodée :

```http
DELETE http://localhost/rest_mediatekdocuments/revue/%7B%22id%22%3A%2210001%22%7D
```

## Génération des identifiants de documents

Lors de la création d’un document, l’API génère automatiquement un identifiant selon la convention MediaTek :

* les livres commencent par `0` ;
* les revues commencent par `1` ;
* les DVD commencent par `2`.

Exemples :

* `00001` pour un livre ;
* `10001` pour une revue ;
* `20001` pour un DVD.

Cette génération est réalisée côté API.

## Suppression protégée des documents

Avant de supprimer un document, l’API vérifie s’il est encore associé à d’autres données.

La suppression est refusée si le document est lié :

* à des exemplaires ;
* à des commandes pour les livres et DVD ;
* à des abonnements pour les revues.

Cette vérification évite de supprimer un document encore utilisé par l’application.

## Endpoints liés aux commandes de documents

Les commandes de documents concernent les livres et les DVD.

### Récupérer les commandes de livres

```http
GET http://localhost/rest_mediatekdocuments/commandedocument/{"typemedia":"livre"}
```

Version encodée :

```http
GET http://localhost/rest_mediatekdocuments/commandedocument/%7B%22typemedia%22%3A%22livre%22%7D
```

### Récupérer les commandes de DVD

```http
GET http://localhost/rest_mediatekdocuments/commandedocument/{"typemedia":"dvd"}
```

Version encodée :

```http
GET http://localhost/rest_mediatekdocuments/commandedocument/%7B%22typemedia%22%3A%22dvd%22%7D
```

### Ajouter une commande de document

```http
POST http://localhost/rest_mediatekdocuments/commandedocument
```

Body `x-www-form-urlencoded` :

```txt
champs={"dateCommande":"2026-04-10","montant":"32.00","nbExemplaire":"1","idLivreDvd":"00001","idSuivi":"1"}
```

### Modifier une commande de document

```http
PUT http://localhost/rest_mediatekdocuments/commandedocument/00002
```

Body `x-www-form-urlencoded` :

```txt
champs={"dateCommande":"2026-04-12","montant":50,"idLivreDvd":"00002","nbExemplaire":1,"idSuivi":1}
```

### Supprimer une commande de document

```http
DELETE http://localhost/rest_mediatekdocuments/commandedocument/{"id":"00001"}
```

Version encodée :

```http
DELETE http://localhost/rest_mediatekdocuments/commandedocument/%7B%22id%22%3A%2200001%22%7D
```

La suppression est effectuée à partir de la table `commande`. La suppression liée dans `commandedocument` est gérée par trigger SQL.

## Endpoints liés aux abonnements

Les abonnements concernent les commandes liées aux revues.

### Récupérer les abonnements

```http
GET http://localhost/rest_mediatekdocuments/abonnement
```

### Ajouter un abonnement

```http
POST http://localhost/rest_mediatekdocuments/abonnement
```

Body `x-www-form-urlencoded` :

```txt
champs={"dateCommande":"2026-04-10","montant":"32.00","dateFinAbonnement":"2027-04-10","idRevue":"10001"}
```

La date de fin d’abonnement doit être postérieure ou égale à la date de commande.

### Supprimer un abonnement

```http
DELETE http://localhost/rest_mediatekdocuments/abonnement/{"id":"00015"}
```

Version encodée :

```http
DELETE http://localhost/rest_mediatekdocuments/abonnement/%7B%22id%22%3A%2200015%22%7D
```

La suppression est effectuée à partir de la table `commande`. La suppression liée dans `abonnement` est gérée par trigger SQL.

### Récupérer les abonnements arrivant à expiration

```http
GET http://localhost/rest_mediatekdocuments/abonnements_expirant_dans/{"jours":30}
```

Version encodée :

```http
GET http://localhost/rest_mediatekdocuments/abonnements_expirant_dans/%7B%22jours%22%3A30%7D
```

Cet endpoint retourne les abonnements dont la date de fin est comprise entre la date du jour et la date du jour plus le nombre de jours indiqué.

## Endpoint d’authentification applicative

L’authentification applicative est utilisée par l’application C# MediaTekDocuments.

Elle permet de vérifier les identifiants d’un utilisateur stocké en base de données.

```http
POST http://localhost/rest_mediatekdocuments/authentification
```

Body `x-www-form-urlencoded` :

```txt
champs={"login":"login_utilisateur","password":"mot_de_passe"}
```

Exemple de test :

```txt
champs={"login":"adm","password":"test"}
```

Le mot de passe fourni est comparé au hash stocké en base de données avec `password_verify()`.

En cas de succès, l’API retourne les informations de l’utilisateur, sans renvoyer le mot de passe, même hashé.

## Codes de suivi des commandes

La table `suivi` contient les états possibles d’une commande :

* `1` : en cours ;
* `2` : relancée ;
* `3` : livrée ;
* `4` : réglée.

Le passage d’une commande de document à l’état `livrée` déclenche la création automatique des exemplaires par trigger SQL.

## Triggers SQL

Le script `mediatek86.sql` contient plusieurs triggers SQL.

### Suppression liée aux commandes

Lorsqu’une ligne de la table `commande` est supprimée, des triggers suppriment automatiquement les données liées :

* dans `commandedocument` pour les commandes de livres et DVD ;
* dans `abonnement` pour les commandes de revues.

### Création automatique des exemplaires

Lorsqu’une commande de document passe à l’état `livrée`, un trigger crée automatiquement les exemplaires correspondants dans la table `exemplaire`.

Le nombre d’exemplaires créés dépend de la valeur du champ `nbExemplaire`.

## Transactions

Les traitements complexes sont réalisés dans des transactions afin d’éviter les incohérences entre plusieurs tables.

C’est notamment le cas pour :

* la création d’un livre ;
* la création d’un DVD ;
* la création d’une revue ;
* la modification d’un document ;
* la suppression d’un document ;
* la création d’une commande ;
* la modification d’une commande ;
* la création d’un abonnement.

En cas d’échec d’une opération dans une transaction, les modifications sont annulées.

## Synthèse des formats utilisés

| Action                       |  Méthode | Format principal utilisé par l’application C#         |
| ---------------------------- | -------: | ----------------------------------------------------- |
| Récupération simple          |    `GET` | `/table`                                              |
| Récupération filtrée         |    `GET` | `/table/{json}`                                       |
| Insertion                    |   `POST` | `/table` avec `champs=<json>` dans le body            |
| Modification                 |    `PUT` | `/table/id` avec `champs=<json>` dans le body         |
| Suppression                  | `DELETE` | `/table/{json}`                                       |
| Authentification applicative |   `POST` | `/authentification` avec `champs=<json>` dans le body |

## Remarque finale

Les exemples avec JSON directement dans l’URL sont présentés pour rester lisibles. En pratique, le client C# peut encoder ces valeurs avant l’envoi de la requête, notamment pour les suppressions.

Exemple lisible :

```http
DELETE /livre/{"id":"00030"}
```

Exemple encodé équivalent :

```http
DELETE /livre/%7B%22id%22%3A%2200030%22%7D
```
