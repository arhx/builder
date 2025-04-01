<?php
namespace Arhx\Builder;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Arhx\Builder\Types\Text;

class Builder{
    protected string $name;
    protected array $fields = [];
    protected array $config;
    protected QueryBuilder $query;

    public function __construct(string $tableName)
    {
        $config = config("builder.tables.{$tableName}", false);
        if ($config === false) {
            abort(404, "Table {$tableName} not found");
        }
        $this->config = $config;
        $this->name = $tableName;

        $query = DB::table($tableName);
        $this->setQuery($query);

        $setFields = [];
        foreach ($this->getConfig('fields', []) as $fieldName => $fieldConfig) {
            $typeClass = $fieldConfig['type'] ?? Text::class;
            $setFields[] = $typeClass::fromConfig($fieldName, $fieldConfig);
        }
        $this->setFields($setFields);
        $this->boot();
    }

    public function getConfig($key = null, $default = null)
    {
        return $key ? Arr::get($this->config, $key, $default) : $this->config;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function setQuery(QueryBuilder $query)
    {
        $this->query = $query;
    }
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }

    protected function boot()
    {
    }
}
