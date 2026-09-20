<?php
// reserved/auth_password.inc — verifica password backoffice (T12).
//
// Nuovi hash: password_hash(..., PASSWORD_ARGON2ID) (~97 char).
// Migrazione senza lockout: le righe legacy MySQL PASSWORD() ('*' + 40 hex,
// double-SHA1) restano valide al primo login e vengono ri-hashate ad Argon2id
// in modo trasparente (vedi reserved/login.php). Righe legacy residue:
//   SELECT user_id FROM `login` WHERE `PASSWORD` LIKE '*%';
// A migrazione completa (zero righe '*...') rimuovere il ramo legacy
// (salsiccia_password_is_legacy + salsiccia_password_verify_legacy).

function salsiccia_password_hash($pwd)
{
    return password_hash($pwd, PASSWORD_ARGON2ID);
}

function salsiccia_password_is_legacy($stored)
{
    return (bool)preg_match('/^\*[0-9A-F]{40}$/', $stored);
}

// Emulazione PHP di MySQL PASSWORD() = CONCAT('*', UPPER(SHA1(UNHEX(SHA1(?))))),
// solo per la migrazione delle righe legacy. Non usare per nuovi hash.
function salsiccia_password_verify_legacy($pwd, $stored)
{
    $calc = '*' . strtoupper(sha1(pack('H*', sha1($pwd))));
    return hash_equals($stored, $calc);
}

function salsiccia_password_verify($pwd, $stored)
{
    if ($stored === '' || $stored === null)
    {
        return false;
    }
    if (salsiccia_password_is_legacy($stored))
    {
        return salsiccia_password_verify_legacy($pwd, $stored);
    }
    return password_verify($pwd, $stored);
}

// Vero per righe legacy (da ri-hashare) o Argon2id con parametri cambiati.
function salsiccia_password_needs_rehash($stored)
{
    if (salsiccia_password_is_legacy($stored))
    {
        return true;
    }
    return password_needs_rehash($stored, PASSWORD_ARGON2ID);
}
