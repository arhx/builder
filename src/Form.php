<?php
namespace Arhx\Builder;
use Illuminate\Support\Facades\Lang;

class Form extends Builder{
    private $item;
    public function title()
    {
        return Lang::get('Slide create');
    }
    public function render()
    {
        return view('builder::form', [
            'form' => $this
        ]);
    }
    public function getFields(): array
    {
        return $this->fields;
    }
    public function load($id)
    {
        $this->item = $this->query->find($id);
        if(!$this->item){
            abort(404, Lang::get('Item not found'));
        }
        foreach($this->getFields() as $field){
            $field->extractValue($this->item);
        }
    }
}
