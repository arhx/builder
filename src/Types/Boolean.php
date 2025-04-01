<?php
namespace Arhx\Builder\Types;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;

class Boolean extends Text
{
    protected string $form_template = 'builder::type.select';

    protected array $extra = [
        'attributes' => ['class' => 'form-control'],

    ];
    public function boot()
    {
        if(!isset($this->extra['options'])){
            $this->extra['options'] = [
                '' => 'Все',
                'true' => __('true'),
                'false' => __('false'),
            ];
        }
    }

    public function toString(): string
    {
        return $this->value > 0 ? __('true') : __('false');
    }
    public function migration(Blueprint $blueprint)
    {
        return $blueprint->boolean($this->getName())->default(false);
    }
    public function applyFilter(Builder $query)
    {
        $name = $this->getName();
        if($value = request("f.$name")){
            if($value === 'true'){
                $query->where($name,'>',0);
            }elseif($value === 'false'){
                $query->where(function($query) use ($name){
                    $query->whereNull($name)
                        ->orWhere($name,'=',0);
                });
            }
        }
    }
}
