<?php
declare(strict_types=1);
// T31: file canonico (non shim) — successore esistente config/cassa.php + flag in storage/ (§7, T17); rimozione solo a migrazione completa, rivalutare entro 2026-12-31.
require_once __DIR__ . '/env.inc';

define("MENU_ENABLE", 0);

# 19 = Fruttati Invecchiati in DB; se manca la cassa mostra SAKE (fallback in mostraTitolo()).
define("DEFAULT_CAT", 19);
define("DEBUG", 0);
define("ONLY_ONE_CATEGORY",0);

#Costante per l'abilitazione della grnerazione del report per la cucina
define("REPORT_CUCINA", "0");
#Modalita' fiera (issue): "1" = barcode visibile/obbligatorio in prodotti-nuovo, "0" = nascosto con default '-'
define("MODALITA_FIERA", "0");
#Numero di righe che conterrà ogni foglio di report
define("REPORT_CUCINA_ROW", "10");

#MAX 32 Caratteri. Costante deploy (decisione #55): si cambia prima della fiera, niente UI di rewrite.
define("EVENT_NAME",  "BAGNASCO - Proloco Casargo 06/08/25");

//define("EVENT_NAME",  "KAPPA Festival il Giappone a Ferrara");
//define("EVENT_NAME",  " La Busecca dell'Asilo - 20/10/2018 ");
//define("EVENT_NAME", "Premana Rivive l'Antico 2018 - 13-14 Ott. Cassa 1");
#MAX 44 Caratteri
define("CREDITS", "T-System SALSICCIA - Datrik Solutions");

#Costante per la stampa di un'etichetta finale con il totale dell'ordine.
#Riservata, nessun effetto attuale: il ramo singoli non emette mai etichetta
#totale (ogni etichetta ha solo il prodotto) e il ramo continuo chiude sempre
#con la riga TOTALE integrata nello scontrino. Tenuta a "0" = disattiva.
define("TOTAL_LABEL", "0");
#Default storico una-etichetta-per-pezzo. Effettivo oggi = colonna prodotti.olpp
#per prodotto ("T" o quantita 1 => una copia per pezzo, v. get_product_label()):
#la costante non e' letta dal codice, tenuta per compatibilita'.
define("ONE_LABEL_PER_PRODUCT", "1");

#Costante per la stampa dell'id ordine su ogni biglietto.
#"1" = prefisso "N.<id_ordine>" su ogni etichetta/riga, "0" = nascosto.
#Letta in entrambi i rami (singoli e continuo).
define("PRINT_ORDER_ID", "0");
define("LABELS_FILE", salsiccia_storage_path("label"));
define("COMMAND_FILE", salsiccia_storage_dir() . "/cmd/cmd");

#Stampante etichette: default ZD230 = RETE+ZPL (unica combo RETE supportata),
#vedi gate stampa_permessa in PrintService::generaFileStampa()
#F4.4 #54: boot da storage/stampante_selezione.json via salsiccia_storage_path()
#(printer_leggi_selezione() in env.inc, valida contro printer_gate_allowed() F4.1).
#Precedenza env > json > default, come CONTINUOUS_LABEL (env > gate):
#SALSICCIA_PRINTER_IP vince sullo state-file, lo state-file vince sul default ZD230.
$__sel = function_exists('printer_leggi_selezione') ? printer_leggi_selezione() : null;
$__env_ip = trim((string)getenv('SALSICCIA_PRINTER_IP'));
$__sel_ip = (is_array($__sel) && isset($__sel['ip'])) ? trim((string)$__sel['ip']) : '';
$__printer_ip = $__env_ip !== '' ? $__env_ip : $__sel_ip;
# T10 fail-closed: nessun fallback. Recovery: copia .env.example in .env.
if ($__printer_ip === '')
{
    http_response_code(500);
    die('Configurazione mancante: SALSICCIA_PRINTER_IP non impostata. Copia .env.example in .env e compila i valori.');
}
#Decisione stagisti 5fe9f0d: IP malformata -> fallback selezione invece di die (vuota resta 500 sopra).
if (filter_var($__printer_ip, FILTER_VALIDATE_IP) === false)
{
    if ($__sel_ip !== '' && filter_var($__sel_ip, FILTER_VALIDATE_IP) !== false)
        $__printer_ip = $__sel_ip;
    else
        $__sel = null;
}
if (is_array($__sel))
{
    define("PRINTER_NAME", $__sel['name']);
    define("PRINTER_CONNECTION", $__sel['connection']);
    define("PRINTER_LANGUAGE", $__sel['language']);
}
else
{
    define("PRINTER_NAME", "ZD230");
    define("PRINTER_CONNECTION", "RETE");
    define("PRINTER_LANGUAGE", "ZPL");
}
define("PRINTER_IP", $__printer_ip);

