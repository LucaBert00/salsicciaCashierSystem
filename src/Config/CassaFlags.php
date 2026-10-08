<?php

declare(strict_types=1);

namespace Salsiccia\Config;

use Salsiccia\Support\Env;
use Salsiccia\Support\Storage;

// Flag runtime della cassa: MODALITA_FIERA e info festa (F5.4 #106,
// docs/ARCHITETTURA_REVISTA.md §10 punto 18 + §6 riga 722-762 + §9 target
// + §7 stato/dipendenza → classe con metodi statici).
// Corpi verbatim da env.inc:717-763 (cassa_flags_file, cassa_leggi_fiera,
// cassa_imposta_fiera) + env.inc:765-808 (festa_file, festa_leggi,
// festa_imposta). Stessi path JSON (storage/cassa_flags.json,
// storage/festa.json), stessa forma del JSON, stessa semantica atomica
// tmp+rename con LOCK_EX e tmp per-process (getmypid),
// F6.2 #110: fallback solo a config/cassa.php inerte + JSON, mai piu
// defined() da set.inc (P4 resta fixato), stessi messaggi di log,
// stessi ritorni. Nessun consumer migrato qui:
// i globali env.inc restano intatti e i chiamanti (functionsFrontend.inc
// isFieraAttiva/impostaModalitaFiera, cassa_azione_fiera, AdminView::info,
// StatsData, PrintService, VisualizzaStore, stat_pdf) restano invariati;
// nessuno shim/alias servito (l'eliminazione dei globali è F5.5 #107).
// Metodi statici: nessun stato, autoload PSR-4 senza toccare composer.json.
// Uniche differenze di riga oltre a namespace/firma: path storage via
// Storage::path(), log via Env::log (stessa facade cassa_log, F5.1 #103)
// e dirname(__DIR__, 2) per config/cassa.php (in classe __DIR__ sarebbe
// src/Config, stessa root di env.inc).
final class CassaFlags
{
    // T17: flag fiera persistente in storage/ JSON, mai rewrite di PHP source.
    // Unico punto di verita' per MODALITA_FIERA: letto da isFieraAttiva()
    // (functionsFrontend.inc) e fieraAttiva() (public/reserved/visualizza.php),
    // scritto da impostaModalitaFiera(). Scrittura atomica tmp+rename con LOCK_EX:
    // concorrente = last-writer-wins, mai file troncato ne' sorgente toccato.
    // F6.2 #110: fallback a config/cassa.php inerte (default), mai piu set.inc.
    public static function cassaFlagsFile(): string
    {
        return Storage::path('cassa_flags.json');
    }

    public static function cassaLeggiFiera(): bool
    {
        $raw = is_readable(self::cassaFlagsFile()) ? file_get_contents(self::cassaFlagsFile()) : false;
        if (is_string($raw) && $raw !== '') {
            $j = json_decode($raw, true);
            if (is_array($j) && isset($j['modalita_fiera'])) {
                return $j['modalita_fiera'] === '1' || $j['modalita_fiera'] === 1 || $j['modalita_fiera'] === true;
            }
        }
        $cfg = dirname(__DIR__, 2) . '/config/cassa.php';
        if (is_readable($cfg)) {
            $arr = is_readable($cfg) ? include $cfg : false;
            if (is_array($arr) && isset($arr['MODALITA_FIERA'])) {
                return (string)$arr['MODALITA_FIERA'] === '1';
            }
        }
        return false;
    }

    // tmp+rename atomico, per-process tmp per concorrenza
    /**
     * @param mixed $valore
     */
    public static function cassaImpostaFiera($valore): void
    {
        $v = $valore === '1' || $valore === 1 || $valore === true ? '1' : '0';
        $f = self::cassaFlagsFile();
        $tmp = $f . '.' . getmypid() . '.tmp';
        $json = json_encode(array('modalita_fiera' => $v));
        if (!is_string($json)) {
            return;
        }
        if (file_put_contents($tmp, $json, LOCK_EX) === false) {
            Env::log('error', 'cassa_flags scrittura fallita');
            return;
        }
        if (!rename($tmp, $f)) {
            Env::log('error', 'cassa_flags rename fallita');
        }
    }

    // F0.3: info festa persistenti in storage/festa.json, mai rewrite di PHP source.
    // Unico punto di verita' per EVENT_NAME/DURATA_FESTA: letto da mostraModificaInfo()
    // (functionsFrontend.inc), PrintService, StatsData, stat_pdf; scritto da
    // festa_imposta(). Scrittura atomica tmp+rename con LOCK_EX:
    // concorrente = last-writer-wins, mai file troncato ne' sorgente toccato.
    // F6.2 #110: a festa.json assente default inerte (vuoto/1), mai piu set.inc.
    public static function festaFile(): string
    {
        return Storage::path('festa.json');
    }

    public static function festaLeggi(): array
    {
        $raw = is_readable(self::festaFile()) ? file_get_contents(self::festaFile()) : false;
        if (is_string($raw) && $raw !== '') {
            $j = json_decode($raw, true);
            if (is_array($j) && isset($j['event_name']) && isset($j['durata_festa'])) {
                return array('event_name' => (string)$j['event_name'], 'durata_festa' => (string)$j['durata_festa']);
            }
        }
        return array(
            'event_name' => '',
            'durata_festa' => '1',
        );
    }

    // tmp+rename atomico, per-process tmp per concorrenza
    /**
     * @param mixed $eventName
     * @param mixed $durataFesta
     */
    public static function festaImposta($eventName, $durataFesta): void
    {
        $e = substr(str_replace(array('"', '\\', "\r", "\n"), '', trim((string)$eventName)), 0, 32);
        $d = (string)max(1, (int)$durataFesta);
        $f = self::festaFile();
        $tmp = $f . '.' . getmypid() . '.tmp';
        $json = json_encode(array('event_name' => $e, 'durata_festa' => $d));
        if (!is_string($json)) {
            return;
        }
        if (file_put_contents($tmp, $json, LOCK_EX) === false) {
            Env::log('error', 'festa scrittura fallita');
            return;
        }
        if (!rename($tmp, $f)) {
            Env::log('error', 'festa rename fallita');
        }
    }
}
