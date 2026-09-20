-- 0003_innodb.sql — esegue la decisione T02 (MyISAM->InnoDB esplicita + FK, T21).
--
-- Decisione T02 (docs/DATABASE_SCHEMA.md § Decisione): tutto a InnoDB con FK
-- dichiarate, charset/collation invariati (utf8mb4/utf8mb4_general_ci su
-- MariaDB 10.4 di fiera). MyISAM non dà né atomicità né crash-safety né lock
-- a riga sul percorso cassa multi-statement (functionsFrontend.inc:227-243,
-- funzioni.inc:59-87); le FK sostituiscono le cascade applicative e abilitano
-- le transazioni del Phase 1. Baseline fedele al live resta 0001 (MyISAM).
-- Ordine: applicare DOPO 0001_schema.sql + 0002_login_argon2id.sql su DB vuoto.
-- Regole FK (da docs/DATABASE_SCHEMA.md § Cascade applicative, oggi in
-- src/Cassa/OrderService.php annullaOrdine + src/Backoffice/Tabs/*Tab.php
-- elimina, verbatim da public/reserved/visualizza.php:260-387 pre-T20):
--   righe_ordini.id_ordine -> ordini.id_ordine ON DELETE CASCADE
--     (OrderService annullaOrdine cancella il padre prima del figlio:
--     con RESTRICT il DELETE padre fallirebbe, la CASCADE lo mantiene verde).
--   prodotti_contatori.id_contatore -> contatori.id_contatore ON DELETE CASCADE
--     (ContatoriTab elimina a cascata le regole del venduto).
--   Le altre 4 FK sono ON DELETE RESTRICT (blocco come i COUNT(*) applicativi):
--     righe_ordini.id_prodotto -> prodotti, prodotti_categorie.id_prodotto
--     -> prodotti, prodotti_categorie.id_categoria -> categorie,
--     prodotti_contatori.id_prodotto -> prodotti.
-- Allineamenti tipo necessari alle FK (live incompatibili per segno/taglia):
--   prodotti_categorie.id_prodotto int(11) signed -> int(10) unsigned
--     (come prodotti.id_prodotto; valori live <= 2557, nessun troncamento).
--   prodotti_contatori.id_prodotto smallint(6) -> int(10) unsigned
--     (come prodotti.id_prodotto; tabella live vuota, zero righe toccate).
-- Indici secondari aggiunti (InnoDB li esige sul lato figlio non-prefisso):
--   prodotti_categorie(id_categoria), prodotti_contatori(id_prodotto),
--   righe_ordini(id_prodotto).
-- Rollback: DROP FOREIGN KEY x6 + DROP INDEX x3 + ENGINE=MyISAM x8 +
--   MODIFY indietro x2 (vedi sotto); reset totale = re-import 0001_schema.sql
--   + riapplica 0002_login_argon2id.sql (0001 da solo ripristina anche
--   login.PASSWORD a varchar(50)).

-- 1. Allinea i tipi figlio al padre (serve prima delle FK).
ALTER TABLE `prodotti_categorie` MODIFY `id_prodotto` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `prodotti_contatori` MODIFY `id_prodotto` int(10) unsigned NOT NULL;

-- 2. Motore: tutto a InnoDB, charset/collation invariati.
ALTER TABLE `categorie` ENGINE=InnoDB;
ALTER TABLE `contatori` ENGINE=InnoDB;
ALTER TABLE `login` ENGINE=InnoDB;
ALTER TABLE `ordini` ENGINE=InnoDB;
ALTER TABLE `prodotti` ENGINE=InnoDB;
ALTER TABLE `prodotti_categorie` ENGINE=InnoDB;
ALTER TABLE `prodotti_contatori` ENGINE=InnoDB;
ALTER TABLE `righe_ordini` ENGINE=InnoDB;

-- 3. Indici figlio mancanti (prima colonna di PK e' gia' indicizzata).
ALTER TABLE `prodotti_categorie` ADD INDEX `idx_pc_categoria` (`id_categoria`);
ALTER TABLE `prodotti_contatori` ADD INDEX `idx_pcnt_prodotto` (`id_prodotto`);
ALTER TABLE `righe_ordini` ADD INDEX `idx_righe_prodotto` (`id_prodotto`);

-- 4. FK con le regole della decisione T02.
ALTER TABLE `righe_ordini`
  ADD CONSTRAINT `fk_righe_ordini_ordini` FOREIGN KEY (`id_ordine`)
    REFERENCES `ordini` (`id_ordine`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_righe_ordini_prodotti` FOREIGN KEY (`id_prodotto`)
    REFERENCES `prodotti` (`id_prodotto`) ON DELETE RESTRICT;
ALTER TABLE `prodotti_categorie`
  ADD CONSTRAINT `fk_pc_prodotti` FOREIGN KEY (`id_prodotto`)
    REFERENCES `prodotti` (`id_prodotto`) ON DELETE RESTRICT,
  ADD CONSTRAINT `fk_pc_categorie` FOREIGN KEY (`id_categoria`)
    REFERENCES `categorie` (`id_categoria`) ON DELETE RESTRICT;
ALTER TABLE `prodotti_contatori`
  ADD CONSTRAINT `fk_pcnt_contatori` FOREIGN KEY (`id_contatore`)
    REFERENCES `contatori` (`id_contatore`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pcnt_prodotti` FOREIGN KEY (`id_prodotto`)
    REFERENCES `prodotti` (`id_prodotto`) ON DELETE RESTRICT;

-- Rollback 0003 (solo questo file, 0001+0002 restano):
--   ALTER TABLE `righe_ordini` DROP FOREIGN KEY `fk_righe_ordini_ordini`;
--   ALTER TABLE `righe_ordini` DROP FOREIGN KEY `fk_righe_ordini_prodotti`;
--   ALTER TABLE `prodotti_categorie` DROP FOREIGN KEY `fk_pc_prodotti`;
--   ALTER TABLE `prodotti_categorie` DROP FOREIGN KEY `fk_pc_categorie`;
--   ALTER TABLE `prodotti_contatori` DROP FOREIGN KEY `fk_pcnt_contatori`;
--   ALTER TABLE `prodotti_contatori` DROP FOREIGN KEY `fk_pcnt_prodotti`;
--   ALTER TABLE `prodotti_categorie` DROP INDEX `idx_pc_categoria`;
--   ALTER TABLE `prodotti_contatori` DROP INDEX `idx_pcnt_prodotto`;
--   ALTER TABLE `righe_ordini` DROP INDEX `idx_righe_prodotto`;
--   ALTER TABLE `categorie` ENGINE=MyISAM; ALTER TABLE `contatori` ENGINE=MyISAM;
--   ALTER TABLE `login` ENGINE=MyISAM; ALTER TABLE `ordini` ENGINE=MyISAM;
--   ALTER TABLE `prodotti` ENGINE=MyISAM;
--   ALTER TABLE `prodotti_categorie` ENGINE=MyISAM;
--   ALTER TABLE `prodotti_contatori` ENGINE=MyISAM;
--   ALTER TABLE `righe_ordini` ENGINE=MyISAM;
--   ALTER TABLE `prodotti_categorie` MODIFY `id_prodotto` int(11) NOT NULL AUTO_INCREMENT;
--   ALTER TABLE `prodotti_contatori` MODIFY `id_prodotto` smallint(6) NOT NULL;
