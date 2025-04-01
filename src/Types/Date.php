<?php
namespace Arhx\Builder\Types;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;


class Date extends Text
{
    protected array $extra = ['attributes' => ['type' => 'date']];

    public function getFormValue()
    {
        return $this->value ? Carbon::parse($this->value)->format('Y-m-d') : null;
    }

    public function getTableValue()
    {
        return $this->value ? Carbon::parse($this->value)->format('d.m.Y') : null;
    }

    public function migration(Blueprint $blueprint)
    {
        return $blueprint->date($this->getName());
    }
}
