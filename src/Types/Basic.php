<?php
namespace Arhx\Builder\Types;

use Arhx\Builder\Helpers;
use Illuminate\Support\Str;

abstract class Basic{
    protected string $name;
    protected $value;
    protected array $extra = [];
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }


    public function getValue()
    {
        return $this->value ?? null;
    }

    public function getTableValue()
    {
        $value = $this->getValue();
        $nowrap = $this->extra['nowrap'] ?? false;
        return $nowrap && $value ? Helpers::nowrap($value) : $value;
    }
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getLabel(): string
    {
        $label = $this->extra['label'] ?? Str::ucfirst(str_replace('_', ' ', $this->name));
        return __($label);
    }

    static function fromConfig($name, array $config): self
    {
        $value = $config['value'] ?? null;
        $type = $config['type'] ?? Text::class;
        $item = new $type($value, $config);
        $item->setName($name);
        return $item;
    }
    public function getLazySource()
    {
        return false;
    }

    public function extractValue($item): void
    {
        $name = $this->getName();
        $this->setValue($item->$name ?? null);
    }
}
