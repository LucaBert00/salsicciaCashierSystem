-- 0002_login_argon2id.sql — colonna PASSWORD a 255 char per Argon2id (T12).
--
-- La baseline 0001 definisce `login`.`PASSWORD` varchar(50): basta per
-- MySQL PASSWORD() ('*'+40 hex = 41 char) ma tronca Argon2id (~97 char).
-- Eseguire PRIMA del deploy di reserved/login.php con rehash trasparente,
-- altrimenti l'UPDATE di migrazione tronca i nuovi hash.
-- Rollback: ALTER TABLE `login` MODIFY `PASSWORD` varchar(50) NOT NULL DEFAULT '';
-- (solo se nessuna riga supera 50 char, cioe' prima di qualsiasi login migrato).

ALTER TABLE `login` MODIFY `PASSWORD` varchar(255) NOT NULL DEFAULT '';

-- Righe ancora legacy (da migrare al primo login, senza lockout):
--   SELECT user_id FROM `login` WHERE `PASSWORD` LIKE '*%';
-- Nuovi account: INSERT con password_hash(..., PASSWORD_ARGON2ID) da PHP,
-- mai MySQL PASSWORD().
