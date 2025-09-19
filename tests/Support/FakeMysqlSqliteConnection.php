<?php

namespace Tests\Support;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\SQLiteConnection;

class FakeMysqlSqliteConnection extends SQLiteConnection
{
    /**
     * @var array<int, string>
     */
    private array $captured = [];

    public function getDriverName()
    {
        return 'mysql';
    }

    private function transformQuery(string $query): string
    {
        $this->captured[] = $query;

        $query = preg_replace(
            "/JOIN\s+JSON_TABLE\(products\.attributes,\s*'\\$\\[\\*]'\s*COLUMNS\(attr_key\s+VARCHAR\(191\)\s+PATH\s+'\\$\\.key',\s*attr_value\s+VARCHAR\(191\)\s+PATH\s+'\\$\\.value',\s*attr_translations\s+JSON\s+PATH\s+'\\$\\.translations'\)\)\s+AS\s+attr\s+ON\s+TRUE/i",
            "JOIN json_each(COALESCE(products.attributes, '[]')) AS attr ON 1=1",
            $query
        ) ?? $query;

        $replacements = [
            'attr.attr_key' => "json_extract(attr.value, '$.key')",
            'attr.attr_value' => "json_extract(attr.value, '$.value')",
            'attr.attr_translations' => "json_extract(attr.value, '$.translations')",
            'JSON_EXTRACT' => 'json_extract',
        ];

        $query = str_replace(array_keys($replacements), array_values($replacements), $query);

        return $query;
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return parent::select($this->transformQuery($query), $bindings, $useReadPdo);
    }

    public function affectingStatement($query, $bindings = [])
    {
        return parent::affectingStatement($this->transformQuery($query), $bindings);
    }

    public function statement($query, $bindings = [])
    {
        return parent::statement($this->transformQuery($query), $bindings);
    }

    public function unprepared($query)
    {
        return parent::unprepared($this->transformQuery($query));
    }

    /**
     * @return array<int, string>
     */
    public function capturedQueries(): array
    {
        return $this->captured;
    }

    public function flushCapturedQueries(): void
    {
        $this->captured = [];
    }
}

class FakeMysqlConnectionResolver implements ConnectionResolverInterface
{
    public function __construct(private SQLiteConnection $connection)
    {
    }

    public function connection($name = null)
    {
        return $this->connection;
    }

    public function getDefaultConnection()
    {
        return 'mysql';
    }

    public function setDefaultConnection($name)
    {
        // No-op for testing purposes.
    }
}
