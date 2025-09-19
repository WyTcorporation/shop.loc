<?php

namespace Tests\Support;

use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\SQLiteConnection;

class FakePgsqlSqliteConnection extends SQLiteConnection
{
    /**
     * @var array<int, string>
     */
    private array $captured = [];

    private function transformQuery(string $query): string
    {
        $this->captured[] = $query;

        $query = str_replace('jsonb_each_text', 'json_each', $query);
        $query = preg_replace('/::jsonb/', '', $query) ?? $query;
        $query = str_ireplace('ilike', 'LIKE', $query);

        $query = $this->replaceArrowOperators($query);

        $needle = <<<'SQL'
CONCAT('$."', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name_translations), '$[0]')), '"')
SQL;
        $replacement = <<<'SQL'
'$."' || COALESCE((SELECT key FROM json_each(name_translations) LIMIT 1), '') || '"'
SQL;

        $query = str_replace($needle, $replacement, $query);
        $query = $this->stripJsonUnquote($query);

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

    private function stripJsonUnquote(string $query): string
    {
        $needle = 'JSON_UNQUOTE(';

        while (($start = strpos($query, $needle)) !== false) {
            $offset = $start + strlen($needle);
            $depth = 1;

            for ($i = $offset, $length = strlen($query); $i < $length; $i++) {
                $char = $query[$i];

                if ($char === '(') {
                    $depth++;
                } elseif ($char === ')') {
                    $depth--;

                    if ($depth === 0) {
                        $inner = substr($query, $offset, $i - $offset);
                        $query = substr($query, 0, $start) . $inner . substr($query, $i + 1);
                        break;
                    }
                }
            }

            if ($depth !== 0) {
                break;
            }
        }

        return $query;
    }

    private function replaceArrowOperators(string $query): string
    {
        $query = preg_replace_callback(
            "/COALESCE\(name_translations\s*,\s*'\{\}'\)\s*->>\s*'([^']+)'/",
            fn ($matches) => "json_extract(COALESCE(name_translations, '{}'), '$.\"" . $matches[1] . "\"')",
            $query
        ) ?? $query;

        $query = preg_replace_callback(
            "/name_translations\s*->>\s*'([^']+)'/",
            fn ($matches) => "json_extract(name_translations, '$.\"" . $matches[1] . "\"')",
            $query
        ) ?? $query;

        return $query;
    }
}

class FakePgsqlConnectionResolver implements ConnectionResolverInterface
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
        return 'pgsql';
    }

    public function setDefaultConnection($name)
    {
        // No-op for testing.
    }
}
