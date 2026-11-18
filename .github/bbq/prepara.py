#!/usr/bin/env python3
"""Prepara il pacchetto per Aruba: bbq-deploy.zip (file nuovi o cambiati) e
bbq-deploy-elimina.txt (file cancellati). Uso: prepara.py <commit-base o vuoto>"""
import subprocess, sys, zipfile, os

def escluso(p):
    parti = p.split('/')
    return (p == 'config.php' or p.startswith('moodledata/') or p.startswith('.github/')
            or any(x.startswith('.git') for x in parti) or parti[-1] == '.DS_Store')

def git(*a):
    return subprocess.run(['git', *a], check=True, capture_output=True).stdout

base = sys.argv[1].strip() if len(sys.argv) > 1 else ''
if base and subprocess.run(['git', 'cat-file', '-e', base + '^{commit}']).returncode != 0:
    print(f'::warning::Commit {base} non trovato nella storia: pubblico tutto.')
    base = ''
if base:
    base = git('rev-parse', base + '^{commit}').decode().strip()

cambiati, eliminati = [], []
if base:
    voci = git('diff', '--no-renames', '--name-status', '-z', base, 'HEAD').split(b'\0')
    for stato, percorso in zip(voci[0::2], voci[1::2]):
        p = percorso.decode()
        if escluso(p):
            continue
        (eliminati if stato == b'D' else cambiati).append(p)
else:
    cambiati = [p for p in git('ls-files', '-z').decode().split('\0') if p and not escluso(p)]

# I sottomoduli/link simbolici non sono file normali: li salto.
cambiati = [p for p in cambiati if os.path.isfile(p) and not os.path.islink(p)]

with zipfile.ZipFile('bbq-deploy.zip', 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as z:
    for p in cambiati:
        z.write(p, p)
with open('bbq-deploy-elimina.txt', 'w') as f:
    f.write(''.join(p + '\n' for p in eliminati))

modo = 'differenze da ' + base[:9] if base else 'completo'
print(f'Pacchetto {modo}: {len(cambiati)} file da caricare, {len(eliminati)} da eliminare.')
with open(os.environ.get('GITHUB_OUTPUT', '/dev/null'), 'a') as o:
    o.write(f'cambiati={len(cambiati)}\neliminati={len(eliminati)}\nmodo={modo}\n')
