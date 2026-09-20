<?php
declare(strict_types=1);
// Template condiviso lista/form 5 tab (T20). Si aspetta le variabili del
// router public/reserved/visualizza.php ($configTabs, $tabConfig, $righe...).
// I 4 wrapper delegano a VisualizzaStore cosi' il corpo resta byte-identico.
require_once __DIR__ . '/../../src/Backoffice/VisualizzaStore.php';

use Salsiccia\Backoffice\VisualizzaStore;

function urlLista($params)
{
    return VisualizzaStore::urlLista($params);
}

function etichettaColore($codice)
{
    return VisualizzaStore::etichettaColore($codice);
}

function etichettaSiNo($flag)
{
    return VisualizzaStore::etichettaSiNo($flag);
}

function fieraAttiva()
{
    return VisualizzaStore::fieraAttiva();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tabValido ? htmlspecialchars($tabConfig['titolo']) : 'TABELLA NON TROVATA'; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<!-- Vista form quando si crea o modifica, vista lista con ricerca e paginazione altrimenti -->
<?php if ($mostraForm): ?>
    <?php
    // Etichette titolo e flag edit: nuovo forza inserimento anche se $rigaInModifica è valorizzato
    $titoliForm = array('categorie' => 'CATEGORIA', 'prodotti' => 'PRODOTTO', 'prodotti_categorie' => 'POSIZIONE', 'contatori' => 'VENDUTO', 'prodotti_contatori' => 'REGOLA');
    $inModifica = $rigaInModifica && !isset($_GET['nuovo']);
    // Action del form: conserva la chiave originale così il POST aggiorna la riga giusta
    if ($tabForm === 'prodotti_categorie' && $inModifica)
        $actionForm = 'visualizza.php?tab=prodotti_categorie&edit_p=' . (int)$rigaInModifica['id_prodotto'] . '&edit_c=' . (int)$rigaInModifica['id_categoria'] . '&edit_pos=' . (int)$rigaInModifica['posizione'];
    elseif ($tabForm === 'prodotti_contatori' && $inModifica)
        $actionForm = 'visualizza.php?tab=prodotti_contatori&edit_c=' . (int)$rigaInModifica['id_contatore'] . '&edit_p=' . (int)$rigaInModifica['id_prodotto'];
    elseif ($inModifica && in_array($tabForm, array('categorie', 'prodotti', 'contatori')))
        $actionForm = 'visualizza.php?tab=' . $tabForm . '&edit=' . (int)reset($rigaInModifica);
    else
        $actionForm = 'visualizza.php?tab=' . $tabForm . '&nuovo=1';
    ?>
    <main style="width:100%;align-items:center;text-align:center;">
        <header style="justify-content:center;"><h1><?php echo $inModifica ? 'MODIFICA ' . $titoliForm[$tabForm] . ' #' . (int)reset($rigaInModifica) : 'NUOVO ' . $titoliForm[$tabForm]; ?></h1></header>
        <section class="admin-panel" style="align-items:center;max-width:560px;">
            <?php if ($messaggioStato != ''): ?>
                <h3 class="admin-group-title"><?php echo htmlspecialchars($messaggioStato); ?></h3>
            <?php endif; ?>
            <!-- Form categoria: descrizione, testo bottone e colore -->
            <?php if ($tabForm === 'categorie'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-categoria" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="categorie">
                <input type="hidden" name="id" value="<?php echo $inModifica ? (int)$rigaInModifica['id_categoria'] : 0; ?>">
                <?php csrf_field(); ?>
                <input type="text" name="descrizione_cat" class="codice-text-field" maxlength="30" required placeholder="DESCRIZIONE" value="<?php echo htmlspecialchars($rigaInModifica['descrizione_cat'] ?? ''); ?>">
                <input type="text" name="testo_bottone" class="codice-text-field" maxlength="20" required placeholder="TESTO BOTTONE" value="<?php echo htmlspecialchars($rigaInModifica['testo_bottone'] ?? ''); ?>">
                <input type="text" name="colore" class="codice-text-field" maxlength="12" required placeholder="COLORE (01..14)" value="<?php echo htmlspecialchars($rigaInModifica['colore'] ?? ''); ?>">
                <!-- Tastiera touch (variante A da issue): details espandibile, tasti 48px, CANC slice, focus ultimo campo -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kb-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kb-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-kb-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=categorie" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-campi">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form venduto: nome, limite, flag periodo/attivo/app e intervallo date -->
            <?php elseif ($tabForm === 'contatori'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-contatore" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="contatori">
                <input type="hidden" name="id" value="<?php echo $inModifica ? (int)$rigaInModifica['id_contatore'] : 0; ?>">
                <?php csrf_field(); ?>
                <input type="text" name="nome" class="codice-text-field" maxlength="50" required placeholder="NOME" value="<?php echo htmlspecialchars($rigaInModifica['nome'] ?? ''); ?>">
                <input type="number" name="limite_qta" class="codice-text-field" step="1" min="0" placeholder="LIMITE QTA" value="<?php echo htmlspecialchars($rigaInModifica['limite_qta'] ?? ''); ?>">
                <div class="admin-btn-row" style="width:100%;">
                    <div style="flex:1;display:flex;flex-direction:column;">
                        <span class="admin-group-title">CONTROLLO PERIODO</span>
                    <select name="controllo_periodo" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['controllo_periodo'] ?? 'F') == 'T' ? ' selected' : ''; ?>>PERIODO SI</option>
                        <option value="F"<?php echo ($rigaInModifica['controllo_periodo'] ?? 'F') == 'F' ? ' selected' : ''; ?>>PERIODO NO</option>
                    </select>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;">
                        <span class="admin-group-title">ATTIVO</span>
                    <select name="attivo" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['attivo'] ?? 'T') == 'T' ? ' selected' : ''; ?>>ATTIVO SI</option>
                        <option value="F"<?php echo ($rigaInModifica['attivo'] ?? '') == 'F' ? ' selected' : ''; ?>>ATTIVO NO</option>
                    </select>
                    </div>
                    <div style="flex:1;display:flex;flex-direction:column;">
                        <span class="admin-group-title">ATTIVO APP</span>
                    <select name="attivo_app" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['attivo_app'] ?? 'T') == 'T' ? ' selected' : ''; ?>>APP SI</option>
                        <option value="F"<?php echo ($rigaInModifica['attivo_app'] ?? '') == 'F' ? ' selected' : ''; ?>>APP NO</option>
                    </select>
                    </div>
                </div>
                <span class="admin-group-title">DALLA DATA</span>
                <input type="datetime-local" name="data_da" class="codice-text-field" required value="<?php echo htmlspecialchars(isset($rigaInModifica['data_da']) ? str_replace(' ', 'T', substr($rigaInModifica['data_da'], 0, 16)) : ''); ?>">
                <span class="admin-group-title">ALLA DATA</span>
                <input type="datetime-local" name="data_a" class="codice-text-field" required value="<?php echo htmlspecialchars(isset($rigaInModifica['data_a']) ? str_replace(' ', 'T', substr($rigaInModifica['data_a'], 0, 16)) : ''); ?>">
                <!-- Tastiera touch (variante A da issue, riuso): sotto la seconda data, focus-target NOME/LIMITE (int-only su LIMITE) -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kbc-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kbc-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-kbc-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=contatori" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-contatore">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form posizione: chiavi nascoste con valori originali per la update sulla tripla -->
            <?php elseif ($tabForm === 'prodotti_categorie'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-posizione" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="prodotti_categorie">
                <input type="hidden" name="is_edit" value="<?php echo $inModifica ? '1' : '0'; ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="oid_p" value="<?php echo $inModifica ? (int)$rigaInModifica['id_prodotto'] : 0; ?>">
                <input type="hidden" name="oid_c" value="<?php echo $inModifica ? (int)$rigaInModifica['id_categoria'] : 0; ?>">
                <input type="hidden" name="oid_pos" value="<?php echo $inModifica ? (int)$rigaInModifica['posizione'] : 0; ?>">
                <select name="id_prodotto" class="codice-text-field" required>
                    <option value="0">PRODOTTO…</option>
                    <?php if ($inModifica): $prodottoTrovato = false; ?>
                        <?php foreach ($opzioniProdotti as $opzione): if ((int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) $prodottoTrovato = true; endforeach; ?>
                        <?php if (!$prodottoTrovato): ?><option value="<?php echo (int)$rigaInModifica['id_prodotto']; ?>" selected>(ORFANO #<?php echo (int)$rigaInModifica['id_prodotto']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniProdotti as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_prodotto']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['descrizione_prod'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="id_categoria" class="codice-text-field" required>
                    <option value="0">CATEGORIA…</option>
                    <?php if ($inModifica): $categoriaTrovata = false; ?>
                        <?php foreach ($opzioniCategorie as $opzione): if ((int)$opzione['id_categoria'] === (int)$rigaInModifica['id_categoria']) $categoriaTrovata = true; endforeach; ?>
                        <?php if (!$categoriaTrovata): ?><option value="<?php echo (int)$rigaInModifica['id_categoria']; ?>" selected>(ORFANA #<?php echo (int)$rigaInModifica['id_categoria']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniCategorie as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_categoria']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_categoria'] === (int)$rigaInModifica['id_categoria']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['descrizione_cat'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="posizione" id="pos-val" value="<?php echo (int)($rigaInModifica['posizione'] ?? 0); ?>">
                <!-- Griglia posizioni 4x6 (issue): occupancy per categoria, click seleziona e sincronizza hidden -->
                <table class="pos-grid" id="pos-grid">
                    <tbody>
                        <?php for ($rigaGrid = 0; $rigaGrid < 6; $rigaGrid++): ?><tr>
                            <?php for ($colGrid = 1; $colGrid <= 4; $colGrid++): $numPos = $rigaGrid * 4 + $colGrid; ?>
                            <td><button type="button" class="bottone pos-cell" data-pos="<?php echo $numPos; ?>"><?php echo sprintf('%02d', $numPos); ?></button></td>
                            <?php endfor; ?>
                        </tr><?php endfor; ?>
                    </tbody>
                </table>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=prodotti_categorie" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-posizione">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form regola venduti: chiavi nascoste con valori originali per la update sulla coppia -->
            <?php elseif ($tabForm === 'prodotti_contatori'): ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-regola" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="prodotti_contatori">
                <input type="hidden" name="is_edit" value="<?php echo $inModifica ? '1' : '0'; ?>">
                <?php csrf_field(); ?>
                <input type="hidden" name="oid_c" value="<?php echo $inModifica ? (int)$rigaInModifica['id_contatore'] : 0; ?>">
                <input type="hidden" name="oid_p" value="<?php echo $inModifica ? (int)$rigaInModifica['id_prodotto'] : 0; ?>">
                <select name="id_contatore" class="codice-text-field" required>
                    <option value="0">VENDUTO…</option>
                    <?php if ($inModifica): $contatoreTrovato = false; ?>
                        <?php foreach ($opzioniContatori as $opzione): if ((int)$opzione['id_contatore'] === (int)$rigaInModifica['id_contatore']) $contatoreTrovato = true; endforeach; ?>
                        <?php if (!$contatoreTrovato): ?><option value="<?php echo (int)$rigaInModifica['id_contatore']; ?>" selected>(ORFANO #<?php echo (int)$rigaInModifica['id_contatore']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniContatori as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_contatore']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_contatore'] === (int)$rigaInModifica['id_contatore']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['nome'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="id_prodotto" class="codice-text-field" required>
                    <option value="0">PRODOTTO…</option>
                    <?php if ($inModifica): $prodottoTrovato = false; ?>
                        <?php foreach ($opzioniProdotti as $opzione): if ((int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) $prodottoTrovato = true; endforeach; ?>
                        <?php if (!$prodottoTrovato): ?><option value="<?php echo (int)$rigaInModifica['id_prodotto']; ?>" selected>(ORFANO #<?php echo (int)$rigaInModifica['id_prodotto']; ?>)</option><?php endif; ?>
                    <?php endif; ?>
                    <?php foreach ($opzioniProdotti as $opzione): ?>
                        <option value="<?php echo (int)$opzione['id_prodotto']; ?>"<?php echo ($rigaInModifica && (int)$opzione['id_prodotto'] === (int)$rigaInModifica['id_prodotto']) ? ' selected' : ''; ?>><?php echo htmlspecialchars(strtoupper($opzione['descrizione_prod'] ?? '')); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantita" class="codice-text-field" step="1" min="0" required placeholder="QUANTITA DISPONIBILE" value="<?php echo htmlspecialchars($rigaInModifica['quantita'] ?? ''); ?>">
                <!-- Tastierino numerico int-only (issue, variante A): solo cifre + CANC sul campo quantita -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERINO NUMERICO</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('123', '456', '789', '0') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kbr-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === '0'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kbr-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=prodotti_contatori" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-regola">SVUOTA CAMPI</button>
                </div>
            </form>
            <!-- Form prodotto: ramo else perché prodotti è il tab di fallback (issue: solo PREZZO, barcode solo a fiera attiva, SVUOTA; tastiera come categorie con PREZZO double-only come QUANTITA in regole) -->
            <?php else: ?>
            <form action="<?php echo htmlspecialchars($actionForm); ?>" method="post" id="form-prodotto" style="display:flex;flex-direction:column;gap:8px;max-width:560px;width:100%;">
                <input type="hidden" name="t" value="prodotti">
                <input type="hidden" name="id" value="<?php echo $inModifica ? (int)$rigaInModifica['id_prodotto'] : 0; ?>">
                <?php csrf_field(); ?>
                <input type="text" name="descrizione_prod" class="codice-text-field" maxlength="100" required placeholder="DESCRIZIONE" value="<?php echo htmlspecialchars($rigaInModifica['descrizione_prod'] ?? ''); ?>">
                <input type="text" name="testo_biglietto" class="codice-text-field" maxlength="100" placeholder="TESTO BIGLIETTO" value="<?php echo htmlspecialchars($rigaInModifica['testo_biglietto'] ?? ''); ?>">
                <input type="number" name="prezzo" class="codice-text-field" step="0.01" min="0" required placeholder="PREZZO" value="<?php echo htmlspecialchars($rigaInModifica['prezzo'] ?? ''); ?>">
                <div class="admin-btn-row" style="width:100%;">
                    <select name="olpp" class="codice-text-field" style="flex:1;">
                        <option value="T"<?php echo ($rigaInModifica['olpp'] ?? 'T') == 'T' ? ' selected' : ''; ?>>OLPP T</option>
                        <option value="F"<?php echo ($rigaInModifica['olpp'] ?? '') == 'F' ? ' selected' : ''; ?>>OLPP F</option>
                    </select>
                    <?php if (fieraAttiva()): ?>
                    <input type="text" name="barcode" class="codice-text-field" maxlength="20" required placeholder="BARCODE" value="<?php echo htmlspecialchars($rigaInModifica['barcode'] ?? ''); ?>" style="flex:2;">
                    <?php else: ?>
                    <input type="hidden" name="barcode" value="<?php echo htmlspecialchars($rigaInModifica['barcode'] ?? '-'); ?>">
                    <?php endif; ?>
                </div>
                <!-- Tastiera touch (variante A da issue, come categorie): focus-target DESCRIZIONE/BIGLIETTO/PREZZO/BARCODE, double-only su PREZZO come QUANTITA in regole -->
                <details open style="width:100%;">
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:12px 16px;font-size:20px;">TASTIERA</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $rigaTastiera): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($iTasto = 0; $iTasto < strlen($rigaTastiera); $iTasto++): ?>
                                    <button type="button" class="tastierino-btn" data-kbp-ch="<?php echo $rigaTastiera[$iTasto]; ?>" style="flex:1;height:48px;font-size:16px;"><?php echo $rigaTastiera[$iTasto]; ?></button>
                                <?php endfor; ?>
                                <?php if ($rigaTastiera === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" data-kbp-canc style="flex:1.5;height:48px;font-size:16px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-kbp-ch=" " style="flex:1;height:48px;font-size:16px;">SPAZIO</button>
                            <button type="button" class="tastierino-btn" data-kbp-ch="." style="flex:1;height:48px;font-size:16px;">.</button>
                            <button type="button" class="tastierino-btn" data-kbp-ch="," style="flex:1;height:48px;font-size:16px;">,</button>
                        </div>
                    </div>
                </details>
                <div class="admin-btn-row">
                    <a href="visualizza.php?tab=prodotti" class="opzione-btn">TORNA A SALSICCIA</a>
                    <button type="submit" name="save" class="opzione-btn"><?php echo $inModifica ? 'SALVA' : 'AGGIUNGI'; ?></button>
                    <button type="button" class="opzione-btn" id="svuota-prodotto">SVUOTA CAMPI</button>
                </div>
            </form>
            <?php endif; ?>
        </section>
    </main>
<?php else: ?>
    <!-- Vista lista del tab corrente con ricerca, ordinamento e paginazione -->
    <main>
        <header><h1><?php echo $tabValido ? htmlspecialchars($tabConfig['titolo']) : 'TABELLA NON TROVATA'; ?></h1></header>
        <section class="admin-panel">
            <?php if (!$tabValido): ?>
                <h3 class="admin-group-title">TABELLA NON TROVATA — SCEGLI DALLA LISTA</h3>
            <?php else: ?>
                <h3 class="admin-group-title"><?php echo htmlspecialchars($tabConfig['label']); ?></h3>
                <?php if ($messaggioStato !== ''): ?>
                    <h3 class="admin-group-title"><?php echo htmlspecialchars($messaggioStato); ?></h3>
                <?php endif; ?>
                <!-- Ricerca GET: conserva tab e ordinamento, X azzera solo la query -->
                <form action="visualizza.php" method="get" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tabCorrente); ?>">
                    <?php if ($colonnaOrd !== ''): ?>
                        <input type="hidden" name="OR" value="<?php echo htmlspecialchars($colonnaOrd); ?>">
                        <input type="hidden" name="DIR" value="<?php echo $direzioneOrd; ?>">
                    <?php endif; ?>
                    <input type="text" id="q" name="q" class="codice-text-field" maxlength="50" placeholder="CERCA…" value="<?php echo htmlspecialchars($ricerca); ?>" style="margin-bottom:0;text-align:left;padding-left:12px;">
                    <button type="submit" class="opzione-btn" style="padding:12px 20px;">CERCA</button>
                    <?php if ($ricerca !== ''): ?>
                        <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;">X</a>
                    <?php endif; ?>
                </form>
                <!-- Tastierino touch: scrive nel campo ricerca senza tastiera fisica -->
                <details>
                    <summary class="opzione-btn" style="cursor:pointer;text-align:center;list-style:none;padding:8px 16px;font-size:20px;">TASTIERA +</summary>
                    <div style="display:flex;flex-direction:column;gap:4px;margin-top:12px;">
                        <?php foreach (array('QWERTYUIOP', 'ASDFGHJKL', 'ZXCVBNM', '1234567890') as $row): ?>
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <?php for ($i = 0; $i < strlen($row); $i++): ?>
                                    <button type="button" class="tastierino-btn" data-ch="<?php echo $row[$i]; ?>" style="flex:1;height:36px;font-size:14px;"><?php echo $row[$i]; ?></button>
                                <?php endfor; ?>
                                <?php if ($row === 'ZXCVBNM'): ?>
                                    <button type="button" class="tastierino-action-btn action-canc" id="kb-canc" style="flex:1.5;height:36px;font-size:14px;">CANC</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="display:flex;gap:4px;justify-content:center;">
                            <button type="button" class="tastierino-btn" data-ch=" " style="flex:1;height:36px;font-size:14px;">SPAZIO</button>
                        </div>
                    </div>
                </details>
                <!-- Tabella: intestazioni cliccabili per ordinare, ogni click azzera la pagina -->
                <table class="modifica-table">
                    <thead><tr>
                        <?php foreach ($tabConfig['headers'] as $intestazione): ?>
                            <?php // $eOrdinata colonna ordinata, $prossimaDir direzione opposta per il prossimo click
                            $eOrdinata = ($colonnaOrd === $intestazione[0]); $prossimaDir = ($eOrdinata && $direzioneOrd === 'ASC') ? 'DESC' : 'ASC'; ?>
                            <th class="intestazione-tabella-descrizione"><?php echo htmlspecialchars($intestazione[1]); ?> <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'OR' => $intestazione[0], 'DIR' => $prossimaDir, 'page' => 1) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" style="font-size:22px;text-decoration:none;"><?php echo $eOrdinata ? ($direzioneOrd === 'ASC' ? '▲' : '▼') : '△'; ?></a></th>
                        <?php endforeach; ?>
                        <th class="intestazione-tabella-azioni">AZIONI</th>
                    </tr></thead>
                    <tbody>
                    <?php if (count($righe) === 0): ?>
                        <tr><td class="cella-tabella-descrizione" style="font-size:16px;" colspan="<?php echo count($tabConfig['headers']) + 1; ?>">NESSUNA RIGA</td></tr>
                    <?php endif; ?>
                    <!-- Righe: un ramo di colonne e azioni per ciascuno dei 5 tab -->
                    <?php foreach ($righe as $riga): ?>
                        <tr>
                        <?php if ($tabCorrente === 'categorie'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['descrizione_cat'] ?? '')); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars($riga['testo_bottone'] ?? ''); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars(etichettaColore($riga['colore'] ?? '')); ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'categorie', 'edit' => (int)$riga['id_categoria']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'categorie'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare <?php echo htmlspecialchars($riga['descrizione_cat'] ?? '', ENT_QUOTES); ?>?');"><input type="hidden" name="del" value="<?php echo (int)$riga['id_categoria']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'prodotti'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['descrizione_prod'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;">&euro; <?php echo number_format((float)$riga['prezzo'], 2, ',', '.'); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo $riga['olpp'] === 'T' ? '1:1' : '1:n'; ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti', 'edit' => (int)$riga['id_prodotto']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare <?php echo htmlspecialchars($riga['descrizione_prod'], ENT_QUOTES); ?>?');"><input type="hidden" name="del" value="<?php echo (int)$riga['id_prodotto']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'prodotti_categorie'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['prodotto'])); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars(strtoupper($riga['categoria'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['posizione']; ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_categorie', 'edit_p' => (int)$riga['id_prodotto'], 'edit_c' => (int)$riga['id_categoria'], 'edit_pos' => (int)$riga['posizione']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_categorie'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare questa POSIZIONE?');"><input type="hidden" name="del_p" value="<?php echo (int)$riga['id_prodotto']; ?>"><input type="hidden" name="del_c" value="<?php echo (int)$riga['id_categoria']; ?>"><input type="hidden" name="del_pos" value="<?php echo (int)$riga['posizione']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'contatori'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['nome'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['limite_qta']; ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo etichettaSiNo($riga['attivo']); ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'contatori', 'edit' => (int)$riga['id_contatore']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'contatori'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare <?php echo htmlspecialchars($riga['nome'], ENT_QUOTES); ?>?');"><input type="hidden" name="del" value="<?php echo (int)$riga['id_contatore']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php elseif ($tabCorrente === 'prodotti_contatori'): ?>
                            <td class="cella-tabella-descrizione" style="font-size:16px;"><?php echo htmlspecialchars(strtoupper($riga['contatore'])); ?></td>
                            <td class="cella-tabella-descrizione" style="font-size:16px;text-align:left;"><?php echo htmlspecialchars(strtoupper($riga['prodotto'])); ?></td>
                            <td class="cella-tabella-prezzo" style="font-size:16px;"><?php echo (int)$riga['quantita']; ?></td>
                            <td class="cella-tabella-azioni">
                                <a href="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_contatori', 'edit_c' => (int)$riga['id_contatore'], 'edit_p' => (int)$riga['id_prodotto']))); ?>" class="action-btn action-check" style="text-decoration:none">&#9998;</a>
                                <form method="post" action="<?php echo htmlspecialchars(urlLista(array('tab' => 'prodotti_contatori'))); ?>" style="display:inline;" onsubmit="return confirm('Eliminare questa REGOLA?');"><input type="hidden" name="del_c" value="<?php echo (int)$riga['id_contatore']; ?>"><input type="hidden" name="del_p" value="<?php echo (int)$riga['id_prodotto']; ?>"><?php csrf_field(); ?><button type="submit" class="action-btn action-delete">&#10005;</button></form>
                            </td>
                        <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Paginazione compatta: prima, corrente e vicine, conserva ricerca e ordinamento -->
                <div class="admin-btn-row">
                    <?php if ($paginaCorrente > 1): ?>
                        <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'page' => $paginaCorrente - 1) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;">&lt;</a>
                    <?php endif; ?>
                    <?php foreach (array_unique(array(1, $paginaCorrente - 1, $paginaCorrente, $paginaCorrente + 1, $totPagine)) as $numPagina): ?>
                        <?php if ($numPagina < 1 || $numPagina > $totPagine) continue; ?>
                        <?php if ($numPagina == $paginaCorrente): ?>
                            <span class="opzione-btn" style="padding:12px 20px;border:2px solid #2b3d4e;"><?php echo $numPagina; ?></span>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'page' => $numPagina) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;"><?php echo $numPagina; ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($paginaCorrente < $totPagine): ?>
                        <a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'page' => $paginaCorrente + 1) + ($colonnaOrd !== '' ? array('OR' => $colonnaOrd, 'DIR' => $direzioneOrd) : array()) + ($ricerca !== '' ? array('q' => $ricerca) : array()))); ?>" class="opzione-btn" style="padding:12px 20px;">&gt;</a>
                    <?php endif; ?>
                </div>
                <!-- Pulsante nuovo con etichetta diversa per ogni tab -->
                <?php $etichetteNuovo = array('categorie' => 'NUOVA CATEGORIA', 'prodotti' => 'NUOVO PRODOTTO', 'prodotti_categorie' => 'NUOVA POSIZIONE', 'contatori' => 'NUOVO VENDUTO', 'prodotti_contatori' => 'NUOVA REGOLA'); ?>
                <div class="admin-btn-row"><a href="<?php echo htmlspecialchars(urlLista(array('tab' => $tabCorrente, 'nuovo' => 1))); ?>" class="opzione-btn"><?php echo $etichetteNuovo[$tabCorrente]; ?></a></div>
            <?php endif; ?>
        </section>
    </main>
    <!-- Nav verticale dei 5 tab, evidenzia quello corrente -->
    <aside>
        <h3 class="admin-group-title">TABELLA</h3>
        <nav style="flex-direction:column;align-items:stretch;">
            <?php foreach ($configTabs as $slugTab => $configTab): ?>
                <button onclick="location.href='<?php echo htmlspecialchars(urlLista(array('tab' => $slugTab)), ENT_QUOTES); ?>'"<?php echo ($tabValido && $slugTab === $tabCorrente) ? ' class="attivo"' : ''; ?>><?php echo htmlspecialchars($configTab['label']); ?></button>
            <?php endforeach; ?>
            <hr style="border:0;border-top:2px solid #e7e9eb;margin:16px 0;">
            <button onclick="location.href='statistiche.php'">STATISTICHE</button>
        </nav>
        <div class="admin-btn-row" style="margin-top:auto;"><a href="../index.php" class="opzione-btn">TORNA A SALSICCIA</a><a href="backup.php" class="opzione-btn">BACKUP</a><a href="visualizza.php?logout=1" class="opzione-btn">LOGOUT</a></div>
    </aside>
