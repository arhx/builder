<?php
namespace Arhx\Builder\Types;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;

class Select extends Text
{
    protected string $form_template = 'builder::type.select';
    protected array $lazySource;

    protected array $extra = [
        'attributes' => ['class' => 'form-control'],

    ];
    public function getLazySource(): array|false
    {
        return $this->lazySource ?? false;
    }
    public function boot()
    {
        if(isset($this->extra['source'])){
            if(isset($this->extra['source']['model'])){
                $this->extra['source']['field'] = $this->extra['source']['field'] ?? 'name';
                $this->extra['source']['large'] = $this->extra['source']['large'] ?? false;
                if($this->extra['source']['large']) {
                    $this->lazySource = $this->extra['source'];
                    $this->form_template = 'builder::type.text';
                }else{
                    $this->extra['options'] = $this->loadSource($this->extra['source']['model'],$this->extra['source']['field']);
                }
            }
        }
        if(!isset($this->extra['options'])){
            $this->extra['options'] = [];
        }
        if($this->nullable){
            $this->extra['options'] = [null => '-'] + $this->extra['options'];
        }
    }

    public function toString(): string
    {
        if($this->extra['source']['large'] ?? false){
            return strval($this->value);
        }
        return $this->extra['options'][$this->value] ?? "!{$this->value}!";
    }
    public function migration(Blueprint $blueprint)
    {
        return $blueprint->unsignedBigInteger($this->getName())->nullable();
    }

    private function loadSource($modelName, $labelField)
    {
        return $modelName::orderBy($labelField)->pluck($labelField,'id')->toArray();
    }
    public function applyFilter(Builder $query)
    {
        $name = $this->getName();
        if($value = request("f.$name")){
            if($this->extra['source']['large'] ?? false){
                $model = $this->extra['source']['model'];
                $ids = $model::query()->where($this->extra['source']['field'],'like',"%$value%")->pluck('id');
                $query->whereIn($name, $ids);
            }else{
                $query->where($name,'=',$value);
            }
        }
    }
}
