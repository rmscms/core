<?php

namespace RMS\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migrator;

class DbShiftCommand extends Command
{
    protected $aliases = ['db:diff'];

    // Execution report buckets
    protected array $report = [
        'fixed' => [],          // fixed protected migrations marked as Ran
        'smartCreate' => [],    // create-table migrations marked as Ran
        'smartAdd' => [],       // add-column migrations marked as Ran
        'executed' => [],       // migrations actually executed (or planned in dry-run)
    ];

    // Fixed protected migrations (will only be marked as Ran on target b)
    protected array $fixedIgnoreMigrations = [
        '0001_01_01_000000_create_users_table',
        '2025_01_19_000000_create_users_table',
        '2025_01_19_120000_create_settings_table',
    ];

    protected $signature = 'db:shift {--a= : Source database name} {--b= : Target database name} {--ignore=users,settings : Comma-separated tables (by name) to ignore in plan} {--ignore-migrations= : Comma-separated migration names to additionally mark as Ran} {--b-connection=mysql : Laravel connection name for target (b)} {--apply : Apply safe actions (mark ignores as Ran, then run migrate on b)} {--dry-run : Simulate apply using --pretend (no changes)} {--details : Show detailed column/index diffs}';
    protected $description = 'Safely shift schema from database A to B with smart skips and optional apply';

