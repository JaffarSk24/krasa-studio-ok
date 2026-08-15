#!/usr/bin/env python3
"""Выгрузка файлов проекта на прод по FTP.

Доступы берутся из .deploy.env в корне проекта (файл в .gitignore).

Примеры:
    python3 tools/deploy_ftp.py --changed              # всё, что изменено относительно HEAD
    python3 tools/deploy_ftp.py public_html/index.php  # конкретные файлы
    python3 tools/deploy_ftp.py --changed --dry-run    # только показать список
"""

import argparse
import os
import subprocess
import sys
from ftplib import FTP

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def load_env(path):
    env = {}
    if not os.path.exists(path):
        sys.exit(f"Нет файла {path} — положи туда FTP_HOST/FTP_USER/FTP_PASS")
    with open(path, encoding='utf-8') as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            key, val = line.split('=', 1)
            env[key.strip()] = val.strip().strip('"\'')
    return env


def changed_files():
    """Файлы под public_html, изменённые относительно HEAD (staged + unstaged + новые)."""
    out = subprocess.run(
        ['git', 'status', '--porcelain', '--', 'public_html'],
        cwd=ROOT, capture_output=True, text=True, check=True,
    ).stdout
    files = []
    for line in out.splitlines():
        path = line[3:].strip().strip('"')
        if ' -> ' in path:                      # переименование
            path = path.split(' -> ', 1)[1]
        if line[:2] == '!!' or path.endswith('/'):
            continue
        if os.path.isfile(os.path.join(ROOT, path)):
            files.append(path)
    return files


def ensure_dir(ftp, remote_dir):
    ftp.cwd('/')
    for part in remote_dir.strip('/').split('/'):
        if not part:
            continue
        try:
            ftp.cwd(part)
        except Exception:
            ftp.mkd(part)
            ftp.cwd(part)


def main():
    ap = argparse.ArgumentParser(description='FTP-деплой файлов проекта')
    ap.add_argument('files', nargs='*', help='пути относительно корня проекта')
    ap.add_argument('--changed', action='store_true', help='взять изменённые по git файлы из public_html')
    ap.add_argument('--dry-run', action='store_true', help='ничего не заливать, только показать список')
    args = ap.parse_args()

    files = list(args.files)
    if args.changed:
        files += changed_files()
    if not files:
        if args.changed:
            print('Изменённых файлов в public_html нет — заливать нечего')
            return
        sys.exit('Нечего заливать: укажи файлы или используй --changed')
    files = sorted(set(files))

    print(f'Файлов к выгрузке: {len(files)}')
    for path in files:
        print('  ', path)
    if args.dry_run:
        return

    env = load_env(os.path.join(ROOT, '.deploy.env'))
    ftp = FTP(env['FTP_HOST'])
    ftp.login(env['FTP_USER'], env['FTP_PASS'])
    print(f"Подключились к {env['FTP_HOST']}")

    uploaded = 0
    for path in files:
        local = os.path.join(ROOT, path)
        if not os.path.isfile(local):
            print(f'  ПРОПУСК (нет локально): {path}')
            continue
        remote = '/' + path.replace(os.sep, '/')
        ensure_dir(ftp, os.path.dirname(remote))
        with open(local, 'rb') as f:
            ftp.storbinary(f'STOR {os.path.basename(remote)}', f)
        print(f'  OK {path} -> {remote}')
        uploaded += 1

    ftp.quit()
    print(f'Готово, выгружено файлов: {uploaded}')


if __name__ == '__main__':
    main()
