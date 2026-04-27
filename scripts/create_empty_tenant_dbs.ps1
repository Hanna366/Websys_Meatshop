$schemaPath = 'C:\Users\OWNER\tenant_schema.sql'
$rootPass = 'YourNewStrongPassword'
$names = @('tenant_missing_1','tenant_missing_2','tenant_missing_3','tenant_missing_4')
if (-not (Test-Path $schemaPath)) { Write-Error "Schema file not found: $schemaPath"; exit 1 }
foreach ($name in $names) {
    Write-Host "Creating database $name"
    & 'C:\xampp\mysql\bin\mysql.exe' -u root -p"$rootPass" -e "CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    $out = "C:\Users\OWNER\tenant_schema_${name}.sql"
    (Get-Content $schemaPath -Raw) -replace 'Database: tenant_00f6a9008849','Database: '+$name | Out-File -FilePath $out -Encoding UTF8
    (Get-Content $out -Raw) -replace 'USE `tenant_00f6a9008849`','USE `'+$name+'`' | Out-File -FilePath $out -Encoding UTF8
    Write-Host "Importing schema into $name"
    & 'C:\xampp\mysql\bin\mysql.exe' -u root -p"$rootPass" $name < $out
}
Write-Host 'Done'