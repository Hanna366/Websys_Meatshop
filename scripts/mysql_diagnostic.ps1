<#
mysql_diagnostic.ps1
Creates a diagnostic bundle for MySQL on Windows (read-only).
Run in an Administrator PowerShell session.
#>

param()

$timestamp = Get-Date -Format yyyyMMdd_HHmmss
$outDir = Join-Path $env:USERPROFILE "Documents\mysql_diagnostic_$timestamp"
New-Item -Path $outDir -ItemType Directory -Force | Out-Null

function Save-Text {
    param($Path, $Text)
    $Text | Out-File -FilePath $Path -Encoding UTF8
}

Write-Host "Output directory: $outDir"

# 1) Search common my.ini locations and extract datadir/log_error
$myIniPaths = @(
    'C:\xampp\mysql\bin\my.ini',
    'C:\xampp\mysql\my.ini',
    'C:\Program Files\MySQL\*\my.ini',
    'C:\ProgramData\MySQL\MySQL Server*\my.ini'
)

$foundMyInis = @()
foreach ($p in $myIniPaths) {
    try {
        $items = Get-ChildItem -Path $p -ErrorAction SilentlyContinue -Force
        foreach ($m in $items) { $foundMyInis += $m.FullName }
    } catch { }
}

Save-Text (Join-Path $outDir 'found_myini_paths.txt') $foundMyInis

$datadirs = @()
$logerrors = @()
foreach ($ini in $foundMyInis) {
    $lines = Get-Content $ini -ErrorAction SilentlyContinue
    Save-Text (Join-Path $outDir "myini_$(Split-Path $ini -Leaf).txt") $lines
    foreach ($line in $lines) {
        if ($line -match '^[\s]*datadir[\s]*=[\s]*(.+)$') { $datadirs += $matches[1].Trim('"') }
        if ($line -match '^[\s]*log_error[\s]*=[\s]*(.+)$') { $logerrors += $matches[1].Trim('"') }
    }
}

# 2) Check common XAMPP and ProgramData error log locations
$possibleLogs = @(
    'C:\xampp\mysql\data\mysql_error.log',
    'C:\xampp\mysql\data\*.err'
)
$possibleLogs += $logerrors

# Scan ProgramData and datadirs
try {
    $programDataErrs = Get-ChildItem -Path 'C:\ProgramData\MySQL' -Recurse -Include *.err -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName
    $possibleLogs += $programDataErrs
} catch { }

foreach ($d in $datadirs) {
    if ($d) {
        $g = Join-Path $d '*'
        try { $files = Get-ChildItem -Path $g -Include *.err, mysql_error.log -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName; $possibleLogs += $files } catch { }
    }
}

$possibleLogs = $possibleLogs | Where-Object { $_ } | Sort-Object -Unique
Save-Text (Join-Path $outDir 'found_log_candidates.txt') $possibleLogs

# 3) Save tail of each log found
foreach ($log in $possibleLogs) {
    if (Test-Path $log) {
        try {
            $tail = Get-Content -Path $log -Tail 400 -ErrorAction Stop
            $outFile = Join-Path $outDir ("log_" + ([IO.Path]::GetFileName($log)) )
            Save-Text $outFile $tail
        } catch { Save-Text (Join-Path $outDir 'log_read_errors.txt') ("Failed to read $log : $_") }
    }
}

# 4) Netstat for port 3306
$netstat = netstat -ano | Select-String ":3306" | Out-String
Save-Text (Join-Path $outDir 'netstat_3306.txt') $netstat

# 5) List processes listening on 3306 via Get-Process by PID
$pids = @()
foreach ($line in ($netstat -split "`n")) {
    if ($line -match '\s+(TCP|UDP)\s+[^\s]+:3306\s+[^\s]+\s+LISTENING\s+(\d+)') { $pids += $matches[2] }
    if ($line -match '\s+(TCP|UDP)\s+[^\s]+:3306\s+[^\s]+\s+(\d+)') { $pids += $matches[2] }
}
$pids = $pids | Sort-Object -Unique
$procInfo = @()
foreach ($pid in $pids) {
    try { $procInfo += (tasklist /FI "PID eq $pid" /V 2>$null) } catch { }
}
Save-Text (Join-Path $outDir 'processes_on_3306.txt') $procInfo

# 6) MySQL-related Windows services
$svc = Get-Service -Name "mysql*" -ErrorAction SilentlyContinue | Format-Table -AutoSize | Out-String
Save-Text (Join-Path $outDir 'mysql_services.txt') $svc

# 7) Event Viewer - Application errors mentioning MySQL or mysqld in the last 24 hours
try {
    $since = (Get-Date).AddDays(-1)
    $ev = Get-WinEvent -FilterHashtable @{LogName='Application';StartTime=$since} -ErrorAction SilentlyContinue | Where-Object { ($_.Message -match 'MySQL') -or ($_.Message -match 'mysqld') -or ($_.ProviderName -match 'MySQL') }
    $ev | Select-Object TimeCreated, ProviderName, Id, LevelDisplayName, Message | Format-List | Out-File (Join-Path $outDir 'eventviewer_mysql.txt') -Encoding UTF8
} catch { Save-Text (Join-Path $outDir 'eventviewer_errors.txt') $_ }

# 8) Disk free space for drives containing datadir
$drives = @()
foreach ($d in $datadirs) {
    if ($d -and (Test-Path $d)) { $drive = (Get-Item $d).PSDrive.Name; $drives += $drive }
}
$drives = $drives | Sort-Object -Unique
$diskInfos = @()
foreach ($drv in $drives) {
    $diskInfos += Get-PSDrive -Name $drv | Select-Object Name, Free, Used, @{Name='Total';Expression={$_.Used + $_.Free}} | Format-List | Out-String
}
Save-Text (Join-Path $outDir 'disk_space.txt') $diskInfos

# 9) Save mysqld binary locations (if present)
$mysqldPaths = @('C:\xampp\mysql\bin\mysqld.exe', 'C:\Program Files\MySQL\MySQL Server*\bin\mysqld.exe')
$foundMysqld = @()
foreach ($p in $mysqldPaths) {
    try { $foundMysqld += (Get-ChildItem -Path $p -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName) } catch { }
}
Save-Text (Join-Path $outDir 'found_mysqld_paths.txt') $foundMysqld

# 10) Summarize
$summary = @()
$summary += "Timestamp: $timestamp"
$summary += "Found my.ini count: $($foundMyInis.Count)"
$summary += "Found log candidates count: $($possibleLogs.Count)"
$summary += "Datadirs: $($datadirs -join ', ')"
$summary += "Mysqld paths: $($foundMysqld -join ', ')"
$summary += "Netstat entry lines: $($netstat.Length)"
Save-Text (Join-Path $outDir 'summary.txt') $summary

Write-Host "Diagnostic bundle created at: $outDir"
Write-Host "Please attach the files in that folder or paste the contents of 'summary.txt' and the most recent log file here."

# End
