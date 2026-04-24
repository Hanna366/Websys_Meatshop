<#
xampp_innodb_recovery.ps1
Interactive script to back up XAMPP MySQL datadir, enable innodb_force_recovery,
attempt mysqldump, and (optionally) rebuild InnoDB system files.
Run as Administrator and review before running.
#>

param()

function Pause-Confirm($msg) {
    $r = Read-Host "$msg  (Y to continue / any other key to abort)"
    if ($r -ne 'Y' -and $r -ne 'y') { Write-Host 'Aborted by user.'; exit 1 }
}

# Paths (adjust if your XAMPP is installed elsewhere)
$XAMPP = 'C:\xampp'
$datadir = Join-Path $XAMPP 'mysql\data'
$myini_candidates = @(
    Join-Path $XAMPP 'mysql\bin\my.ini',
    Join-Path $XAMPP 'mysql\my.ini'
)
$mysqldump = Join-Path $XAMPP 'mysql\bin\mysqldump.exe'
$mysql = Join-Path $XAMPP 'mysql\bin\mysql.exe'
$backupRoot = Join-Path $env:USERPROFILE ('mysql_recovery_backup_' + (Get-Date -Format yyyyMMdd_HHmmss))

Write-Host "Datadir: $datadir"
Write-Host "Backup root will be: $backupRoot"

Pause-Confirm "Make sure XAMPP Control Panel MySQL is STOPPED. Continue?"

# 1) Kill any running mysqld processes
$procs = Get-Process -Name mysqld -ErrorAction SilentlyContinue
if ($procs) {
    Write-Host "Found running mysqld processes:"
    $procs | Format-Table Id, ProcessName, StartTime -AutoSize
    Pause-Confirm "Kill these mysqld processes?"
    $procs | ForEach-Object { Stop-Process -Id $_.Id -Force -ErrorAction SilentlyContinue }
    Start-Sleep -Seconds 2
}

# 2) Show port 3306 status
Write-Host "Port 3306 usage:"
netstat -ano | Select-String ':3306' | ForEach-Object { $_.ToString() }

# 3) Backup datadir
Write-Host "Creating backup directory and copying datadir..."
New-Item -Path $backupRoot -ItemType Directory -Force | Out-Null
$datadirBackup = Join-Path $backupRoot 'data_backup'
try {
    Copy-Item -Path $datadir -Destination $datadirBackup -Recurse -Force -ErrorAction Stop
    Write-Host "Datadir copied to $datadirBackup"
} catch {
    Write-Host "Failed to copy datadir: $_"
    exit 1
}

# 4) Backup my.ini and add innodb_force_recovery=1
$myini = $myini_candidates | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $myini) { Write-Host "Cannot find my.ini in expected XAMPP paths. Edit your my.ini manually and add innodb_force_recovery=1 under [mysqld]."; exit 1 }
$myiniBackup = Join-Path $backupRoot ('my_ini_backup_' + (Split-Path $myini -Leaf))
Copy-Item -Path $myini -Destination $myiniBackup -Force
Write-Host "Backed up my.ini to $myiniBackup"

# Add or update innodb_force_recovery line
$iniContent = Get-Content $myini -Raw
if ($iniContent -match '(?ms)^\s*\[mysqld\].*') {
    if ($iniContent -match '(?m)^\s*innodb_force_recovery\s*=') {
        $iniContent = $iniContent -replace '(?m)^\s*innodb_force_recovery\s*=.*','innodb_force_recovery=1'
    } else {
        $iniContent = $iniContent -replace '(?ms)^\s*\[mysqld\]','[mysqld]' + "`r`ninnodb_force_recovery=1"
    }
    Set-Content -Path $myini -Value $iniContent -Force
    Write-Host "Set innodb_force_recovery=1 in $myini"
} else {
    Write-Host "Unexpected my.ini format. Please add 'innodb_force_recovery=1' under [mysqld] manually."; exit 1
}

