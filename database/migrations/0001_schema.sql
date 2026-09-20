-- 0001_schema.sql — baseline dello schema live di fiera (T02, rif. T21 per l'esecuzione).
--
-- Sorgente: DB `salsiccia` sul laptop di fiera (XAMPP locale, host=localhost).
-- Catturato: 2026-09-20 via mysqldump --no-data (server 10.4.32-MariaDB).
-- Contenuto: SOLO struttura (nessun dato). Fedele al live: tutti i tavoli sono
-- MyISAM, charset utf8mb4, collation utf8mb4_general_ci, zero FOREIGN KEY.
-- AUTO_INCREMENT=* rimossi dal dump (dato, non schema).
-- Decisione MyISAM->InnoDB: vedi docs/DATABASE_SCHEMA.md (T21 la esegue).
-- Rollback: questo file E' il rollback (re-import = ritorno a MyISAM baseline).

CREATE TABLE `categorie` (
  `id_categoria` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descrizione_cat` varchar(30) DEFAULT NULL,
  `testo_bottone` varchar(20) NOT NULL,
  `colore` varchar(7) NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contatori` (
  `id_contatore` smallint(6) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `limite_qta` smallint(6) NOT NULL,
  `controllo_periodo` char(1) NOT NULL,
  `data_da` datetime NOT NULL,
  `data_a` datetime NOT NULL,
  `attivo` char(1) NOT NULL,
  `attivo_app` char(1) NOT NULL,
  PRIMARY KEY (`id_contatore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `login` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL DEFAULT '',
  `PASSWORD` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ordini` (
  `id_ordine` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_ora` datetime DEFAULT NULL,
  `tipo` varchar(3) DEFAULT NULL,
  `totale` float DEFAULT NULL,
  `n_pezzi` int(10) unsigned DEFAULT NULL,
  `id_cassa` tinyint(3) unsigned DEFAULT NULL,
  `chiuso` char(1) DEFAULT NULL,
  `num_biglietti` int(11) NOT NULL,
  PRIMARY KEY (`id_ordine`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `prodotti` (
  `id_prodotto` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descrizione_prod` varchar(100) DEFAULT NULL,
  `prezzo` float DEFAULT NULL,
  `iva` float NOT NULL,
  `testo_biglietto` varchar(100) DEFAULT NULL,
  `olpp` char(1) NOT NULL DEFAULT 'F',
  `barcode` varchar(20) NOT NULL,
  PRIMARY KEY (`id_prodotto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `prodotti_categorie` (
  `id_prodotto` int(11) NOT NULL AUTO_INCREMENT,
  `id_categoria` int(10) unsigned NOT NULL,
  `posizione` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id_prodotto`,`id_categoria`,`posizione`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `prodotti_contatori` (
  `id_contatore` smallint(6) NOT NULL,
  `id_prodotto` smallint(6) NOT NULL,
  `quantita` smallint(6) NOT NULL,
  PRIMARY KEY (`id_contatore`,`id_prodotto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `righe_ordini` (
  `id_ordine` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_prodotto` int(10) unsigned NOT NULL,
  `quantita` int(10) unsigned DEFAULT NULL,
  `totale` float DEFAULT NULL,
  PRIMARY KEY (`id_ordine`,`id_prodotto`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
