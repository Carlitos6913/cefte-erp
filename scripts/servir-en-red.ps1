# Servir CEFTE en la red local para probarlo desde el celular.
#
# Qué hace:
#   1. Detecta la IP local de la PC.
#   2. Ajusta temporalmente APP_URL del .env a http://<IP>:8000.
#   3. Levanta `php artisan serve --host=0.0.0.0 --port=8000` en un proceso aparte
#      (con log en storage\logs\servir-en-red.*) para que no muera con esta ventana.
#   4. Al salir (pulsa cualquier tecla) detiene el servidor y restaura el APP_URL.
#
# Uso:
#   powershell -ExecutionPolicy Bypass -File scripts\servir-en-red.ps1
#
# Si el servidor se cierra solo, revisa el log:
#   Get-Content storage\logs\servir-en-red.err.log
#
# Requisitos: el celular debe estar en el mismo Wi-Fi (o hotspot del celular) que la PC.
# Si la conexión falla, ejecutar como administrador para crear la regla de firewall:
#   powershell -ExecutionPolicy Bypass -File scripts\servir-en-red.ps1 -Firewall

param(
    [switch]$Firewall
)

$ErrorActionPreference = "Stop"

$Port = 8000
$Root = Split-Path $PSScriptRoot -Parent
$EnvFile = Join-Path $Root ".env"
$TempOriginal = Join-Path $env:TEMP "cefte_erp_appurl_original.txt"
$LogOut = Join-Path $Root "storage\logs\servir-en-red.out.log"
$LogErr = Join-Path $Root "storage\logs\servir-en-red.err.log"

# ---------------------------------------------------------------------------
# Localizar PHP (solo se usa el que realmente ejecuta código sin errores)
# ---------------------------------------------------------------------------
function Test-Php {
    param([string]$exe)
    if (-not $exe -or -not (Test-Path $exe)) { return $false }
    & $exe -r "echo 1;" 2>$null | Out-Null
    return $LASTEXITCODE -eq 0
}

$php = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not (Test-Php $php)) { $php = $null }

if (-not $php) {
    foreach ($base in @("D:\Laragon\laragon\bin\php", "C:\Laragon\bin\php")) {
        $candidates = Get-ChildItem -Path $base -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue |
            Where-Object { $_.FullName -match "vs\d+.*x64" -or $_.FullName -match "win" } |
            Sort-Object FullName -Descending
        foreach ($c in $candidates) {
            if (Test-Php $c.FullName) { $php = $c.FullName; break }
        }
        if ($php) { break }
    }
}
if (-not $php) {
    throw "No se encontró un PHP funcional. Revisa los binarios en D:\Laragon\laragon\bin\php (o agrega PHP al PATH)."
}

# ---------------------------------------------------------------------------
# Localizar la IP local (la del adaptador con internet, p. ej. hotpost del celular)
# ---------------------------------------------------------------------------
$ipLocal = $null
try {
    $gw = Get-NetIPConfiguration -ErrorAction SilentlyContinue |
        Where-Object { $_.IPv4DefaultGateway -and $_.NetAdapter.Status -eq "Up" }
    foreach ($row in $gw) {
        foreach ($addr in $row.IPv4Address) {
            if ($addr.IPAddress -notmatch "^(169\.254|127\.)") { $ipLocal = $addr.IPAddress; break }
        }
        if ($ipLocal) { break }
    }
} catch { }

if (-not $ipLocal) {
    $ipLocal = (Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
        Where-Object { $_.IPAddress -notmatch "^(169\.254|127\.)" } |
        Select-Object -First 1).IPAddress
}
if (-not $ipLocal) { throw "No se pudo detectar la IP local. Verifica que esté conectado a una red." }

# ---------------------------------------------------------------------------
# Comprobar que el puerto esté libre (si está ocupado, PHP cierra solo)
# ---------------------------------------------------------------------------
$ocupado = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if ($ocupado) {
    $proc = Get-Process -Id $ocupado.OwningProcess -ErrorAction SilentlyContinue
    Write-Host "El puerto $Port ya está en uso por:" -ForegroundColor Yellow
    Write-Host "  PID $($ocupado.OwningProcess) - $($proc.ProcessName)" -ForegroundColor Yellow
    $choice = Read-Host "¿Cerrar ese proceso y continuar? (s/N)"
    if ($choice -match "^[sSyY]$") {
        if (-not $proc) { $proc = Get-Process -Id $ocupado.OwningProcess -ErrorAction SilentlyContinue }
        Stop-Process -Id $ocupado.OwningProcess -Force -ErrorAction SilentlyContinue
        Start-Sleep -Milliseconds 600
    } else {
        Write-Host "Abortando. Si el proceso es un php artisan serve anterior, ciérralo y vuelve a intentarlo." -ForegroundColor Cyan
        exit 1
    }
}

# ---------------------------------------------------------------------------
# Regla de firewall (opcional / requerida si el celular no conecta)
# ---------------------------------------------------------------------------
$RuleName = "CEFTE - puerto $Port"
$IsAdmin = ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if ($Firewall -or $IsAdmin) {
    try {
        if (-not (Get-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue)) {
            New-NetFirewallRule -DisplayName $RuleName -Direction Inbound -Protocol TCP -LocalPort $Port -Action Allow | Out-Null
            Write-Host "  Regla de firewall creada: $RuleName" -ForegroundColor Green
        }
    } catch {
        Write-Host "  No se pudo crear la regla de firewall automáticamente." -ForegroundColor Yellow
    }
}

