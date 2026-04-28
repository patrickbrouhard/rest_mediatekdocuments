# API REST MediatekDocuments (PHP)

Cette API utilise un **point d’entrée unique** (`src/index.php`) et un **routage via `.htaccess`** pour exposer des URLs “propres” (ex: `/livre`).

**URL de base (locale) :**

- `http://localhost/rest_mediatekdocuments/`

Exemples :
- `http://localhost/rest_mediatekdocuments/livre`
- `http://localhost/rest_mediatekdocuments/genre`

---

## Table des matières

- [1. URL de base et routage](#1-url-de-base-et-routage)
    - [1.1 Point d’entrée réel (interne)](#11-point-dentrée-réel-interne)
    - [1.2 Routes exposées par .htaccess](#12-routes-exposées-par-htaccess)
- [2. Authentification](#2-authentification)
    - [2.1 Aucun contrôle d’auth (par défaut)](#21-aucun-contrôle-dauth-par-défaut)
    - [2.2 HTTP Basic Auth](#22-http-basic-auth)
    - [2.3 Authentification applicative (endpoint)](#23-authentification-applicative-endpoint)
- [3. Format des requêtes et réponses](#3-format-des-requêtes-et-réponses)
    - [3.1 Paramètres manipulés par l’API](#31-paramètres-manipulés-par-lapi)
    - [3.2 Le paramètre `champs` (JSON)](#32-le-paramètre-champs-json)
    - [3.3 Format des réponses](#33-format-des-réponses)
- [4. Endpoints (GET)](#4-endpoints-get)
- [5. Endpoints (POST)](#5-endpoints-post)
- [6. Endpoints (PUT)](#6-endpoints-put)
- [7. Endpoints (DELETE)](#7-endpoints-delete)
- [8. Conseils Postman / curl](#8-conseils-postman--curl)
- [9. Cheatsheet](#9-cheatsheet)

---

## 1) URL de base et routage

### 1.1 Point d’entrée réel (interne)

Techniquement, tout est traité par :

- `src/index.php`

Ce fichier lit les variables :
- `table` (obligatoire)
- `id` (selon cas, surtout pour PUT)
- `champs` (JSON selon cas)

### 1.2 Routes exposées par .htaccess

Le fichier `.htaccess` (à la racine) réécrit les URLs vers `src/index.php` :

- `/` → `src/index.php`
- `GET /{table}` → `src/index.php?table={table}`
- `GET /{table}/{json}` → `src/index.php?table={table}&champs={json}`
- `POST /{table}` → `src/index.php?table={table}`
- `PUT /{table}` → `src/index.php?table={table}`
- `PUT /{table}/{id}` → `src/index.php?table={table}&id={id}`
- `DELETE /{table}` → `src/index.php?table={table}`
- `DELETE /{table}/{json}` → `src/index.php?table={table}&champs={json}`

> `{table}` accepte : lettres, chiffres et `_` (regex `.htaccess` : `[a-zA-Z0-9_]+`).

Donc, en local, on appelle directement :
- `http://localhost/rest_mediatekdocuments/livre`
  plutôt que :
- `http://localhost/rest_mediatekdocuments/src/index.php?table=livre`

---

## 2) Authentification

L’API supporte 2 niveaux possibles :

1) **Authentification HTTP Basic** (contrôle d’accès à l’API elle-même)
2) **Authentification applicative** via un endpoint (`POST /authentification`) qui vérifie un utilisateur en base

### 2.1 Aucun contrôle d’auth (par défaut)
Si `AUTHENTIFICATION` (dans `.env`) est vide : aucune authentification n’est requise.

### 2.2 HTTP Basic Auth
Si `AUTHENTIFICATION=basic`, l’API attend une authentification HTTP Basic :

- utilisateur attendu : `AUTH_USER`
- mot de passe attendu : `AUTH_PW`

Exemple curl :
```bash
curl -u "monuser:monmdp" \
  "http://localhost/rest_mediatekdocuments/livre"
```

Si l’auth échoue : réponse JSON avec `code=401`.

### 2.3 Authentification applicative (endpoint)
L’API propose aussi un endpoint pour vérifier un login/mot de passe stocké en BDD :

- `POST /authentification` avec `champs.login` + `champs.password`

Voir la section [5. Endpoints (POST)](#5-endpoints-post).

---

## 3) Format des requêtes et réponses

### 3.1 Paramètres manipulés par l’API

L’API récupère les données depuis :
- la query string (`$_GET`)
- les champs POST (`$_POST`)
- le body brut (`php://input`) interprété comme `x-www-form-urlencoded` (`parse_str`)

Paramètres côté code :
- `table` (**obligatoire**) : ressource ciblée
- `id` (optionnel) : identifiant, utilisé notamment pour `PUT /{table}/{id}`
- `champs` (optionnel) : données au format JSON (voir ci-dessous)

Si `table` est absent/vide : HTTP 404 avec :
```json
{"error":"Aucune ressource demandée"}
```

### 3.2 Le paramètre `champs` (JSON)

`champs` est un **objet JSON sérialisé en chaîne**, puis décodé côté serveur.

Deux façons “pratiques” de l’envoyer :

#### A) Dans le body (recommandé)
En `POST/PUT/DELETE`, en `x-www-form-urlencoded` :

```bash
curl -X POST \
  -d 'champs={"titre":"Mon livre","idRayon":"R1","idPublic":"P1","idGenre":"G1"}' \
  "http://localhost/rest_mediatekdocuments/livre"
```

#### B) Dans l’URL (possible, surtout en GET/DELETE)
Le `.htaccess` autorise :
- `GET /{table}/{json}`
- `DELETE /{table}/{json}`

Exemple (GET) :
```bash
curl "http://localhost/rest_mediatekdocuments/exemplaire/%7B%22id%22%3A%221001%22%7D"
```

> Important : si on met le JSON dans l’URL, il faut l’encoder (URL-encoding), sinon ça casse facilement.

### 3.3 Format des réponses

Toutes les réponses sont JSON :

```json
{
  "code": 200,
  "message": "OK",
  "result": ...
}
```

Comportements :
- `code=200` si résultat non `null`
- `code=400` si requête invalide (résultat `null`)
- `code=401` si authentification incorrecte
- `code=500` en cas d’erreur serveur au démarrage

---

# 4) Endpoints (GET)

## GET /livre
Retourne la liste des livres (avec jointures document + genre/public/rayon).

```bash
curl "http://localhost/rest_mediatekdocuments/livre"
```

---

## GET /dvd
Retourne la liste des DVD.

```bash
curl "http://localhost/rest_mediatekdocuments/dvd"
```

---

## GET /revue
Retourne la liste des revues.

```bash
curl "http://localhost/rest_mediatekdocuments/revue"
```

---

## GET /exemplaire/{champs_json}
Retourne les exemplaires pour un document (en pratique une revue), avec :

- requis : `champs.id`

Exemple (JSON dans l’URL) :
```bash
curl "http://localhost/rest_mediatekdocuments/exemplaire/%7B%22id%22%3A%221001%22%7D"
```

Alternative (si on préfère la querystring) :
```bash
curl "http://localhost/rest_mediatekdocuments/src/index.php?table=exemplaire&champs=%7B%22id%22%3A%221001%22%7D"
```

---

## GET /genre
## GET /public
## GET /rayon
## GET /etat
Retourne les lignes de tables “simples” (id + libellé), triées par libellé.

Exemple :
```bash
curl "http://localhost/rest_mediatekdocuments/genre"
```

---

## GET /commandedocument  (avec champs dans querystring)
Le `.htaccess` ne prévoit pas de route `GET /commandedocument/{id}`, mais prévoit `GET /{table}/{json}`.

- attendu : `champs.typemedia` vaut `"livre"` ou `"dvd"` (sinon pas de filtre)

Exemple (JSON dans l’URL) :
```bash
curl "http://localhost/rest_mediatekdocuments/commandedocument/%7B%22typemedia%22%3A%22livre%22%7D"
```

---

## GET /abonnement
Retourne les commandes associées aux revues.

```bash
curl "http://localhost/rest_mediatekdocuments/abonnement"
```

---

## GET /abonnements_expirant_dans (option: /abonnements_expirant_dans/{json})
Retourne les abonnements dont la fin intervient dans les X prochains jours.

- optionnel : `champs.jours` (défaut 30)

Exemple (défaut) :
```bash
curl "http://localhost/rest_mediatekdocuments/abonnements_expirant_dans"
```

Exemple (10 jours) :
```bash
curl "http://localhost/rest_mediatekdocuments/abonnements_expirant_dans/%7B%22jours%22%3A10%7D"
```

---

## GET /{table} (cas général)
Si `{table}` n’est pas un cas spécial, l’API fait :
- sans `champs` : `SELECT * FROM table`
- avec `champs` : filtre `WHERE ... AND ...`

Exemple :
```bash
curl "http://localhost/rest_mediatekdocuments/utilisateur/%7B%22login%22%3A%22admin%22%7D"
```

---

# 5) Endpoints (POST)

## POST /authentification
Endpoint “spécial” (ce n’est pas un INSERT) : vérifie un utilisateur.

- requis : `champs.login`, `champs.password`

Exemple :
```bash
curl -X POST \
  -d 'champs={"login":"admin","password":"secret"}' \
  "http://localhost/rest_mediatekdocuments/authentification"
```

Retour :
- si OK : infos utilisateur (sans mot de passe)
- si KO : résultat vide ou requête invalide selon le cas

---

## POST /livre
Crée un livre (ID auto, préfixe `0`).

- requis (minimum) : `idGenre`, `idPublic`, `idRayon`
- champs possibles : `titre`, `image`, `ISBN`, `auteur`, `collection`, …

Exemple :
```bash
curl -X POST \
  -d 'champs={"titre":"Mon livre","idRayon":"R1","idPublic":"P1","idGenre":"G1","auteur":"Moi"}' \
  "http://localhost/rest_mediatekdocuments/livre"
```

---

## POST /dvd
Crée un DVD (ID auto, préfixe `2`).

- requis (minimum) : `idGenre`, `idPublic`, `idRayon`

Exemple :
```bash
curl -X POST \
  -d 'champs={"titre":"Mon film","idRayon":"R1","idPublic":"P1","idGenre":"G1","duree":120}' \
  "http://localhost/rest_mediatekdocuments/dvd"
```

---

## POST /revue
Crée une revue (ID auto, préfixe `1`).

- requis (minimum) : `idGenre`, `idPublic`, `idRayon`

Exemple :
```bash
curl -X POST \
  -d 'champs={"titre":"Ma revue","idRayon":"R1","idPublic":"P1","idGenre":"G1","periodicite":"Mensuel"}' \
  "http://localhost/rest_mediatekdocuments/revue"
```

---

## POST /commandedocument
Crée une commande de document (livre/dvd). ID auto sur 5 chiffres (`00001`, …).

- requis : `dateCommande`, `montant`, `nbExemplaire`, `idLivreDvd`, `idSuivi`

Exemple :
```bash
curl -X POST \
  -d 'champs={"dateCommande":"2026-04-26","montant":42.5,"nbExemplaire":2,"idLivreDvd":"0001","idSuivi":"1"}' \
  "http://localhost/rest_mediatekdocuments/commandedocument"
```

---

## POST /abonnement
Crée un abonnement (commande de revue). ID auto sur 5 chiffres.

- requis : `dateCommande`, `montant`, `dateFinAbonnement`, `idRevue`
- contrainte : `dateFinAbonnement >= dateCommande`

Exemple :
```bash
curl -X POST \
  -d 'champs={"dateCommande":"2026-04-26","montant":90,"dateFinAbonnement":"2027-04-26","idRevue":"1001"}' \
  "http://localhost/rest_mediatekdocuments/abonnement"
```

---

## POST /{table} (cas général)
Si `{table}` n’est pas spécial, l’API fait un INSERT direct avec les clés de `champs`.

Exemple :
```bash
curl -X POST \
  -d 'champs={"id":"S9","libelle":"Nouveau service"}' \
  "http://localhost/rest_mediatekdocuments/service"
```

---

# 6) Endpoints (PUT)

Rappels routage `.htaccess` :

- `PUT /{table}` → `table={table}` (l’`id` peut aussi être dans `champs` selon le traitement)
- `PUT /{table}/{id}` → `table={table}&id={id}`

## PUT /livre/{id}
Met à jour un livre (met à jour `document` + `livre` si champs spécifiques présents).

Exemple :
```bash
curl -X PUT \
  -d 'champs={"titre":"Nouveau titre","auteur":"Autre auteur"}' \
  "http://localhost/rest_mediatekdocuments/livre/0001"
```

---

## PUT /dvd/{id}
Exemple :
```bash
curl -X PUT \
  -d 'champs={"synopsis":"Résumé mis à jour"}' \
  "http://localhost/rest_mediatekdocuments/dvd/2001"
```

---

## PUT /revue/{id}
Exemple :
```bash
curl -X PUT \
  -d 'champs={"periodicite":"Hebdomadaire"}' \
  "http://localhost/rest_mediatekdocuments/revue/1001"
```

---

## PUT /exemplaire
Met à jour l’état d’un exemplaire.

- requis : `champs.id`, `champs.numero`, `champs.idEtat`

Exemple :
```bash
curl -X PUT \
  -d 'champs={"id":"1001","numero":"00001","idEtat":"2"}' \
  "http://localhost/rest_mediatekdocuments/exemplaire"
```

---

## PUT /commandedocument/{id}
Met à jour une commande document.

Champs possibles :
- pour `commande` : `dateCommande`, `montant`
- pour `commandedocument` : `nbExemplaire`, `idLivreDvd`, `idSuivi`

Exemple :
```bash
curl -X PUT \
  -d 'champs={"idSuivi":"3"}' \
  "http://localhost/rest_mediatekdocuments/commandedocument/00012"
```

---

## PUT /{table}/{id} (cas général)
Exemple :
```bash
curl -X PUT \
  -d 'champs={"libelle":"Roman"}' \
  "http://localhost/rest_mediatekdocuments/genre/G1"
```

---

# 7) Endpoints (DELETE)

Rappels routage `.htaccess` :

- `DELETE /{table}` → `table={table}`
- `DELETE /{table}/{json}` → `table={table}&champs={json}`

> Important : le code attend l’identifiant **dans `champs.id`** (pas dans `{id}`) pour la plupart des suppressions.

## DELETE /livre  (avec champs dans body)
- requis : `champs.id`
- suppression protégée : refuse si exemplaires/commandes existent

Exemple :
```bash
curl -X DELETE \
  -d 'champs={"id":"0001"}' \
  "http://localhost/rest_mediatekdocuments/livre"
```

---

## DELETE /dvd
Exemple :
```bash
curl -X DELETE \
  -d 'champs={"id":"2001"}' \
  "http://localhost/rest_mediatekdocuments/dvd"
```

---

## DELETE /revue
Exemple :
```bash
curl -X DELETE \
  -d 'champs={"id":"1001"}' \
  "http://localhost/rest_mediatekdocuments/revue"
```

---

## DELETE /exemplaire
- requis : `champs.id`, `champs.numero`

Exemple :
```bash
curl -X DELETE \
  -d 'champs={"id":"1001","numero":"00001"}' \
  "http://localhost/rest_mediatekdocuments/exemplaire"
```

---

## DELETE /commandedocument
- requis : `champs.id`

Exemple :
```bash
curl -X DELETE \
  -d 'champs={"id":"00012"}' \
  "http://localhost/rest_mediatekdocuments/commandedocument"
```

---

## DELETE /abonnement
- requis : `champs.id`

Exemple :
```bash
curl -X DELETE \
  -d 'champs={"id":"00077"}' \
  "http://localhost/rest_mediatekdocuments/abonnement"
```

---

## DELETE /{table}/{json} (optionnel)
Le `.htaccess` autorise de passer `champs` dans l’URL pour DELETE.

Exemple (suppression filtrée) :
```bash
curl -X DELETE \
  "http://localhost/rest_mediatekdocuments/service/%7B%22id%22%3A%22S9%22%7D"
```

---

# 8) Conseils Postman / curl

## Postman : où mettre `champs` ?
Le plus simple :
- Method : GET/POST/PUT/DELETE
- URL : `http://localhost/rest_mediatekdocuments/<table>` (ex: `/livre`)
- Body (pour POST/PUT/DELETE) :
    - `x-www-form-urlencoded`
    - clé : `champs`
    - valeur : un JSON (ex: `{"id":"0001"}`)

## PUT/DELETE : Content-Type
En curl, si besoin :
```bash
-H "Content-Type: application/x-www-form-urlencoded"
```

## JSON dans l’URL
Possible, mais fragile : il faut URL-encoder.
À réserver aux tests simples en GET.

---

# 9) Cheatsheet

- Livres : `GET http://localhost/rest_mediatekdocuments/livre`
- DVD : `GET http://localhost/rest_mediatekdocuments/dvd`
- Revues : `GET http://localhost/rest_mediatekdocuments/revue`
- Genres/Public/Rayon/Etat :
    - `GET http://localhost/rest_mediatekdocuments/genre`
    - `GET http://localhost/rest_mediatekdocuments/public`
    - `GET http://localhost/rest_mediatekdocuments/rayon`
    - `GET http://localhost/rest_mediatekdocuments/etat`

- Exemplaires d’un doc :
    - `GET http://localhost/rest_mediatekdocuments/exemplaire/{json_url_encodé}`
    - ou envoyer `champs` via querystring vers `src/index.php` si tu préfères

- Auth applicative :
    - `POST http://localhost/rest_mediatekdocuments/authentification` + body `champs={"login":"...","password":"..."}`

- Créer :
    - `POST /livre` / `POST /dvd` / `POST /revue` (avec `champs`)
    - `POST /commandedocument` (avec `champs`)
    - `POST /abonnement` (avec `champs`)

- Modifier :
    - `PUT /livre/{id}` (avec `champs`)
    - `PUT /dvd/{id}` (avec `champs`)
    - `PUT /revue/{id}` (avec `champs`)
    - `PUT /commandedocument/{id}` (avec `champs`)
    - `PUT /exemplaire` (avec `champs` contenant id/numero/idEtat)

- Supprimer :
    - `DELETE /livre` (avec `champs={"id":"..."}`)
    - `DELETE /dvd` (avec `champs={"id":"..."}`)
    - `DELETE /revue` (avec `champs={"id":"..."}`)
    - `DELETE /commandedocument` (avec `champs={"id":"..."}`)
    - `DELETE /abonnement` (avec `champs={"id":"..."}`)
    - `DELETE /exemplaire` (avec `champs={"id":"...","numero":"..."}`)