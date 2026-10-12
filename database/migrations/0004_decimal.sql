-- 0004_decimal.sql — denaro da float a DECIMAL esatto (REL-03 #130).
--
-- Cosa: `prodotti.prezzo`, `ordini.totale`, `righe_ordini.totale` -> DECIMAL(10,2);
--   `prodotti.iva` -> DECIMAL(4,2) (e un'aliquota: 0.04/0.10/0.22, mai un prezzo).
-- Perche: il denaro era float IEEE-754 end-to-end (0001:42,53,54,79; 0003:40-47
--   solo motore) fino a Fiscale::buildXml:60 ($cent da binary float). Con DECIMAL
--   il DB e esatto per definizione; l'aritmetica passa a centesimi interi in
--   OrderService::calcolaTotali e Fiscale::buildXml, senza BCMath (zero estensioni,
--   una sola dipendenza runtime setasign/fpdf). Display invariato
--   (number_format(...,2,',','.')), XML fiscale byte-identico (PureBuildersTest).
-- Ordine: applicare DOPO 0001_schema.sql + 0002_login_argon2id.sql +
--   0003_innodb.sql, su DB vuoto o su clone verificato dal gate. Nessuna FK sui
--   soldi (le FK sono sugli id), quindi nessun riordino indici; gli ALTER sono
--   indipendenti e idempotenti nel tipo (re-run = no-op se gia DECIMAL).
--   Verificato su clone verify130_gate: DESCRIBE 10,2/10,2/10,2 + 4,2; mysqli
--   rende stringhe ("8.20"), mai float.
-- Rollback: restore del dump pre-migrazione (REL-05: qui il rollback e il dump,
--   non un re-import). 0001 resta baseline MyISAM, 0003 resta motore+FK.
--   Reverse documentativo (solo se il dump manca):
--   MODIFY `prezzo`/`totale` float, MODIFY `iva` float.

ALTER TABLE `prodotti` MODIFY `prezzo` DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE `prodotti` MODIFY `iva` DECIMAL(4,2) NOT NULL;
ALTER TABLE `ordini` MODIFY `totale` DECIMAL(10,2) DEFAULT NULL;
ALTER TABLE `righe_ordini` MODIFY `totale` DECIMAL(10,2) DEFAULT NULL;
