# Run locally in PowerShell (not via browser). Never commit actual dump files.
$ErrorActionPreference = 'Stop'
$exe = 'C:\xampp\mysql\bin\mysqldump.exe'
if (-not (Test-Path $exe)) { throw "mysqldump not found at $exe. Update path to match XAMPP." }
$root = Split-Path -Parent $PSScriptRoot
$dir = Join-Path $root 'database\backups'
New-Item -ItemType Directory -Path $dir -Force | Out-Null
$name = 'studenthub_pr8_db_' + (Get-Date -Format 'yyyyMMdd_HHmmss') + '.sql'
$target = Join-Path $dir $name
# XAMPP typically has root with no password on local installations.
# If yours uses a password, configure MySQL option file locally instead of putting it in Git.
& $exe --host=127.0.0.1 --user=root --single-transaction --routines --triggers --events --default-character-set=utf8mb4 --databases studenthub_pr8_db --result-file=$target
if ($LASTEXITCODE -ne 0) { throw 'mysqldump failed. Check DB password and XAMPP MySQL.' }
Write-Host "Private full database dump saved: $target"
Write-Host 'This contains account password hashes and possibly personal data. Do NOT commit it to a public GitHub repository.'
