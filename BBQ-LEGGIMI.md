# Moodle di www.cominciadaqui.it (BBQ Fiumicino)

Ramo `bbq` = Moodle ufficiale `MOODLE_503_STABLE` + aggiunte nostre:

- `public/mod/hvp` – H5P 1.28.5 (github.com/h5p/moodle-mod_hvp, con sottomoduli)
- `public/mod/customcert` – Certificato personalizzato 5.3.5 (tag v5.3.5)
- `.htaccess` (radice) e `public/.htaccess` – Moodle 5.1+ su hosting condiviso Aruba
- `.github/workflows/deploy-aruba.yml` – pubblicazione via FTP

Mai su GitHub: `config.php` (password) e `moodledata` (dati). Restano solo sul server.

## Aggiornare Moodle
1. Ramo `MOODLE_503_STABLE` -> **Sync fork** -> Update branch.
2. **Pull request** da `MOODLE_503_STABLE` a `bbq` -> Merge.
3. Moodle in **Modalità manutenzione**.
4. **Actions** -> Pubblica Moodle su Aruba -> **Run workflow** (ramo `bbq`).
5. Aprire `https://www.cominciadaqui.it/admin`, completare l'aggiornamento, togliere la manutenzione.

## Aggiornare un plugin
Sostituire la cartella in `public/mod/...`, commit sul ramo `bbq`, poi passi 3-5.