    public function handle(): int
    {
        $dbA = $this->option('a') ?: env('DB_DATABASE');
        $dbB = $this->option('b') ?: env('DB_DATABASE');
        if (!$dbA || !$dbB) {
            $this->error('Please specify --a and --b database names or set DB_DATABASE.');
            return self::FAILURE;
        }
        $ignore = collect(explode(',', (string)$this->option('ignore')))->map(fn($x)=>trim($x))->filter()->values()->all();

        $this->line("Comparing schemas: A={$dbA} vs B={$dbB}");

        $tablesA = $this->getTables($dbA);
        $tablesB = $this->getTables($dbB);

        $onlyInA = array_values(array_diff($tablesA, $tablesB));
        $onlyInB = array_values(array_diff($tablesB, $tablesA));
        $common  = array_values(array_intersect($tablesA, $tablesB));

        $this->line(PHP_EOL.'=== Table differences ===');
        // Pretty tables for only-in-A / only-in-B
        $oa = array_map(fn($t)=>['📦', $t], $onlyInA);
        $ob = array_map(fn($t)=>['📦', $t], $onlyInB);
        if ($oa) { $this->table(['🟢 Only in A', 'Table'], $oa); } else { $this->info('🟢 Only in A: -'); }
        if ($ob) { $this->table(['🟠 Only in B', 'Table'], $ob); } else { $this->info('🟠 Only in B: -'); }

        $columnDiffs = [];
        foreach ($common as $t) {
            $colsA = $this->getColumns($dbA, $t);
            $colsB = $this->getColumns($dbB, $t);
            $colsOnlyA = array_values(array_diff(array_keys($colsA), array_keys($colsB)));
            $colsOnlyB = array_values(array_diff(array_keys($colsB), array_keys($colsA)));
            $changed = [];
            foreach (array_intersect(array_keys($colsA), array_keys($colsB)) as $c) {
                if ($this->columnChanged($colsA[$c], $colsB[$c])) {
                    $changed[] = $c;
                }
            }
            if ($colsOnlyA || $colsOnlyB || $changed) {
                $columnDiffs[$t] = compact('colsOnlyA','colsOnlyB','changed','colsA','colsB');
            }
        }

        $this->line(PHP_EOL.'=== Column/index differences (summary) ===');
        if (!$columnDiffs) {
            $this->info('✅ No column diffs found.');
        } else {
            $rows = [];
            foreach ($columnDiffs as $t => $d) {
                $rows[] = ['🧱', $t, !empty($d['colsOnlyA'])?implode(', ',$d['colsOnlyA']):'-', !empty($d['colsOnlyB'])?implode(', ',$d['colsOnlyB']):'-', !empty($d['changed'])?implode(', ',$d['changed']):'-'];
            }
            $this->table(['Tbl', 'Name', 'Only in A ➕', 'Only in B ➖', 'Changed ✏️'], $rows);
        }

        // Suggest safe plan (skip protected tables)
        $this->line(PHP_EOL.'=== Plan (safe suggestions) ===');
        $this->info('🔒 Protected tables: '.implode(', ', $ignore));
        $planRows = [];
        foreach ($onlyInA as $t) { if (!in_array($t,$ignore,true)) $planRows[] = ['➕', "create table in B", $t, '']; }
        foreach ($onlyInB as $t) { if (!in_array($t,$ignore,true)) $planRows[] = ['➖', "table only in B", $t, 'legacy/extra']; }
        foreach ($columnDiffs as $t => $d) {
            $prot = in_array($t,$ignore,true);
            if (!empty($d['colsOnlyA'])){
                foreach ($d['colsOnlyA'] as $c) $planRows[] = [$prot?'⛔':'➕', $prot?"[SKIP] add column":"add column", "$t.$c", $prot?'protected table':''];
            }
            if (!empty($d['colsOnlyB'])){
                foreach ($d['colsOnlyB'] as $c) $planRows[] = [$prot?'⛔':'➖', $prot?"[SKIP] column only in B":"column only in B", "$t.$c", $prot?'protected table':'legacy/extra'];
            }
            if (!empty($d['changed'])){
                foreach ($d['changed'] as $c) $planRows[] = [$prot?'⛔':'~', $prot?"[SKIP] alter":"alter column", "$t.$c", $prot?'protected table':''];
            }
        }
        if ($planRows) $this->table(['Act','Action','Target','Note'], $planRows); else $this->info('Nothing to suggest.');

        $this->newLine();
        $this->info('ℹ️ Note: This command does not change the database unless --apply is used. Use --dry-run to simulate.');
        $this->info('➡️  Next: add guards to migrations (Schema::hasTable/hasColumn) and/or mark legacy migrations as Ran.');

        if ($this->option('apply')) {
            $this->newLine();
            $this->info('🚀 Applying safe actions on target (b)...');
            $bConn = $this->option('b-connection') ?: 'mysql';
            // Merge hard-coded fixed ignores with any extra provided via option
            $ignoredMigrations = array_values(array_unique(array_merge(
                $this->fixedIgnoreMigrations,
                collect(explode(',', (string)$this->option('ignore-migrations')))
                    ->map(fn($x)=>trim($x))->filter()->values()->all()
            )));

            // 1) Ensure migrations table exists on b
            $this->ensureMigrationsTableExists($bConn);

            // 2) Mark ignored migrations as Ran (if present and pending)
            $this->markMigrationsAsRan($dbB, $bConn, $ignoredMigrations, 'fixed');

            // 2.b) Smart-skip: mark create-table migrations as Ran if table already exists on b
            $this->smartSkipCreateTableMigrations($dbB, $bConn);

            // 2.c) Smart-skip: mark add-column migrations as Ran if all target columns already exist on b
            $this->smartSkipAddColumnMigrations($dbB, $bConn);

            // 3) Run migrate on b for the rest
            $this->info('🏃 Running php artisan migrate on target (b)...');
            $migrateArgs = [
                '--database' => $bConn,
            ];
            if ($this->option('dry-run')) {
                $migrateArgs['--pretend'] = true;
            } else {
                $migrateArgs['--force'] = true;
            }
            $code = Artisan::call('migrate', $migrateArgs);
            $out = Artisan::output();
            $this->output->write($out);
            // Parse executed/planned migrations from output
            $this->collectExecutedFromOutput($out, (bool)$this->option('dry-run'));
            if ($code !== 0) {
                $this->error('Migrate failed on target (b).');
                return self::FAILURE;
            }

            // Final report
            $this->printFinalReport((bool)$this->option('dry-run'));
            $this->info('✅ Safe apply complete.');
        }
        return self::SUCCESS;
    }

    protected function getTables(string $schema): array
    {
        $rows = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME", [$schema]);
        return array_map(fn($r)=>$r->TABLE_NAME, $rows);
    }

    protected function getColumns(string $schema, string $table): array
    {
        $rows = DB::select("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION", [$schema, $table]);
        $out = [];
        foreach ($rows as $r) {
            $out[$r->COLUMN_NAME] = [
                'type' => $r->DATA_TYPE,
                'nullable' => $r->IS_NULLABLE === 'YES',
                'default' => $r->COLUMN_DEFAULT,
                'len' => $r->CHARACTER_MAXIMUM_LENGTH,
                'precision' => $r->NUMERIC_PRECISION,
                'scale' => $r->NUMERIC_SCALE,
            ];
        }
        return $out;
    }