Pause-Confirm "Start XAMPP MySQL now (via Control Panel) and press Y when it's started (or press N to attempt starting here)?"
# At this point user can start via XAMPP. Wait for confirmation.
$r = Read-Host "If you started MySQL via XAMPP, type 'Y' to continue; type 'N' to try to start mysqld.exe here"
if ($r -eq 'N' -or $r -eq 'n') {
    # Try starting mysqld in background
    $mysqldExe = Join-Path $XAMPP 'mysql\bin\mysqld.exe'
    if (Test-Path $mysqldExe) {
        Start-Process -FilePath $mysqldExe -ArgumentList '--console' -WindowStyle Hidden
        Start-Sleep -Seconds 6
        Write-Host "Attempted to start mysqld.exe (check XAMPP Control Panel)."
    } else {
        Write-Host "Cannot find mysqld.exe at $mysqldExe. Start MySQL via XAMPP Control Panel then continue."
        Pause-Confirm "Started MySQL in XAMPP Control Panel?"
    }
} else {
    Write-Host "Continuing with assumed running MySQL."
}

# 5) Attempt mysqldump
if (-not (Test-Path $mysqldump)) { Write-Host "Cannot find mysqldump at $mysqldump. Adjust path and run manual dump."; exit 1 }
$outDump = Join-Path $backupRoot 'alldb_dump.sql'
Write-Host "Attempting to dump all databases to $outDump"
& $mysqldump -u root -p --all-databases --routines --events > $outDump
if ($LASTEXITCODE -ne 0) {
    Write-Host "mysqldump exited with code $LASTEXITCODE. You may need to increase innodb_force_recovery."
    $inc = Read-Host "Increase innodb_force_recovery to 2..6? Enter level (or press Enter to skip)"
    if ($inc -match '^[1-6]$') {
        (Get-Content $myini) -replace '(?m)^\s*innodb_force_recovery\s*=.*','innodb_force_recovery=' + $inc | Set-Content $myini
        Write-Host "Set innodb_force_recovery=$inc. Stop MySQL, then start it and re-run this script's dump step."
    } else {
        Write-Host "Skipping increment. Manual steps required."
    }
    exit 1
} else {
    Write-Host "Dump completed successfully: $outDump"
}

# 6) Prompt to rebuild InnoDB (rename system files)
Pause-Confirm "If dump succeeded, do you want to rebuild InnoDB system files now? This will rename ibdata1 and ib_logfile* (non-destructive but required)."
# Stop MySQL before renaming
Pause-Confirm "Make sure MySQL is STOPPED in XAMPP Control Panel. Continue to rename files?"

$filesToRename = @('ibdata1','ib_logfile0','ib_logfile1')
foreach ($f in $filesToRename) {
    $src = Join-Path $datadir $f
    if (Test-Path $src) {
        $dst = Join-Path $backupRoot ($f + '.bak')
        Rename-Item -Path $src -NewName (Split-Path $dst -Leaf) -Force
        Write-Host "Renamed $src -> $dst"
    } else {
        Write-Host "$src not found, skipping."
    }
}
# Remove ibtmp1 and ib_buffer_pool if present
$rm = @('ibtmp1','ib_buffer_pool')
foreach ($f in $rm) {
    $p = Join-Path $datadir $f
    if (Test-Path $p) { Remove-Item $p -Force -ErrorAction SilentlyContinue; Write-Host "Removed $p" }
}

# Remove recovery setting from my.ini
$iniContent = Get-Content $myini -Raw
$iniContent = $iniContent -replace '(?m)^\s*innodb_force_recovery\s*=.*\r?\n',''
Set-Content -Path $myini -Value $iniContent -Force
Write-Host "Removed innodb_force_recovery from $myini"

Write-Host "Now start MySQL via XAMPP Control Panel — it should recreate InnoDB files. After it starts, restore the dump if needed:" 
Write-Host "    Dump file path: "
Write-Host $outDump
Write-Host "Recovery script finished. Review the backup folder at:"
Write-Host $backupRoot
