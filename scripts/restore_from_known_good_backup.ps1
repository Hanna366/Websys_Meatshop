<#
Restore XAMPP MySQL datadir from known-good repository backup.
Run this script in an Administrator PowerShell from the repository root.
Usage (Admin PowerShell):
  cd C:\Users\OWNER\Documents\webs\meatshop
  powershell -ExecutionPolicy Bypass -File .\scripts\restore_from_known_good_backup.ps1
This script is non-destructive: it copies the current datadir to a timestamped backup before restoring.
#>

# Configuration
$TS = (Get-Date).ToString('yyyyMMdd_HHmmss')
$RepoRoot = Split-Path -Parent $PSScriptRoot
$XAMPP_Data = 'C:\xampp\mysql\data'
$RepoBackup = Join-Path $RepoRoot 'database\mysql_data_backup_20260421'
$AltBackup = 'C:\xampp\mysql\backup'
If (Test-Path $RepoBackup) {
    $BACKUP_SRC = $RepoBackup
    Write-Host "Using repository backup: $BACKUP_SRC"
} elseif (Test-Path $AltBackup) {
    $BACKUP_SRC = $AltBackup
    Write-Host "Repository backup not found — using XAMPP local backup: $BACKUP_SRC"
} else {
    Write-Error "No backup source found. Checked: $RepoBackup and $AltBackup. Aborting."
    exit 1
}
$USER_BACKUP = Join-Path $env:USERPROFILE "mysql_datadir_backup_$TS"

# Ensure running as Admin
If (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "This script must be run in an Administrator PowerShell."
    exit 1
}

Write-Host "Timestamp: $TS"
Write-Host "Repo backup source: $BACKUP_SRC"
Write-Host "XAMPP datadir: $XAMPP_Data"
Write-Host "User backup destination: $USER_BACKUP"

# Stop mysql service or mysqld process
Try {
    Stop-Service -Name mysql -Force -ErrorAction Stop
    Write-Host "Stopped 'mysql' service"
} Catch {
    Write-Host "Service 'mysql' not found or not running; attempting to stop mysqld process"
    Get-Process mysqld -ErrorAction SilentlyContinue | ForEach-Object { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue }
}

# Backup current datadir
If (Test-Path $XAMPP_Data) {
    New-Item -ItemType Directory -Path $USER_BACKUP -Force | Out-Null
    Write-Host "Copying current datadir to $USER_BACKUP (this may take a while)"
    robocopy $XAMPP_Data $USER_BACKUP /MIR /COPYALL /R:3 /W:5 | Out-Null
    Write-Host "Datadir backup complete"
} Else {
    Write-Host "No existing datadir found at $XAMPP_Data"
}

# Move existing datadir out-of-the-way (rename)
If (Test-Path $XAMPP_Data) {
    $archived = "$XAMPP_Data`_ORIG_$TS"
    Write-Host "Renaming existing datadir to $archived"
    Rename-Item -Path $XAMPP_Data -NewName (Split-Path $archived -Leaf)
}

# Recreate datadir folder
New-Item -ItemType Directory -Path $XAMPP_Data -Force | Out-Null

# Validate source backup exists
If (-not (Test-Path $BACKUP_SRC)) {
    Write-Error "Backup source not found: $BACKUP_SRC. Aborting."
    exit 1
}

# Copy backup into XAMPP datadir
Write-Host "Restoring files from $BACKUP_SRC to $XAMPP_Data"
Copy-Item -Path (Join-Path $BACKUP_SRC '*') -Destination $XAMPP_Data -Recurse -Force
Write-Host "Restore copy complete"

# Try to start mysql service, otherwise start mysqld.exe
Try {
    Start-Service -Name mysql -ErrorAction Stop
    Start-Sleep -Seconds 3
    Write-Host "Started 'mysql' service"
} Catch {
    Write-Host "Could not start 'mysql' service; launching mysqld.exe in background"
    Start-Process -FilePath 'C:\xampp\mysql\bin\mysqld.exe' -ArgumentList '--console' -WorkingDirectory 'C:\xampp\mysql\bin' -WindowStyle Hidden
    Start-Sleep -Seconds 5
}

# Quick verification
$checkOut = Join-Path $env:USERPROFILE "mysql_start_check_$TS.txt"
& 'C:\xampp\mysql\bin\mysql.exe' -u root -e "SHOW DATABASES;" 2>&1 | Tee-Object -FilePath $checkOut
Write-Host "Verification output saved to $checkOut"

Write-Host "Done. If MySQL failed to start, check C:\xampp\mysql\data\mysql_error.log and share the last 50 lines."