<?php endif; ?>
<script>
// Tastiera form categoria (issue, variante A da issue): scrive sul campo con focus,
// CANC = slice ultimo char, SVUOTA = form.reset() senza submit.
(function()
{
    var f = document.getElementById('form-categoria');
    if (!f) return;
    var campi = f.querySelectorAll('input[type=text]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kb-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            target.value += b.getAttribute('data-kb-ch'); target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kb-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-campi');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Tastiera form prodotto (variante A come categorie, riuso): focus-target DESCRIZIONE/BIGLIETTO/PREZZO/BARCODE, double-only su PREZZO come QUANTITA in regole, SVUOTA reset.
(function()
{
    var f = document.getElementById('form-prodotto');
    if (!f) return;
    var campi = f.querySelectorAll('input[name=descrizione_prod],input[name=testo_biglietto],input[name=prezzo],input[name=barcode]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbp-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            var ch = b.getAttribute('data-kbp-ch');
            if (target.name === 'prezzo')
            {
                if (ch === ',') ch = '.';
                if (ch === '.')
                {
                    if (target.value.indexOf('.') !== -1) return;
                    ch = target.value === '' ? '0.' : '.';
                }
                else if (!/[0-9]/.test(ch)) return;
            }
            target.value += ch; target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbp-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-prodotto');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Tastiera form contatore (issue, variante A riuso): focus-target NOME/LIMITE, int-only su LIMITE, SVUOTA reset.
(function()
{
    var f = document.getElementById('form-contatore');
    if (!f) return;
    var campi = f.querySelectorAll('input[name=nome],input[name=limite_qta]');
    var target = campi.length ? campi[0] : null;
    Array.prototype.forEach.call(campi, function(i)
    {
        i.addEventListener('focus', function() { target = i; });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbc-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            var ch = b.getAttribute('data-kbc-ch');
            if (target.name === 'limite_qta' && !/[0-9]/.test(ch)) return;
            target.value += ch; target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbc-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-contatore');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Tastierino numerico form regola (issue, variante A): solo cifre su quantita, SVUOTA reset.
(function()
{
    var f = document.getElementById('form-regola');
    if (!f) return;
    var target = f.querySelector('input[name=quantita]');
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbr-ch]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (!target) return;
            target.value += b.getAttribute('data-kbr-ch'); target.focus();
        });
    });
    Array.prototype.forEach.call(f.querySelectorAll('[data-kbr-canc]'), function(b)
    {
        b.addEventListener('click', function()
        {
            if (target) { target.value = target.value.slice(0, -1); target.focus(); }
        });
    });
    var sv = document.getElementById('svuota-regola');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset();
        if (target) target.focus();
    });
})();
// Griglia posizioni 4x6 (issue): verde libera / rossa occupata per categoria, click = gialla + hidden sincronizzato, SVUOTA resetta anche la griglia.
(function()
{
    var f = document.getElementById('form-posizione');
    if (!f) return;
    var val = document.getElementById('pos-val');
    var cat = f.querySelector('select[name=id_categoria]');
    var celle = Array.prototype.slice.call(f.querySelectorAll('#pos-grid [data-pos]'));
    var occ = <?php echo json_encode($mappaPosizioni); ?>;
    function render()
    {
        var sel = parseInt(val.value || '0', 10);
        var piene = {};
        Array.prototype.forEach.call(occ[cat.value] || [], function(p) { piene[parseInt(p, 10)] = 1; });
        Array.prototype.forEach.call(celle, function(b)
        {
            var p = parseInt(b.getAttribute('data-pos'), 10);
            b.classList.remove('pos-libera', 'pos-occupata', 'pos-selezionata');
            if (p === sel && sel >= 1 && sel <= 24) b.classList.add('pos-selezionata');
            else if (piene[p]) b.classList.add('pos-occupata');
            else b.classList.add('pos-libera');
        });
    }
    Array.prototype.forEach.call(celle, function(b)
    {
        b.addEventListener('click', function() { val.value = b.getAttribute('data-pos'); render(); });
    });
    if (cat) cat.addEventListener('change', render);
    var sv = document.getElementById('svuota-posizione');
    if (sv) sv.addEventListener('click', function()
    {
        f.reset(); val.value = '0'; render();
        var s = f.querySelector('select'); if (s) s.focus();
    });
    render();
})();
</script>
<script>

// Tastierino: accoda il carattere al campo ricerca e ridà il focus
document.querySelectorAll('.tastierino-btn[data-ch]').forEach(function(b)
{
    b.addEventListener('click', function()
    {
        var i = document.getElementById('q');
        if (i)
        {
            i.value += b.getAttribute('data-ch'); i.focus();
        }
    });
});
 // Tasto CANC: cancella l'ultimo carattere della ricerca
var kc = document.getElementById('kb-canc');

if (kc) kc.addEventListener('click', function()
{
    var i = document.getElementById('q');
    if (i)
    {
        i.value = i.value.slice(0, -1); i.focus();
    }
});
</script>
</body>
</html>
