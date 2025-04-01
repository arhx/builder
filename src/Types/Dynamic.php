<?php
namespace Arhx\Builder\Types;

class Dynamic extends Basic{
    protected $handler;
    public function __construct(string $name, $handler)
    {
        $this->setName($name);
        $this->handler = $handler;
    }
    public function extractValue($item): void{
        $this->setValue(call_user_func($this->handler, $item));
    }
}
