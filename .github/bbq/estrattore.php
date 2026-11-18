<?php
/**
 * Estrattore temporaneo usato SOLO dal workflow "Pubblica Moodle su Aruba".
 * Il workflow lo carica in public/ con nome e chiave casuali, lo usa e lo cancella.
 * Non va mai lasciato sul server.
 */
define('CHIAVE', '__CHIAVE__');
define('BASE', dirname(__DIR__));                      // radice del sito (questo file sta in public/)
define('ZIPFILE', BASE . '/bbq-deploy.zip');
define('ELIMINA', BASE . '/bbq-deploy-elimina.txt');
define('MARCA', BASE . '/.bbq-deploy-commit');
define('DATAROOT', '__DATAROOT__');
define('BLOCCO', 2500);

header('Content-Type: application/json; charset=utf-8');
function fine($dati, $codice = 200) { http_response_code($codice); echo json_encode($dati); exit; }
if (!hash_equals(CHIAVE, (string)($_GET['k'] ?? ''))) fine(['errore' => 'accesso negato'], 403);
@set_time_limit(300);

// Percorso relativo sicuro: niente "..", niente percorsi assoluti, mai config.php o moodledata.
function sicuro($nome) {
    $nome = str_replace('\\', '/', $nome);
    if ($nome === '' || $nome[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $nome)) return false;
    if ($nome === 'config.php' || str_starts_with($nome, 'moodledata/')) return false;
    return true;
}

$op = $_GET['op'] ?? '';

if ($op === 'inizio') {                                  // manutenzione durante la copia
    $ok = @file_put_contents(DATAROOT . '/climaintenance.html',
        '<h1>Aggiornamento in corso</h1><p>Torniamo online tra pochi minuti.</p>') !== false;
    fine(['manutenzione' => $ok]);
}

if ($op === 'estrai') {
    if (!class_exists('ZipArchive')) fine(['errore' => 'estensione zip mancante'], 500);
    $zip = new ZipArchive();
    if ($zip->open(ZIPFILE) !== true) fine(['errore' => 'zip non trovato'], 500);
    $totale = $zip->numFiles;
    $da = max(0, (int)($_GET['da'] ?? 0));
    $a = min($totale, $da + BLOCCO);
    $errori = [];
    for ($i = $da; $i < $a; $i++) {
        $nome = $zip->getNameIndex($i);
        if ($nome === false || !sicuro($nome)) { $errori[] = "saltato: $nome"; continue; }
        $dest = BASE . '/' . $nome;
        if (str_ends_with($nome, '/')) { if (!is_dir($dest)) @mkdir($dest, 0755, true); continue; }
        if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0755, true);
        $dati = $zip->getFromIndex($i);
        if ($dati === false || file_put_contents($dest, $dati) === false) $errori[] = "errore: $nome";
    }
    $zip->close();
    fine(['fatti' => $a, 'totale' => $totale, 'errori' => $errori]);
}

if ($op === 'chiudi') {
    $eliminati = 0; $errori = [];
    if (is_file(ELIMINA)) {
        foreach (file(ELIMINA, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $nome) {
            if (!sicuro($nome)) { $errori[] = "saltato: $nome"; continue; }
            $p = BASE . '/' . $nome;
            if (is_file($p)) { if (@unlink($p)) $eliminati++; else $errori[] = "non eliminato: $nome"; }
        }
    }
    $commit = preg_replace('/[^0-9a-f]/', '', (string)($_GET['c'] ?? ''));
    if (strlen($commit) === 40) file_put_contents(MARCA, $commit . "\n");
    @unlink(ZIPFILE);
    @unlink(ELIMINA);
    @unlink(DATAROOT . '/climaintenance.html');
    @unlink(__FILE__);
    fine(['eliminati' => $eliminati, 'errori' => $errori, 'commit' => $commit]);
}

fine(['errore' => 'operazione sconosciuta'], 400);
