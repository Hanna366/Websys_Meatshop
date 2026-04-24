<#
Switch XAMPP MySQL from port 3306 to 3307 and update project .env
Run this in an Administrator PowerShell:

  cd C:\Users\OWNER\Documents\webs\meatshop\scripts
  Start-Process powershell -Verb runAs -ArgumentList '-NoProfile','-ExecutionPolicy','Bypass','-File','"'$(Resolve-Path .\switch_xampp_mysql_to_3307.ps1)'"'

The script is non-destructive: it creates backups of any modified files.
#>

If (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Run this script in an elevated (Administrator) PowerShell."
    exit 1
}

$changes = @()

function Backup-File($path) {
    if (Test-Path $path) {
        $bak = "$path.bak.$((Get-Date).ToString('yyyyMMdd_HHmmss'))"
        Copy-Item -Path $path -Destination $bak -Force
        Write-Host "Backed up $path => $bak"
    }
}

# 1) Update my.ini files
$myIniFiles = @('C:\xampp\mysql\bin\my.ini','C:\xampp\mysql\my.ini')
foreach ($f in $myIniFiles) {
    if (Test-Path $f) {
        Backup-File $f
        (Get-Content $f) -replace '(^\s*port\s*=\s*)3306','$1' + '3307' | Set-Content $f -Force
        Write-Host "Updated port in $f"
        $changes += $f
    }
}

# 2) Update phpMyAdmin config if present
$pma = 'C:\xampp\phpMyAdmin\config.inc.php'
if (Test-Path $pma) {
    Backup-File $pma
    (Get-Content $pma) -replace "(['\"]port['\"]\s*=>\s*)3306",'$1' + '3307' | Set-Content $pma -Force
    (Get-Content $pma) -replace '(["\']port["\']\s*=>\s*)3306','$1' + '3307' | Set-Content $pma -Force
    Write-Host "Updated phpMyAdmin config: $pma"
    $changes += $pma
}

# 3) Update Laravel .env DB_PORT
$envFile = Join-Path (Split-Path -Parent (Resolve-Path '..\')) '.env'
if (-not (Test-Path $envFile)) { $envFile = 'C:\Users\OWNER\Documents\webs\meatshop\.env' }
if (Test-Path $envFile) {
    Backup-File $envFile
    (Get-Content $envFile) -replace '(^DB_PORT=)\d+','$1' + '3307' | Set-Content $envFile -Force
    Write-Host "Updated .env DB_PORT to 3307"
    $changes += $envFile
} else {
    Write-Host "Warning: .env not found at expected path: $envFile"
}

# 4) Inform user and restart XAMPP Control Panel (for user to Start MySQL)
Write-Host "\nCompleted edits. Files changed:`n" -NoNewline
foreach ($c in $changes) { Write-Host " - $c" }

Write-Host "\nLaunching XAMPP Control Panel (run it as Administrator if UAC prompts). Use the Control Panel 'Start' button for MySQL now." -ForegroundColor Yellow
Start-Process 'C:\xampp\xampp-control.exe' -Verb runAs

# 5) Clear Laravel caches
try {
    $proj = 'C:\Users\OWNER\Documents\webs\meatshop'
    if (Test-Path $proj) {
        Push-Location $proj
        Write-Host "Clearing Laravel config/cache..."
        php artisan config:clear 2>$null | Out-Null
        php artisan cache:clear 2>$null | Out-Null
        Pop-Location
    }
} catch {
    Write-Host "Could not run artisan commands. Make sure PHP is in PATH." -ForegroundColor Red
}

Write-Host "Script finished. After XAMPP shows MySQL running, run: php artisan migrate:status and verify the app." -ForegroundColor Green
