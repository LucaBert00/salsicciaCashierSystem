<?php

declare(strict_types=1);

namespace Salsiccia\Catalog;

// Unica casa testabile delle letture catalogo (T19, da
// mostraNavCategorie/mostraTitolo/mostraTabellaProdotti + defaultCat +
// tinta categoria in public/index.php). DB iniettato via costruttore, mai
// preso dallo stato globale (come OrderService in src/Cassa/OrderService.php
// e il sender iniettabile di Fiscale::ritentaCoda() in src/Fiscale/Fiscale.php).
// Query verbatim dai call-site (prepared via db_select, statiche via
// mysql_query_safe per T09); ritorna array puri, mai output HTML.
if (!function_exists('db_select')) {
    require_once dirname(__DIR__, 2) . '/funzioni.inc';
}

final class CatalogRepo
{
    /** @var mixed $db mysqli reale o doppio di test con la stessa superficie */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // mostraNavCategorie(): tutte le categorie in ordine id.
    public function allCategorie(): array
    {
        $ris = mysql_query_safe($this->db, "SELECT * FROM categorie ORDER BY id_categoria");
        $righe = array();
        while ($ris && ($riga = mysqli_fetch_array($ris))) {
            $righe[] = $riga;
        }
        return $righe;
    }

    // Riga categoria per id (ramo vuoto di mostraTabellaProdotti + tinta
    // categoria in public/index.php: il chiamante legge ['colore']).
    public function categoria(int $id): ?array
    {
        $ris = db_select($this->db, "SELECT * FROM categorie WHERE id_categoria = ?", 'i', array($id));
        if ($ris && ($riga = mysqli_fetch_array($ris))) {
            return $riga;
        }
        return null;
    }

    // mostraTitolo(): descrizione o null (il chiamante stampa 'SAKE').
    public function descrizioneCategoria(int $id): ?string
    {
        $ris = db_select($this->db, "SELECT descrizione_cat FROM categorie WHERE id_categoria = ?", 'i', array($id));
        if ($ris && mysqli_num_rows($ris) > 0 && ($riga = mysqli_fetch_array($ris))) {
            return (string)$riga['descrizione_cat'];
        }
        return null;
    }

    // mostraTabellaProdotti(): righe prodotto in ordine di posizione.
    public function prodottiByCategoria(int $id): array
    {
        $ris = db_select($this->db, "SELECT * FROM prodotti, categorie, prodotti_categorie WHERE prodotti_categorie.id_prodotto = prodotti.id_prodotto AND prodotti_categorie.id_categoria = categorie.id_categoria AND categorie.id_categoria = ? ORDER BY posizione", 'i', array($id));
        $righe = array();
        while ($ris && ($riga = mysqli_fetch_array($ris))) {
            $righe[] = $riga;
        }
        return $righe;
    }

    // defaultCat(): id per nome in CAPS o null.
    public function trovaIdPerNome(string $nomeUpper): ?int
    {
        $ris = db_select($this->db, "SELECT id_categoria FROM categorie WHERE UPPER(descrizione_cat) = ? LIMIT 1", 's', array($nomeUpper));
        if ($ris && ($riga = mysqli_fetch_array($ris)) && (int)$riga['id_categoria'] > 0) {
            return (int)$riga['id_categoria'];
        }
        return null;
    }

    // defaultCat(): MIN(id) o null.
    public function minCategoriaId(): ?int
    {
        $ris = mysql_query_safe($this->db, "SELECT MIN(id_categoria) AS m FROM categorie");
        if ($ris && ($riga = mysqli_fetch_array($ris)) && (int)$riga['m'] > 0) {
            return (int)$riga['m'];
        }
        return null;
    }
}
