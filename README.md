# API REST MediaTekDocuments

## Présentation

Cette API REST, écrite en PHP, est basée sur l’API fournie par le CNED pour le projet **MediaTekDocuments** :
https://github.com/CNED-SLAM/rest_mediatekdocuments

Le dépôt d’origine fournit une base permettant d’exécuter des requêtes sur la base de données `mediatek86`.

Ce dépôt correspond à une **évolution de cette API existante**. Le travail réalisé consiste à adapter et enrichir l’API afin de répondre aux besoins de l’application C# **MediaTekDocuments**, disponible sur le dépôt suivant :
https://github.com/patrickbrouhard/mediatekdocuments

L’API permet d’effectuer des opérations CRUD sur la base de données MySQL `mediatek86`, ainsi que des traitements spécifiques liés :

* aux livres, DVD et revues ;
* aux exemplaires ;
* aux commandes de documents ;
* aux abonnements de revues ;
* à l’authentification applicative des utilisateurs.

## Contexte du projet

Le projet MediaTekDocuments repose sur deux composants principaux :

* une application cliente C# WinForms ;
* une API REST PHP connectée à une base de données MySQL.

L’application C# utilise cette API pour consulter, créer, modifier et supprimer des données relatives à la gestion documentaire d’une médiathèque.

L’objectif de ce dépôt est donc de fournir une API adaptée aux besoins fonctionnels de l’application cliente, tout en conservant la structure générale de l’API initiale fournie comme support de départ.

## Évolutions réalisées

Par rapport à l’API de départ, les principales évolutions concernent :

* le fichier `.env`, utilisé pour la configuration sensible de l’accès à la base de données ;
* le fichier `MyAccessBDD.php`, qui contient les traitements SQL spécifiques ajoutés ;
* le script `mediatek86.sql`, qui crée la structure de la base, les contraintes, les triggers et les données de départ nécessaires au fonctionnement de l’application.

Les fonctionnalités ajoutées ou adaptées permettent notamment :

* la récupération des livres, DVD et revues avec leurs informations associées ;
* la gestion des exemplaires liés aux documents ;
* la création, modification et suppression de documents en transaction ;
* la protection contre la suppression de documents encore liés à des exemplaires, commandes ou abonnements ;
* la gestion des commandes de livres et de DVD ;
* la gestion des abonnements aux revues ;
* la détection des abonnements arrivant prochainement à expiration ;
* l’authentification applicative des utilisateurs avec vérification d’un mot de passe hashé.

## Authentification

L’API utilise deux mécanismes d’authentification distincts.

### Authentification d’accès à l’API

L’accès aux endpoints de l’API est protégé par une authentification HTTP Basic Auth.

Pour tester l’API avec Postman :

* Type : `Basic Auth`
* Username : `admin`
* Password : `adminpwd`

Cette authentification permet d’autoriser l’accès technique à l’API.

### Authentification applicative

L’application C# MediaTekDocuments possède également son propre mécanisme d’authentification.

Lorsqu’un utilisateur se connecte depuis l’application, ses identifiants sont envoyés à l’API via l’endpoint `POST /authentification`.

L’API vérifie alors :

* le login de l’utilisateur ;
* le mot de passe fourni ;
* le hash stocké en base de données, avec `password_verify()` ;
* le service auquel appartient l’utilisateur.

Ce mécanisme permet à l’application C# d’adapter les droits d’accès selon le profil de l’utilisateur connecté.

## Installation en local

### Prérequis

Installer les outils suivants :

* WampServer ou équivalent ;
* Composer ;
* Postman pour tester les endpoints ;
* NetBeans, Visual Studio Code ou un autre IDE pour consulter et modifier le code.

### Installation du projet

1. Télécharger le code de l’API.
2. Dézipper le projet dans le dossier `www` de WampServer.
3. Renommer le dossier en `rest_mediatekdocuments` si nécessaire.
4. Ouvrir une fenêtre de commande dans le dossier du projet.
5. Exécuter la commande suivante pour recréer le dossier `vendor` :

```bash
composer install
```

### Création de la base de données

1. Ouvrir phpMyAdmin.
2. Créer une base de données nommée `mediatek86`.
3. Exécuter le script SQL fourni :

```txt
mediatek86.sql
```

Ce script crée notamment :

* les tables ;
* les contraintes d’intégrité ;
* les triggers SQL ;
* les données de départ.

## Configuration

La configuration de l’accès à la base de données se fait dans le fichier `.env`.

Ce fichier contient les informations nécessaires à la connexion :

* serveur de base de données ;
* nom de la base ;
* identifiant ;
* mot de passe.

Le fichier `.env` ne doit pas être versionné s’il contient des informations sensibles propres à un environnement local ou de production.

## Exploitation de l’API

Adresse locale de l’API :

```txt
http://localhost/rest_mediatekdocuments/
```

L’API repose sur des endpoints REST permettant :

* de récupérer des données avec `GET` ;
* d’insérer des données avec `POST` ;
* de modifier des données avec `PUT` ;
* de supprimer des données avec `DELETE`.

La documentation détaillée des endpoints et des exemples d’utilisation est disponible ici :
[Documentation de l’API](docs/API.md)

## Structure de la documentation

```txt
docs/
└── API.md
```

Le fichier `docs/API.md` décrit :

* le format général des requêtes ;
* l’utilisation des méthodes HTTP ;
* les endpoints spécifiques ajoutés ;
* les exemples d’appels ;
* les traitements SQL particuliers ;
* les triggers utilisés par la base de données.

## Projet associé

Cette API est utilisée par l’application C# MediaTekDocuments.

Dépôt de l’application C# :
https://github.com/patrickbrouhard/mediatekdocuments

