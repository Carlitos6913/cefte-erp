# Backup automático de la base de datos CEFTE (MySQL).
#
# Uso manual:
#   powershell -ExecutionPolicy Bypass -File scripts\backup-database.ps1
#
# Programar en el Administrador de tareas (una vez al día, p. ej. 02:00):
#   schtasks /Create /TN "CEFTE-BackupBD" /SC DAILY /ST 02:00 /RL HIGHEST /TR "powershell -ExecutionPolicy Bypass -File \"D:\Laragon\laragon\www\cefte-erp\scripts\backup-database.ps1\""
#   schtasks /Delete /TN "CEFTE-BackupBD" /F
#
# Restaurar (reemplaza la BD):
#   mysql -u root cefte_erp < backups\backup_2026-09-25.sql

param(
    [string]$OutputDir = "D:\Laragon\laragon\www\cefte-erp\storage\backups",
    [string]$MysqlBin = "D:\Laragon\laragon\bin\mysql\mysql-8.4.3-winx64\bin",
    [string]$Mysqldump = "$MysqlBin\mysqldump.exe",
    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 3306,
    [string]$DbUser = "root",
    [string]$DbPassword = "",
    [string]$DbName = "cefte_erp"
)

$ErrorActionPreference = "Stop"

# Leer credenciales desde .env si existe.
$envFile = Join-Path (Split-Path $PSScriptRoot -Parent) ".env"
if (Test-Path $envFile) {
    $envLines = Get-Content $envFile
    $get = { param($key) ($envLines | Where-Object { $_ -match "^$key=" }) -replace "^$key=", "" }
    if (& $get "DB_HOST")      { $DbHost = & $get "DB_HOST" }
    if (& $get "DB_PORT")      { $DbPort = [int](& $get "DB_PORT") }
    if (& $get "DB_USERNAME")  { $DbUser = & $get "DB_USERNAME" }
    if (& $get "DB_PASSWORD")  { $DbPassword = & $get "DB_PASSWORD" }
    if (& $get "DB_DATABASE")  { $DbName = & $get "DB_DATABASE" }
}

if (!(Test-Path $Mysqldump)) {
    throw "No se encontró mysqldump en: $Mysqldump"
}

if (!(Test-Path $OutputDir)) {
    New-Item -ItemType Directory -Path $OutputDir -Force | Out-Null
}

$timestamp = Get-Date -Format "yyyy-MM-dd_HHmmss"
$archivo = Join-Path $OutputDir "backup_cefte_erp_$timestamp.sql"
$log = Join-Path $OutputDir "backup_cefte_erp.log"

$args = @(
    "--host=$DbHost",
    "--port=$DbPort",
    "--user=$DbUser"
)

if ($DbPassword -ne "") {
    $args += "--password=$DbPassword"
}

$args += "--single-transaction"
$args += "--routines"
$args += "--triggers"
$args += "--result-file=$archivo"
$args += $DbName

& $Mysqldump @args

if ($LASTEXITCODE -ne 0) {
    Add-Content -Path $log -Value "$(Get-Date) - ERROR: mysqldump falló con código $LASTEXITCODE"
    throw "mysqldump falló con código $LASTEXITCODE"
}

$peso = [math]::Round((Get-Item $archivo).Length / 1MB, 2)
Add-Content -Path $log -Value "$(Get-Date) - OK: $archivo ($peso MB)"

# Retener los últimos 30 backups.
Get-ChildItem -Path $OutputDir -Filter "backup_cefte_erp_*.sql" |
    Sort-Object LastWriteTime -Descending |
    Select-Object -Skip 30 |
    Remove-Item -Force

Write-Host "Backup completo: $archivo"