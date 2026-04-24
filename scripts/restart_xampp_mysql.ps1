$mysqld='C:\xampp\mysql\bin\mysqld.exe'
if(-not (Test-Path $mysqld)){
    Write-Output 'mysqld.exe not found at C:\xampp\mysql\bin'
    exit 2
}

try {
    $p = Start-Process -FilePath $mysqld -ArgumentList '--defaults-file=C:\xampp\mysql\bin\my.ini' -WindowStyle Hidden -PassThru -ErrorAction Stop
    Write-Output "Started mysqld process Id=$($p.Id)"
} catch {
    Write-Output "Failed to start mysqld: $($_.Exception.Message)"
}

Start-Sleep -Seconds 4

try {
    Get-Process -Name mysqld,mysqld64 -ErrorAction SilentlyContinue | Select-Object ProcessName,Id,CPU | Format-Table -AutoSize
} catch {
    Write-Output 'No mysqld process found'
}

if (Test-Path 'C:\xampp\mysql\bin\mysqladmin.exe') {
    try {
        & 'C:\xampp\mysql\bin\mysqladmin.exe' -u root ping 2>&1 | Write-Output
    } catch {
        Write-Output 'mysqladmin ping failed or returned non-zero'
    }
} else {
    Write-Output 'mysqladmin not found'
}