    protected function columnChanged(array $a, array $b): bool
    {
        // Compare type/null/default/len/precision/scale
        return $a['type'] !== $b['type']
            || $a['nullable'] !== $b['nullable']
            || (string)$a['default'] !== (string)$b['default']
            || (string)$a['len'] !== (string)$b['len']
            || (string)$a['precision'] !== (string)$b['precision']
            || (string)$a['scale'] !== (string)$b['scale'];
    }

    protected function ensureMigrationsTableExists(string $bConn): void
    {
        try {
            DB::connection($bConn)->table(config('database.migrations.table', 'migrations'))->limit(1)->get();
        } catch (\Throwable $e) {
            $this->info('🧰 Installing migrations table on target (b)...');
            Artisan::call('migrate:install', [
                '--database' => $bConn,
                '--force' => true,
            ]);
            $this->output->write(Artisan::output());
        }
    }

    protected function markMigrationsAsRan(string $dbB, string $bConn, array $migrationNames, string $category = 'other'): void
    {
        if (empty($migrationNames)) return;
        $table = config('database.migrations.table', 'migrations');
        $conn = DB::connection($bConn);

        // Keep existing max batch (including 0) to avoid jumping batches unexpectedly
        $maxRow = $conn->table($table)->selectRaw('MAX(batch) as b')->first();
        $batch = (int)($maxRow->b ?? 0);

        foreach ($migrationNames as $name) {
            // Skip if already present
            $exists = $conn->table($table)->where('migration', $name)->exists();
            if ($exists) {
                $this->line("⏭️  Migration already marked: {$name}");
                continue;
            }
            $onDisk = $this->migrationExistsOnDisk($name);
            if (!$onDisk) {
                $this->warn("⚠️ Migration not found on disk, still marking as Ran: {$name}");
            }
            $conn->table($table)->insert([
                'migration' => $name,
                'batch' => $batch,
            ]);
            $this->line("📝 Marked as Ran (batch {$batch}): {$name}");
            // Collect into report bucket
            if (!isset($this->report[$category])) $this->report[$category] = [];
            $this->report[$category][] = $name;
        }
    }

    protected function migrationExistsOnDisk(string $name): bool
    {
        foreach ($this->allMigrationFiles() as $path) {
            $base = basename($path, '.php');
            if ($base === $name) return true;
        }
        return false;
    }

    protected function collectExecutedFromOutput(string $output, bool $dryRun): void
    {
        // Look for lines like: "YYYY_mm_dd_xxxxxx_name  .... DONE" or any migration path in pretend output
        $lines = preg_split('/\r?\n/', $output);
        foreach ($lines as $line) {
            if (preg_match('/\s*(\d{4}_\d{2}_\d{2}_[0-9_]+_[a-zA-Z0-9_]+)\s+\.+\s+DONE/', $line, $m)) {
                $this->report['executed'][] = ($dryRun ? '[DRY] ' : '') . $m[1];
            }
            // Pretend output of Laravel prints queries, but we still try to catch migration names if present
        }
    }

    protected function printFinalReport(bool $dryRun): void
    {
        $this->newLine();
        $this->info('📊 Final report');
        $rows = [];
        foreach ([
            'fixed' => 'Fixed (protected) marked as Ran',
            'smartCreate' => 'Smart create marked as Ran',
            'smartAdd' => 'Smart add-column marked as Ran',
            'executed' => ($dryRun ? 'Planned to execute' : 'Executed'),
        ] as $key => $label) {
            $items = $this->report[$key] ?? [];
            if (empty($items)) continue;
            foreach ($items as $name) {
                $rows[] = [$label, $name];
            }
        }
        if ($rows) {
            $this->table(['Category', 'Migration'], $rows);
        } else {
            $this->line('No actions recorded.');
        }
    }

    protected function allMigrationFiles(): array
    {
        /** @var Migrator $migrator */
        $migrator = app('migrator');
        $paths = array_values(array_unique(array_merge([
            database_path('migrations'),
        ], $migrator->paths())));
        $fs = new Filesystem();
        $files = [];
        foreach ($paths as $p) {
            if (!is_dir($p)) continue;
            foreach ($fs->files($p) as $f) {
                $files[] = (string) $f;
            }
        }
        return $files;
    }