#Carta continua (scontrino unico) vs biglietti singoli: default dal gate unico
#printer_is_continuous() in env.inc (stessa tabella di stampa_permessa in
#PrintService::generaFileStampa(), nessun valore hardcoded). Default: Zebra_Multi in
#DIRETTA (ZPL o EPL) e Zebra_Net (RETE o DIRETTA, ZPL) fanno scontrino unico;
#Zebra_EPL_1/2 fanno biglietti singoli. Combo non permessa => stampa bloccata a monte.
#Override senza toccare il codice: se SALSICCIA_CONTINUOUS_LABEL e' impostata a "1"/"0"
#in .env (ignorato da git) o env di sistema, vince sul gate. "1"=unico, "0"=singoli.
$__continuous_env = getenv('SALSICCIA_CONTINUOUS_LABEL');
if ($__continuous_env !== false && trim((string)$__continuous_env) !== '')
    define("CONTINUOUS_LABEL", ((int)$__continuous_env) ? 1 : 0);
else
    define("CONTINUOUS_LABEL", (int)printer_is_continuous(PRINTER_NAME, PRINTER_CONNECTION, PRINTER_LANGUAGE));
#Risoluzione testina: 8 dots/mm (203dpi), serve per calcolare ^LL in dots
define("ZPL_DOTS_PER_MM", 8);

#Durata Festa in Giorni. Costante deploy come EVENT_NAME (decisione #55).
define("DURATA_FESTA", "1");
#Ora cambio data: la imposta l'utente via .env (SALSICCIA_ORA_CAMBIO=6), fallback 5.
#Range 0..23 validato in StatsData::oraCambio() (src/Stats/StatsData.php), come CONTINUOUS_LABEL.
$__ora_env = getenv('SALSICCIA_ORA_CAMBIO');
if ($__ora_env !== false && trim((string)$__ora_env) !== '')
    define("ORA_CAMBIO_DATA", (string)$__ora_env);
else
    define("ORA_CAMBIO_DATA", "5");

#Max BUT_X_COL 7
define("BOT_X_COL", 4);
#Max BUT_X_ROW 7
if(ONLY_ONE_CATEGORY)
    define("BOT_X_ROW", 7);
else
    define("BOT_X_ROW", 6);
#TD
#define("COL_WIDTH", 120);
#define("COL_HEIGHT",150);
#Bottone
define("BOT_WIDTH", 205);
define("BOT_HEIGHT",120);

define("TIPO_ORDINE", "nor");
define("REMOTE_CLIENT_IP", "192.168.0.30");
$remoteIP = $_SERVER['REMOTE_ADDR'];

if($remoteIP == REMOTE_CLIENT_IP)
    define("ID_CASSA", 100);
else
    define("ID_CASSA", 1);
$__admin_hash = trim((string)getenv('SALSICCIA_ADMIN_PWD_HASH'));
if ($__admin_hash === '' || password_get_info($__admin_hash)['algo'] === null)
{
    http_response_code(500);
    die('Configurazione mancante: SALSICCIA_ADMIN_PWD_HASH non impostata o non valida. Genera con php -r "echo password_hash(\'CODICE\', PASSWORD_ARGON2ID), PHP_EOL;" e incolla l\'hash in .env.');
}
define("PWD_HASH", $__admin_hash);
unset($__admin_hash);
define("DATA_CONFRONTO", "2019-06-16 06:00:00");

?>
