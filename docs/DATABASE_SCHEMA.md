# Database Schema (live di fiera, T02)

Sorgente: DB `salsiccia` sul laptop di fiera (XAMPP locale, host=localhost),
dump `--no-data` del 2026-09-20, server 10.4.32-MariaDB.
Baseline versionata: `database/migrations/0001_schema.sql` (solo struttura,
fedele al live). Nessun dato nel repo. Verifica FK:
`INFORMATION_SCHEMA.KEY_COLUMN_USAGE` con `REFERENCED_TABLE_NAME IS NOT NULL`
= 0 righe (zero FK).

## Engine / charset / collation per tabella

Default del DB: `utf8mb4` / `utf8mb4_general_ci`.

| Tabella | Engine | Charset | Collation | Righe al dump |
|---|---|---|---|---|
| `categorie` | MyISAM | utf8mb4 | utf8mb4_general_ci | 3 |
| `contatori` | MyISAM | utf8mb4 | utf8mb4_general_ci | 0 |
| `login` | MyISAM | utf8mb4 | utf8mb4_general_ci | 1 |
| `ordini` | MyISAM | utf8mb4 | utf8mb4_general_ci | 212 |
| `prodotti` | MyISAM | utf8mb4 | utf8mb4_general_ci | 210 |
| `prodotti_categorie` | MyISAM | utf8mb4 | utf8mb4_general_ci | 22 |
| `prodotti_contatori` | MyISAM | utf8mb4 | utf8mb4_general_ci | 0 |
| `righe_ordini` | MyISAM | utf8mb4 | utf8mb4_general_ci | 342 |

Dettaglio colonne: vedi `0001_schema.sql` (PK: `categorie(id_categoria)`,
`contatori(id_contatore)`, `login(id)`, `ordini(id_ordine)`,
`prodotti(id_prodotto)`, `prodotti_categorie(id_prodotto,id_categoria,posizione)`,
`prodotti_contatori(id_contatore,id_prodotto)`,
`righe_ordini(id_ordine,id_prodotto)`; nessun indice secondario, nessuna FK).

## Relazioni logiche (oggi solo applicative, MyISAM = zero FK)

- `righe_ordini.id_ordine` -> `ordini.id_ordine`
- `righe_ordini.id_prodotto` -> `prodotti.id_prodotto`
- `prodotti_categorie.id_prodotto` -> `prodotti.id_prodotto`
- `prodotti_categorie.id_categoria` -> `categorie.id_categoria`
- `prodotti_contatori.id_prodotto` -> `prodotti.id_prodotto`
- `prodotti_contatori.id_contatore` -> `contatori.id_contatore`

## Decisione: migrare a InnoDB (esegue T21)

Si migra tutto a InnoDB con FK dichiarate e charset/collation invariati
(`utf8mb4`/`utf8mb4_general_ci` su MariaDB 10.4 di fiera): il percorso cassa
scrive ordini+righe in più statement senza transazioni (`functionsFrontend.inc:227-243`,
`funzioni.inc:59-87`) e MyISAM non dà né atomicità né crash-safety né lock a riga,
quindi un guasto a metà flusso o due tocchi concorrenti lasciano ordini parziali proprio
sotto il carico di fiera; le FK InnoDB sostituiscono le cascade applicative elencate sotto
e abilitano le transazioni del Phase 1, al costo di una sola migrazione `ALTER TABLE ... ENGINE=InnoDB`
+ `ADD CONSTRAINT` da provare su copia e ribaltare col re-import di `0001_schema.sql`.

## Cascade applicative che le FK sostituiranno (T21)

1. `functionsFrontend.inc:199-211` (`action=r`, annulla ordine): `DELETE FROM ordini ...`
   + `DELETE FROM righe_ordini WHERE id_ordine = ...` — cascade manuale
   `ordini` -> `righe_ordini` (sostituibile con `ON DELETE CASCADE`).
2. `reserved/visualizza.php:290-316` (elimina prodotto): `COUNT(*)` su
   `prodotti_categorie` + `prodotti_contatori` + `righe_ordini`, elimina bloccata se
   referenziato — `RESTRICT` applicativo (sostituibile con FK senza cascade + messaggio).
3. `reserved/visualizza.php:323-344` (elimina categoria): `COUNT(*)` su
   `prodotti_categorie`, elimina bloccata se referenziata — `RESTRICT` applicativo.
4. `reserved/visualizza.php:351-369` (elimina contatore): `DELETE FROM prodotti_contatori
   WHERE id_contatore = ?` + `DELETE FROM contatori` — cascade applicativa
   `contatori` -> `prodotti_contatori` (sostituibile con `ON DELETE CASCADE`).
5. `reserved/visualizza.php:376-396` (elimina posizione): `DELETE FROM prodotti_categorie`
   su chiave tripla — cancellazione riga figlia diretta.
6. `reserved/visualizza.php:399-419` (elimina regola venduti): `DELETE FROM prodotti_contatori`
   su coppia — cancellazione riga figlia diretta.

## Migrations

- `database/migrations/0001_schema.sql` — baseline live (MyISAM, fedele al dump).
- T21: `0002_innodb.sql` (`ENGINE=InnoDB` + FK da § Relazioni con le regole sopra) —
  da provare su copia del DB di fiera, rollback = re-import di `0001_schema.sql`.
