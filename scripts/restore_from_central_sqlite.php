<?php
// restore_from_central_sqlite.php
// Usage: php restore_from_central_sqlite.php [mysql_root_password]
$rootPass = $argv[1] ?? getenv('DB_PASSWORD') ?: '';
$sqlitePath = __DIR__ . '/../database/database.sqlite';
if (!file_exists($sqlitePath)) {
    echo "Central sqlite not found: $sqlitePath\n";
    exit(1);
}
$mysqlDsn = 'mysql:host=127.0.0.1;port=3306;';
try {
    $sqlite = new PDO('sqlite:' . $sqlitePath);
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $mysql = new PDO($mysqlDsn, 'root', $rootPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "Connection error: " . $e->getMessage() . PHP_EOL; exit(1);
}
$tenants = $sqlite->query("SELECT * FROM tenants")->fetchAll(PDO::FETCH_ASSOC);
if (!$tenants) { echo "No tenants in sqlite file\n"; exit(0); }

echo "Found " . count($tenants) . " tenants in sqlite backup.\n";
foreach ($tenants as $t) {
    $tenant_id = $t['tenant_id'] ?? null;
    if (!$tenant_id) continue;
    $db_name = $t['db_name'] ?? null;
    $domain = $t['domain'] ?? null;
    $business_name = $t['business_name'] ?: 'Unnamed Tenant';
    $business_email = $t['business_email'] ?: ($tenant_id . '@local');
    $business_phone = $t['business_phone'] ?? '';
    $business_address = $t['business_address'] ?: '[]';
    $subscription = $t['subscription'] ?: '[]';
    $plan = $t['plan'] ?? 'basic';
    $settings = $t['settings'] ?: '[]';
    $usage = $t['usage'] ?: '[]';
    $limits = $t['limits'] ?: '[]';
    $status = $t['status'] ?? 'active';
    $payment_status = $t['payment_status'] ?? 'paid';

    // check existing central row
    $stmt = $mysql->prepare("SELECT id, tenant_id, domain, db_name FROM meatshop_pos.tenants WHERE tenant_id = ?");
    $stmt->execute([$tenant_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $id = $row['id'];
        // update missing fields
        $updates = [];
        $params = [];
        if (empty($row['db_name']) && $db_name) { $updates[] = 'db_name = ?'; $params[] = $db_name; }
        if (empty($row['domain']) && $domain) { $updates[] = 'domain = ?'; $params[] = $domain; }
        if ($updates) {
            $params[] = $id;
            $sql = 'UPDATE meatshop_pos.tenants SET ' . implode(', ', $updates) . ' WHERE id = ?';
            $mysql->prepare($sql)->execute($params);
            echo "Updated tenant $tenant_id (id=$id): " . implode(', ', $updates) . "\n";
        } else {
            echo "Tenant $tenant_id exists (id=$id) - no update needed\n";
        }
    } else {
        // insert new row, ensure required JSON columns are valid
        $ins = $mysql->prepare("INSERT INTO meatshop_pos.tenants (tenant_id, domain, db_name, db_username, db_password, business_name, business_email, admin_name, admin_email, logo_path, business_phone, business_address, subscription, plan, plan_started_at, plan_ends_at, settings, `usage`, `limits`, status, payment_status, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
        $ins->execute([
            $tenant_id,
            $domain,
            $db_name,
            $t['db_username'] ?? null,
            $t['db_password'] ?? null,
            $business_name,
            $business_email,
            $t['admin_name'] ?? null,
            $t['admin_email'] ?? null,
            $t['logo_path'] ?? null,
            $business_phone,
            $business_address,
            $subscription,
            $plan,
            $t['plan_started_at'] ?? null,
            $t['plan_ends_at'] ?? null,
            $settings,
            $usage,
            $limits,
            $status,
            $payment_status
        ]);
        $newId = $mysql->lastInsertId();
        echo "Inserted tenant $tenant_id as id=$newId\n";
    }

    // ensure tenant DB exists
    if ($db_name) {
        $check = $mysql->prepare("SELECT schema_name FROM information_schema.schemata WHERE schema_name = ?");
        $check->execute([$db_name]);
        $exists = $check->fetch(PDO::FETCH_ASSOC);
        if ($exists) {
            echo "Tenant DB $db_name already exists.\n";
            continue;
        }
        // look for sqlite tenant file
        $candidates = [__DIR__ . "/../database/tenants/{$db_name}.sqlite", __DIR__ . "/../database/backups/restore_20260424_153929/{$db_name}.sqlite"];
        $found = null;
        foreach ($candidates as $cand) { if (file_exists($cand)) { $found = $cand; break; } }
        if ($found) {
            echo "Found sqlite for $db_name at $found - importing...\n";
            // call import script
            $cmd = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/import_sqlite_to_mysql.php') . ' ' . escapeshellarg($found) . ' ' . escapeshellarg($db_name) . ' ' . escapeshellarg($rootPass);
            echo "Running: $cmd\n";
            passthru($cmd, $rc);
            if ($rc === 0) echo "Imported $db_name from sqlite.\n"; else echo "Import of $db_name failed (rc=$rc).\n";
        } else {
            // search SQL dumps for CREATE DATABASE or USE lines referencing db_name
            $out = [];
            exec('powershell -Command "Select-String -Path \"database\\backups\\*.sql\" -Pattern \"'.$db_name.'\" -List | Select-Object -Expand Path"', $out);
            if ($out) {
                foreach ($out as $f) {
                    $f = trim($f);
                    echo "Found SQL referencing $db_name in $f - importing...\n";
                    // import by piping file
                    $cmd = '"C:\\xampp\\mysql\\bin\\mysql.exe" -u root -p"' . $rootPass . '" ' . escapeshellarg($db_name) . ' < ' . escapeshellarg($f);
                    // create DB first
                    $mysql->exec("CREATE DATABASE IF NOT EXISTS `".$db_name."` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    passthru($cmd, $rc);
                    if ($rc === 0) { echo "Imported SQL from $f into $db_name\n"; break; } else { echo "Import from $f failed (rc=$rc)\n"; }
                }
            } else {
                echo "No local backup found for tenant DB $db_name.\n";
            }
        }
    }
}

echo "Done processing tenants from central sqlite.\n";
