<?php
namespace Arhx\Builder\Types;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\HtmlString;

class Datetime extends Text
{
    protected array $extra = ['attributes' => ['type' => 'datetime-local'], 'nowrap' => true];

    public function getFormValue()
    {
        return $this->value ? Carbon::parse($this->value)->toDateTimeLocalString() : null;
    }

    public function getTableValue()
    {
        return $this->value ? Carbon::parse($this->value)->format('d.m.Y H:i') : null;
    }

    public function migration(Blueprint $blueprint)
    {
        return $blueprint->dateTime($this->getName());
    }
}