# ---------------------------------------------------------------------------
# Funciones para ajustar/restaurar APP_URL en .env
# ---------------------------------------------------------------------------
if (-not (Test-Path $EnvFile)) { throw "No se encontró $EnvFile" }

function Get-EnvLines {
    $enc = New-Object System.Text.UTF8Encoding($false)
    return [System.IO.File]::ReadAllLines($EnvFile, $enc)
}

function Set-AppUrl([string]$Line) {
    $lines = Get-EnvLines
    $out = New-Object System.Collections.Generic.List[string]
    $done = $false
    foreach ($l in $lines) {
        if ($l -match "^APP_URL=") {
            $out.Add($Line)
            $done = $true
        } else {
            $out.Add($l)
        }
    }
    if (-not $done) { $out.Add($Line) }
    $enc = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllLines($EnvFile, $out.ToArray(), $enc)
    pushd $Root
    & $php artisan config:clear | Out-Null
    popd
}

function Restore-AppUrl {
    if (Test-Path $TempOriginal) {
        $original = (Get-Content $TempOriginal -Raw).Trim()
        if ($original -and $original -ne "") { Set-AppUrl $original }
        Remove-Item $TempOriginal -Force -ErrorAction SilentlyContinue
        Write-Host ""
        Write-Host "APP_URL restaurado a: $original" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "No había APP_URL respaldado para restaurar." -ForegroundColor Yellow
    }
}

# Autocuración: si una ejecución anterior se cerró de golpe y quedó APP_URL
# apuntando a una IP + puerto, restaurar el original antes de empezar.
if (Test-Path $TempOriginal) {
    $restore = (Get-Content $TempOriginal -Raw).Trim()
    if ($restore -and $restore -ne "") {
        Write-Host "Detecté APP_URL sin restaurar de una sesión anterior. Restaurando..." -ForegroundColor Yellow
        Set-AppUrl $restore
    }
}

# ---------------------------------------------------------------------------
# Guardar APP_URL original y apuntar a la IP local
# ---------------------------------------------------------------------------
$lineActual = (Get-EnvLines | Where-Object { $_ -match "^APP_URL=" } | Select-Object -First 1)
if (-not $lineActual) { $lineActual = "APP_URL=http://localhost" }

[System.IO.File]::WriteAllText($TempOriginal, $lineActual + "`n", (New-Object System.Text.UTF8Encoding($false)))

$NuevaUrl = "http://${ipLocal}:$Port"
Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "  CEFTE - acceso desde la red local"
Write-Host "--------------------------------------------------"
Write-Host "  IP de esta PC:       $ipLocal"
Write-Host "  URL para tu celular: $NuevaUrl"
Write-Host "  (La laptop y el celular deben estar en la misma red / hotspot)"
Write-Host "**************************************************"
Write-Host "  Para DETENER: pulsa cualquier tecla en esta ventana."
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

Set-AppUrl "APP_URL=$NuevaUrl"

# ---------------------------------------------------------------------------
# Levantar el servidor en un proceso aparte (oculto) con logs
# ---------------------------------------------------------------------------
if (-not (Test-Path (Join-Path $Root "storage\logs"))) {
    New-Item -ItemType Directory -Path (Join-Path $Root "storage\logs") -Force | Out-Null
}

$server = Start-Process -FilePath $php `
    -ArgumentList @("artisan", "serve", "--host=0.0.0.0", "--port=$Port") `
    -WorkingDirectory $Root `
    -WindowStyle Hidden `
    -RedirectStandardOutput $LogOut `
    -RedirectStandardError $LogErr `
    -PassThru

Start-Sleep -Milliseconds 1500

if ($server.HasExited) {
    Write-Host "El servidor PHP se cerró solo al arrancar. Motivo probable en el log:" -ForegroundColor Red
    if (Test-Path $LogErr) { Get-Content $LogErr -Tail 20 }
    if (Test-Path $LogOut) { Get-Content $LogOut -Tail 20 }
    Restore-AppUrl
    exit 1
}

Write-Host "Servidor en marcha (PID $($server.Id)). Log: $LogOut" -ForegroundColor Green
Write-Host "Abre $NuevaUrl desde tu celular." -ForegroundColor Green
Write-Host "@"
Write-Host "Esperando... pulsa CUALQUIER TECLA para detener el servidor y restaurar .env." -ForegroundColor Cyan

# Espera activa; se aborta con cualquier tecla o si el servidor muere solo.
try {
    while (-not $server.HasExited) {
        Start-Sleep -Milliseconds 400
        if ([Console]::KeyAvailable) {
            [Console]::ReadKey($true) | Out-Null
            break
        }
    }

    if ($server.HasExited) {
        Write-Host ""
        Write-Host "El servidor se cerró solo. Últimas líneas del log:" -ForegroundColor Red
        if (Test-Path $LogErr) { Get-Content $LogErr -Tail 20 }
        if (Test-Path $LogOut) { Get-Content $LogOut -Tail 20 }
    }
} finally {
    Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue
    Restore-AppUrl
    Write-Host ""
    Write-Host "Servidor detenido. Archivo .env restaurado." -ForegroundColor Green
}