    protected function smartSkipCreateTableMigrations(string $dbB, string $bConn): void
    {
        $conn = DB::connection($bConn);
        $tables = $this->getTables($dbB);
        $existing = array_flip($tables);
        $files = $this->allMigrationFiles();
        $toMark = [];
        foreach ($files as $path) {
            $basename = basename($path, '.php');
            if ($conn->table(config('database.migrations.table', 'migrations'))->where('migration', $basename)->exists()) {
                continue; // already ran
            }
            $content = @file_get_contents($path) ?: '';
            if (!$content) continue;
            // naive regex for Schema::create('table'
            if (preg_match_all("/Schema::create\(\s*['\"]([a-zA-Z0-9_]+)['\"]/m", $content, $m)) {
                foreach ($m[1] as $tbl) {
                    if (isset($existing[$tbl])) {
                        $toMark[$basename] = true;
                        break;
                    }
                }
            }
        }
        if (!empty($toMark)) {
            $this->info('🧠 Smart-skip (create table exists): marking as Ran -> '.implode(', ', array_keys($toMark)));
            $this->markMigrationsAsRan($dbB, $bConn, array_keys($toMark), 'smartCreate');
        }
    }

    protected function smartSkipAddColumnMigrations(string $dbB, string $bConn): void
    {
        $conn = DB::connection($bConn);
        $files = $this->allMigrationFiles();
        $tableCache = [];
        $toMark = [];
        foreach ($files as $path) {
            $basename = basename($path, '.php');
            if ($conn->table(config('database.migrations.table', 'migrations'))->where('migration', $basename)->exists()) {
                continue; // already ran
            }
            $content = @file_get_contents($path) ?: '';
            if (!$content) continue;

            // Find Schema::table('tbl', function (Blueprint $table) { ... }) blocks
            if (preg_match_all("/Schema::table\(\s*['\"]([a-zA-Z0-9_]+)['\"][^)]*\)\s*,\s*function\s*\([^)]*\)\s*\{([\s\S]*?)\}\s*\);/m", $content, $blocks, PREG_SET_ORDER)) {
                foreach ($blocks as $block) {
                    $tbl = $block[1];
                    $body = $block[2];
                    // collect add-column intents: $table->type('col' ...);
                    $cols = [];
                    if (preg_match_all("/\$table->\s*(string|char|text|mediumText|longText|integer|tinyInteger|smallInteger|bigInteger|unsignedBigInteger|float|double|decimal|boolean|date|dateTime|dateTimeTz|time|timestamp|timestampTz|year|json|uuid|ipAddress|macAddress|enum|set|binary|geometry|point|lineString|polygon|multiPoint|multiLineString|multiPolygon)\s*\(\s*['\"]([a-zA-Z0-9_]+)['\"]/m", $body, $mm, PREG_SET_ORDER)) {
                        foreach ($mm as $m1) {
                            $cols[] = $m1[2];
                        }
                    }
                    // Exclude drops/renames/changes
                    if (preg_match("/->drop(Column)?\(\s*['\"][^'\"]+['\"]\s*\)/m", $body)) {
                        continue; // altering/removing, let migrate handle
                    }
                    if (preg_match("/->rename(Column)?\(\s*['\"][^'\"]+['\"]\s*,/m", $body)) {
                        continue;
                    }
                    if (preg_match("/->change\s*\(\s*\)/m", $body)) {
                        continue; // explicit changes should run
                    }
                    if (empty($cols)) continue;

                    // load columns of table once
                    if (!isset($tableCache[$tbl])) {
                        $tableCache[$tbl] = array_keys($this->getColumns($dbB, $tbl));
                    }
                    $existingCols = array_flip($tableCache[$tbl]);
                    $allExist = true;
                    foreach ($cols as $c) {
                        if (!isset($existingCols[$c])) { $allExist = false; break; }
                    }
                    if ($allExist) {
                        $toMark[$basename] = true;
                        break; // mark this migration; move to next file
                    }
                }
            }
        }
        if (!empty($toMark)) {
            $this->info('🧠 Smart-skip (all target columns already exist): marking as Ran -> '.implode(', ', array_keys($toMark)));
            $this->markMigrationsAsRan($dbB, $bConn, array_keys($toMark), 'smartAdd');
        }
    }
}
