<?php
namespace Arhx\Builder\Types;

use Arhx\Builder\Helpers;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\View;

class Text extends Basic
{
    protected $value = null;
    protected bool $migration = true;
    protected bool $nullable = true;
    protected array $extra = ['attributes' => ['class' => 'form-control']];
    protected string $form_template = 'builder::type.text';

    public function __construct($value = null, array $extra = [], string $name = null)
    {
        $this->value = $value;
        $this->extra = array_merge($this->extra, $extra);
        if ($name) {
            $this->setName($name);
        }
        $this->boot();
    }
    public function boot(){}
    public function getFormSize(): int
    {
        return intval($this->extra['form_size'] ?? 6);
    }
    public function getFormSizeClass(): string
    {
        $size = $this->getFormSize();

        return "col-span-$size";
    }

    public function getLazySource(): array|bool
    {
        return false;
    }

    public function toForm(string $name = null, $value = null): View
    {
        if (!$name) {
            $name = $this->getName();
        }
        if(!$value){
            $value = $this->getFormValue();
        }
        $extra = $this->extra;
        $attributes = $extra['attributes'] ?? [];
        $attributes = array_merge($attributes, [
            'class' => $attributes['class'] ?? 'form-control',
            'name' => $name,
            'type' => $attributes['type'] ?? 'text',
            'value' => $value,
            'id' => "field-$name",
        ]);
        $extra['attributes'] = $attributes;

        if (isset($extra['source']['route'])) {
            $extra['source']['url'] = route($extra['source']['route']);
        }
        $attributes = new ComponentAttributeBag($attributes);

        return view($this->form_template, [
            ...$extra,
            'value' => $value,
            'attributes' => $attributes,
        ]);
    }

    public function getFilter()
    {
        $name = $this->getName();
        $form = $this->toForm("f[$name]", request("f.$name"));
        $form->attributes['type'] = 'search';
        return $form;
    }

    public function toString(): string
    {
        return is_scalar($this->value) || is_null($this->value) ? (string)$this->value : json_encode($this->value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function getFormValue()
    {
        return $this->value;
    }

    public function getTableValue()
    {
        return $this->toString();
    }

    public function migration(Blueprint $blueprint)
    {
        if (!$this->migration) {
            return false;
        }
        $maxlength = $this->extra['maxlength'] ?? null;
        $field = $blueprint->string($this->getName(), $maxlength);
        if ($this->nullable) {
            $field->nullable();
        }
        return $field;
    }
    public function applyFilter(Builder $query)
    {
        $name = $this->getName();
        if($value = request("f.$name")){
            if(preg_match('/^(\d+)-(\d+)$/', $value, $matches)){
                $query->whereBetween($name, [$matches[1], $matches[2]]);
            }else{
                $query->where($name,'like',"%$value%");
            }
        }
    }
}
