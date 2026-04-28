# Backup hosts and add chop.localhost mapping if missing
$hosts = 'C:\Windows\System32\drivers\etc\hosts'
$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$backup = "$hosts.bak_$timestamp"
Copy-Item -Path $hosts -Destination $backup -Force
Write-Output "Backed up hosts to: $backup"
$pattern = '^\s*127\.0\.0\.1\s+chop\.localhost\s*$'
$exists = Select-String -Path $hosts -Pattern $pattern -Quiet
if (-not $exists) {
    Add-Content -Path $hosts -Value "`n127.0.0.1    chop.localhost"
    Write-Output 'Added mapping: 127.0.0.1    chop.localhost'
} else {
    Write-Output 'Mapping already present; no change made.'
}
Write-Output 'DONE'
