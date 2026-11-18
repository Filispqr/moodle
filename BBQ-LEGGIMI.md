# Moodle di www.cominciadaqui.it (BBQ Fiumicino)

Ramo `bbq` = Moodle ufficiale `MOODLE_503_STABLE` + aggiunte nostre:

- `public/mod/hvp` – H5P 1.28.5 (github.com/h5p/moodle-mod_hvp, con sottomoduli)
- `public/mod/customcert` – Certificato personalizzato 5.3.5 (tag v5.3.5)
- `.htaccess` (radice) e `public/.htaccess` – Moodle 5.1+ su hosting condiviso Aruba
- `.github/workflows/deploy-aruba.yml` + `.github/bbq/` – pubblicazione su Aruba: carica uno zip con i soli file cambiati e lo fa scompattare sul server da uno script temporaneo (cancellato a fine lavoro). Il commit pubblicato è scritto in `.bbq-deploy-commit` nella radice del sito.

Mai su GitHub: `config.php` (password) e `moodledata` (dati). Restano solo sul server.

## Aggiornare Moodle
1. Ramo `MOODLE_503_STABLE` -> **Sync fork** -> Update branch.
2. **Pull request** da `MOODLE_503_STABLE` a `bbq` -> Merge.
3. **Actions** -> Pubblica Moodle su Aruba -> **Run workflow** (ramo `bbq`). Durante la copia il sito va da solo in manutenzione e torna online alla fine (pochi minuti).
4. Aprire `https://www.cominciadaqui.it/admin` e confermare l'aggiornamento del database.

Se qualcosa va storto a metà, il sito resta in manutenzione (file `climaintenance.html` in moodledata): rilanciare il workflow. Per ricaricare tutto: campo "base" = `tutto`.

## Aggiornare un plugin
Sostituire la cartella in `public/mod/...`, commit sul ramo `bbq`, poi passi 3-4.
