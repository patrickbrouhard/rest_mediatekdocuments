<?php
include_once("AccessBDD.php");
require_once("TypeDocument.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD
{
    public const LIVRE = "livre";
    public const DVD = "dvd";
    public const REVUE = "revue";
    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct()
    {
        try {
            parent::__construct();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function isChampsObligatoiresAbsents(array $obligatoires, ?array $champs) : bool
    {
        if (empty($champs)) {
            return true;
        }

        foreach ($obligatoires as $champ) {
            if (!isset($champs[$champ])) {
                return true;
            }
        }
        return false;
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */
    protected function traitementSelect(string $table, ?array $champs) : ?array
    {
        switch ($table) {
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "commandedocument":
                return $this->selectCommandesDocument($champs['typemedia']);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */
    protected function traitementInsert(string $table, ?array $champs) : ?int
    {
        switch ($table) {
            case "livre":
                return $this->insertLivre($champs);
            case "dvd":
                return $this->insertDvd($champs);
            case "revue":
                return $this->insertRevue($champs);
            case "commandedocument":
                return $this->insertCommandeDocument($champs);
            default:
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int
    {
        if (empty($id) || empty($champs)) {
            return 0;
        }
        
        switch ($table) {
            case "livre":
                return $this->updateLivre($id, $champs);
            case "dvd":
                return $this->updateDvd($id, $champs);
            case "revue":
                return $this->updateRevue($id, $champs);
            case "commandedocument":
                return $this->updateCommandeDocument($id, $champs);
            default:
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }
    }
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */
    protected function traitementDelete(string $table, ?array $champs) : ?int
    {
        if (empty($champs) || empty($champs['id'])) {
            return null;
        }
        
        $id = $champs['id'];

        if ($table === "livre" || $table === "dvd" || $table === "revue") {
            if (!$this->isOktoDeleteDocument($table, $id)) {
                return null;
            }
        }

        switch ($table) {
            case "livre":
                return $this->deleteLivre($id);
            case "dvd":
                return $this->deleteDvd($id);
            case "revue":
                return $this->deleteRevue($id);
            case "commandedocument":
                return $this->deleteCommandeDocument($id);
            default:
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);
        }
    }
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array
    {
        if (empty($champs)) {
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);
        } else {
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value) {
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);
            return $this->conn->queryBDD($requete, $champs);
        }
    }

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value) {
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value) {
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string|null $id
     * @param array|null $champs
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        if (is_null($id)) {
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value) {
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $champs["id"] = $id;
        $requete .= " where id=:id;";
        return $this->conn->updateBDD($requete, $champs);
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int
    {
        if (empty($champs)) {
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value) {
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);
        return $this->conn->updateBDD($requete, $champs);
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array
    {
        $requete = "select * from $table order by libelle;";
        return $this->conn->queryBDD($requete);
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array
    {
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array
    {
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array
    {
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array
    {
        if (empty($champs)) {
            return null;
        }
        if (!array_key_exists('id', $champs)) {
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    /**
    * Génère le prochain identifiant disponible pour un type de document donné.
    *
    * Cconvention Mediatek :
    *  - livres : commencent par 0xxxx
    *  - revues : commencent par 1xxxx
    *  - dvd    : commencent par 2xxxx
    *
    * @param TypeDocument $type Type de document (LIVRE | DVD | REVUE)
    * @return string Nouvel identifiant formaté
    * @throws Exception si type inconnu
    */
    private function getNextId(TypeDocument $type): string
    {
        $table = $type->table();
        $prefixe = $type->index(); // "0", "1", "2"
        
        // trouve la ligne avec l'id le plus élevé et pose un
        // verrou exclusif sur la ligne tant qu'on a pas fait COMMIT (FOR UPDATE)
        // (aucune autre transaction ne peut calculer le même id)
        $requete = "SELECT id FROM $table ORDER BY id DESC LIMIT 1 FOR UPDATE;";
        $result = $this->conn->queryBDD($requete);
        
        // Si la table est vide : premier id de la table
        if (empty($result)) {
            return $prefixe . "0001";
        }
        $dernierId = $result[0]["id"];
        
        // Extraire la partie numérique (tout sauf le premier caractère)
        $numerique = (int)substr($dernierId, 1);
        $numerique++;
        
        // Reconstruire l'ID : préfixe + partie numérique
        return $prefixe . str_pad($numerique, 4, "0", STR_PAD_LEFT);
    }
    
    private function insertDocument(string $id, array $champs): ?int
    {
        return $this->insertOneTupleOneTable("document", [
            "id" => $id,
            "titre" => $champs["titre"],
            "image" => $champs["image"] ?? null,
            "idRayon" => $champs["idRayon"],
            "idPublic" => $champs["idPublic"],
            "idGenre" => $champs["idGenre"]
        ]);
    }
    
    private function updateDocument(string $id, array $champs): ?int
    {
        $listeBlanche = ["titre", "image", "idRayon", "idPublic", "idGenre"];

        // champs NOT NULL en base
        $champsNonNull = ["idRayon", "idPublic", "idGenre"];

        $donnees = [];

        foreach ($listeBlanche as $key) {
            if (array_key_exists($key, $champs)) {

                // protection : ces champs ne doivent pas être null dans la BDD
                if (in_array($key, $champsNonNull) && $champs[$key] === null) {
                    return null;
                }

                $donnees[$key] = $champs[$key];
            }
        }
        return $this->updateOneTupleOneTable("document", $id, $donnees);
    }
    
    /**
     * Insère un livre dans la base
     * Si un insert échoue, il throw et déclenche le catch dans transaction()
     * et donc le rollback
     *
     * @param array $champs
     * @return ?int
     */
    private function insertLivre(array $champs): ?int
    {
        if ($this->isChampsObligatoiresAbsents(["idGenre", "idPublic", "idRayon"], $champs)) {
            return null;
        }

        try {
            // appel de transaction en lui passant la fonction anonyme (c'est à
            // dire le bloc en dessous)
            $this->conn->transaction(function () use ($champs) {

                // Génération d’un nouvel identifiant sécurisé (FOR UPDATE)
                $id = $this->getNextId(TypeDocument::LIVRE);

                // insertion document (table mère)
                if (!$this->insertDocument($id, $champs)) {
                    throw new Exception("Erreur insertion document");
                }

                // insertion livres_dvd
                if (!$this->insertOneTupleOneTable("livres_dvd", [
                    "id" => $id
                ])) {
                    throw new Exception("Erreur insertion livres_dvd");
                }

                // insertion livre (table spécifique)
                if (!$this->insertOneTupleOneTable("livre", [
                    "id" => $id,
                    "ISBN" => $champs["ISBN"] ?? null,
                    "auteur" => $champs["auteur"] ?? null,
                    "collection" => $champs["collection"] ?? null
                ])) {
                    throw new Exception("Erreur insertion livre");
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
    * Insère un DVD dans la base
    *
    * Si un insert échoue, il throw et déclenche le catch dans transaction()
    * et donc le rollback
    *
    * @param array $champs
    * @return int 1 si succès, 0 sinon
    */
    private function insertDvd(array $champs): ?int
    {
        if ($this->isChampsObligatoiresAbsents(["idGenre", "idPublic", "idRayon"], $champs)) {
            return null;
        }

        try {

            $this->conn->transaction(function () use ($champs) {

                $id = $this->getNextId(TypeDocument::DVD);

                if (!$this->insertDocument($id, $champs)) {
                    throw new Exception("Erreur insertion document");
                }

                if (!$this->insertOneTupleOneTable("livres_dvd", [
                    "id" => $id
                ])) {
                    throw new Exception("Erreur insertion livres_dvd");
                }

                if (!$this->insertOneTupleOneTable("dvd", [
                    "id" => $id,
                    "realisateur" => $champs["realisateur"] ?? null,
                    "duree" => $champs["duree"] ?? null,
                    "synopsis" => $champs["synopsis"] ?? null
                ])) {
                    throw new Exception("Erreur insertion dvd");
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Insère une revue dans la base (transaction atomique)
     *
     * Si un insert échoue, il throw et déclenche le catch dans transaction()
     * et donc le rollback
     *
     * @param array $champs
     * @return int 1 si succès, 0 sinon
     */
    private function insertRevue(array $champs): ?int
    {
        if ($this->isChampsObligatoiresAbsents(["idGenre", "idPublic", "idRayon"], $champs)) {
            return null;
        }

        try {
            $this->conn->transaction(function () use ($champs) {

                $id = $this->getNextId(TypeDocument::REVUE);

                if (!$this->insertDocument($id, $champs)) {
                    throw new Exception("Erreur insertion document");
                }

                if (!$this->insertOneTupleOneTable("revue", [
                    "id" => $id,
                    "periodicite" => $champs["periodicite"] ?? null,
                    "delaiMiseADispo" => $champs["delaiMiseADispo"] ?? null
                ])) {
                    throw new Exception("Erreur insertion revue");
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Met à jour un livre
     *
     * Met à jour :
     * - document
     * - livre
     *
     * rollback automatique si erreur
     *
     * @param string $id
     * @param array $champs
     * @return int 1 si succès, 0 sinon
     */
    private function updateLivre(string $id, array $champs): ?int
    {
        try {
            $this->conn->transaction(function () use ($id, $champs) {

                if ($this->updateDocument($id, $champs) === null) {
                    throw new Exception("Erreur update document");
                }

                $specifique = [];

                foreach (["ISBN", "auteur", "collection"] as $key) {
                    if (array_key_exists($key, $champs)) {
                        $specifique[$key] = $champs[$key];
                    }
                }

                if (!empty($specifique)) {
                    if ($this->updateOneTupleOneTable(
                        "livre",
                        $id,
                        $specifique
                    ) === null) {
                        throw new Exception("Erreur update livre");
                    }
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Met à jour un DVD
     *
     * Met à jour :
     * - document
     * - dvd
     *
     * rollback automatique si erreur
     *
     * @param string $id
     * @param array $champs
     * @return int 1 si succès, 0 sinon
     */
    private function updateDvd(string $id, array $champs): ?int
    {
        try {

            $this->conn->transaction(function () use ($id, $champs) {

                if ($this->updateDocument($id, $champs) === null) {
                    throw new Exception("Erreur update document");
                }

                $specific = [];

                foreach (["realisateur", "duree", "synopsis"] as $key) {
                    if (array_key_exists($key, $champs)) {
                        $specific[$key] = $champs[$key];
                    }
                }

                if (!empty($specific)) {

                    if ($this->updateOneTupleOneTable(
                        "dvd",
                            $id,
                        $specific,
                    ) === null) {
                        throw new Exception("Erreur update dvd");
                    }
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Met à jour une revue
     *
     * Met à jour :
     * - document
     * - revue
     *
     * rollback automatique si erreur
     *
     * @param string $id
     * @param array $champs
     * @return int 1 si succès, 0 sinon
     */
    private function updateRevue(string $id, array $champs): ?int
    {
        try {

            $this->conn->transaction(function () use ($id, $champs) {

                if ($this->updateDocument($id, $champs) === null) {
                    throw new Exception("Erreur update document");
                }

                $specific = [];

                foreach (["periodicite", "delaiMiseADispo"] as $key) {
                    if (array_key_exists($key, $champs)) {
                        $specific[$key] = $champs[$key];
                    }
                }

                if (!empty($specific)) {

                    if ($this->updateOneTupleOneTable(
                        "revue",
                            $id,
                        $specific,
                    ) === null) {
                        throw new Exception("Erreur update revue");
                    }
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Supprime un livre
     *
     * Supprime successivement :
     * - livre
     * - livres_dvd
     * - document
     *
     * rollback automatique si erreur
     *
     * @param string $id
     * @return int 1 si succès, 0 sinon
     */
    private function deleteLivre(string $id): ?int
    {
        try {
            $this->conn->transaction(function () use ($id) {

                if ($this->deleteTuplesOneTable("livre", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression livre");
                }

                if ($this->deleteTuplesOneTable("livres_dvd", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression livres_dvd");
                }

                if ($this->deleteTuplesOneTable("document", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression document");
                }

            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Supprime un dvd (transaction atomique)
     *
     * Supprime successivement :
     * - dvd
     * - livres_dvd
     * - document
     *
     * rollback automatique si erreur
     *
     * @param string $id
     * @return int 1 si succès, 0 sinon
     */
    private function deleteDvd(string $id): ?int
    {
        try {

            $this->conn->transaction(function () use ($id) {

                if ($this->deleteTuplesOneTable("dvd", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression dvd");
                }

                if ($this->deleteTuplesOneTable("livres_dvd", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression livres_dvd");
                }

                if ($this->deleteTuplesOneTable("document", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression document");
                }

            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Supprime une revue
     *
     * Supprime successivement :
     * - revue
     * - document
     *
     * rollback automatique si erreur
     *
     * @param string $id
     * @return int 1 si succès, 0 sinon
     */
    private function deleteRevue(string $id): ?int
    {
        try {

            $this->conn->transaction(function () use ($id) {

                if ($this->deleteTuplesOneTable("revue", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression revue");
                }

                if ($this->deleteTuplesOneTable("document", ["id" => $id]) === null) {
                    throw new Exception("Erreur suppression document");
                }

            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function isOktoDeleteDocument(string $table, string $id) : bool
    {
        // vérifie exemplaires
        $exemplairesCount = $this->conn->queryBDD(
            "SELECT COUNT(*) as nb FROM exemplaire WHERE id = :id",
            ["id"=>$id]
        );

        if ($exemplairesCount[0]["nb"] != 0) {
            return false;
        }

        // vérifie commandes
        if ($table == "livre" || $table == "dvd") {
            $commandes = $this->conn->queryBDD(
                "SELECT COUNT(*) as nb FROM commandedocument WHERE idLivreDvd = :id",
                ["id"=>$id]
            );

            if ($commandes[0]["nb"] != 0) {
                return false;
            }
        }

        // vérifie abonnements (revues uniquement)
        if ($table == "revue") {
            $abonnements = $this->conn->queryBDD(
                "SELECT COUNT(*) as nb FROM abonnement WHERE idRevue = :id",
                ["id"=>$id]
            );

            if ($abonnements[0]["nb"] != 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne la liste des commandes de documents selon leur type (livre ou DVD),
     * avec les informations de commande, de suivi et du document associé.
     * @param string $type "livre" ou "dvd"
     * @return array liste des commandes trouvées (tableau vide si type invalide)
     */
    private function selectCommandesDocument(?string $typemedia = null): array
    {
        $where = "";

        if ($typemedia === "livre") {
            $where = "WHERE ld.id LIKE '0%'";
        } elseif ($typemedia === "dvd") {
            $where = "WHERE ld.id LIKE '2%'";
        }

        $requete = "
        SELECT
            cd.id,
            c.dateCommande,
            c.montant,
            cd.idLivreDvd,
            cd.nbExemplaire,
            s.idSuivi,
            s.libelleEtat

        FROM commandedocument cd
        JOIN commande c ON cd.id = c.id
        JOIN suivi s ON cd.idSuivi = s.idSuivi
        JOIN livres_dvd ld ON cd.idLivreDvd = ld.id
        $where
        ORDER BY c.dateCommande DESC
    ";

        return $this->conn->queryBDD($requete);
    }

    /**
     * Insère une commande de document (transaction atomique)
     *
     * Insère successivement :
     * - commande
     * - commandedocument
     *
     * rollback automatique si erreur
     *
     * @param array $champs
     * @return int 1 si succès, 0 sinon
     */
    private function insertCommandeDocument(array $champs): ?int
    {
        if ($this->isChampsObligatoiresAbsents(["dateCommande", "montant", "nbExemplaire", "idLivreDvd", "idSuivi"], $champs)) {
            return null;
        }

        try {
            $this->conn->transaction(function () use ($champs) {

                $requete = "SELECT id FROM commande ORDER BY id DESC LIMIT 1 FOR UPDATE;";
                $result = $this->conn->queryBDD($requete);

                $dernierId = empty($result) ? 0 : (int)$result[0]["id"];
                $nouvelId = str_pad($dernierId + 1, 5, "0", STR_PAD_LEFT);

                if (!$this->insertOneTupleOneTable("commande", [
                    "id" => $nouvelId,
                    "dateCommande" => $champs["dateCommande"],
                    "montant" => $champs["montant"]
                ])) {
                    throw new Exception("Erreur insertion commande");
                }

                if (!$this->insertOneTupleOneTable("commandedocument", [
                    "id" => $nouvelId,
                    "nbExemplaire" => $champs["nbExemplaire"],
                    "idLivreDvd" => $champs["idLivreDvd"],
                    "idSuivi" => $champs["idSuivi"]
                ])) {
                    throw new Exception("Erreur insertion commandedocument");
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    /**
     * Met à jour une commande de document
     *
     * Met à jour les tables :
     * - commande
     * - commandedocument
     *
     * Utilise array_intersect_key (filtre par clé de dictionnaire) afin de
     * séparer ce qui est pour commande de ce qui est pour commandeDocument
     *
     * @param string $id identifiant de la commande
     * @param array $champs champs à modifier
     * @return int nombre de tuples modifiés
     */
    private function updateCommandeDocument(string $id, array $champs): ?int
    {
        try {
            $this->conn->transaction(function () use ($id, $champs) {

                // champs table commande
                $ChampsPourCommande = array_intersect_key($champs, array_flip([
                    "dateCommande",
                    "montant"
                ]));

                if (!empty($ChampsPourCommande)) {
                    if ($this->updateOneTupleOneTable("commande", $id, $ChampsPourCommande) === null) {
                        throw new Exception("Erreur update commande");
                    }
                }

                // champs table commandedocument
                $ChampsPourCommandeDocument = array_intersect_key($champs, array_flip([
                    "nbExemplaire",
                    "idLivreDvd",
                    "idSuivi"
                ]));

                if (!empty($ChampsPourCommandeDocument)) {
                    if ($this->updateOneTupleOneTable("commandedocument", $id, $ChampsPourCommandeDocument) === null) {
                        throw new Exception("Erreur update commandedocument");
                    }
                }
            });
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Supprime une commande de document
     *
     * Supprime la ligne dans commande.
     * La suppression dans commandedocument est gérée par trigger SQL.
     *
     * @param string $id identifiant de la commande
     * @return int nombre de tuples supprimés
     */
    private function deleteCommandeDocument(string $id): ?int
    {
        try {
            return $this->deleteTuplesOneTable("commande", [
                "id" => $id
            ]);
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
}

