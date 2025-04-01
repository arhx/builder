<?php
namespace Arhx\Builder;

use Arhx\Builder\Types\Datetime;
use Arhx\Builder\Types\Dynamic;
use Arhx\Builder\Types\Text;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Table extends Builder
{
    protected array $fields = [];
    protected array $items;
    protected Paginator $pagination;
    protected function boot()
    {

    }

    // Получение заголовков таблицы
    public function headers(): HtmlString
    {
        $header = '<tr>';
        foreach ($this->getFields(true) as $field) {
            $label = $field->getLabel();
            $header .= "<th>$label</th>";
        }
        $header .= '</tr>';

        if($this->getConfig('filters',true)){
            $header .= "<tr class='filters'>";
            foreach ($this->getFields(true) as $field) {
                if(method_exists($field, 'getFilter')){
                    $filter = $field->getFilter();
                    $header .= "<th>$filter</th>";
                }else{
                    $header .= "<th></th>";
                }
            }
            $header .= "</tr>";
        }

        return new HtmlString($header);
    }

    // Генерация тела таблицы
    public function body($items = null): HtmlString
    {
        if (!$items) {
            $items = $this->getItems();
        }
        $rows = '';
        foreach ($items as $item) {
            $rows .= $this->row($item);
        }
        if(empty($rows)){
            $colspan = count($this->getFields(true));
            $notFoundText = __('Items not found');
            $rows = "<tr><td colspan='$colspan'>$notFoundText</td></tr>";
        }
        return new HtmlString($rows);
    }

    // Генерация строки таблицы
    public function row($item): HtmlString
    {
        $row = '';
        foreach ($this->getFields(true) as $field) {
            $field->extractValue($item);
            $row .= "<td>{$field->getTableValue()}</td>";
        }
        return new HtmlString("<tr>$row</tr>");
    }

    // Генерация формы
    public function form($item): string
    {
        $formFields = '';
        foreach ($this->fields as $field) {
            $formFields .= $field->form($item);
        }
        return "<form>{$formFields}</form>";
    }

    // Генерация данных для заполнения
    public function data($item): array
    {
        $data = [];
        foreach ($this->getFields() as $field) {
            $data[$field->getName()] = $field->fillData($item);
        }
        return $data;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getItems()
    {

        $parser = $this->getConfig('parser');
        $items = $this->pagination()->items();
        if(is_callable($parser)){
            $items = array_map($parser, $items);
        }
        return $items;
    }

    public function render()
    {
        return view('builder::type.table', [
            'table' => $this
        ]);
    }
    public function getLabel(): string
    {
        $lang_key = "builder.table.{$this->name}";
        $label = Lang::has($lang_key) ?
            Lang::get($lang_key) :
            __(Str::ucfirst(str_replace('_', ' ', $this->name)));
        return $label;
    }

    public function schemaCreate()
    {
        Schema::create($this->name, function (Blueprint $table) {
            $table->id();
            foreach ($this->getFields() as $field) {
                $field->migration($table);
            }
            $table->timestamps();
        });
    }

    public function getFields($forTable = false): array
    {
        if(!$forTable){
            return $this->fields;
        }
        $prepend = [];
        if($handler = $this->getConfig('actions',false)){
            $prepend[] = new Dynamic('actions', $handler);
        }
        $prepend[] = new Text(name: 'id');

        $append = [
            new Datetime(name: 'created_at'),
            new Datetime(name: 'updated_at'),
        ];
        return array_merge($prepend, $this->fields, $append);
    }
    public function pagination(): Paginator
    {
        if(!isset($this->pagination)){
            $this->applyFilters();
            $this->pagination = $this->query->simplePaginate($this->config['per_page'] ?? 100);
            $lazySource = [];
            foreach($this->getFields() as $field){
                if($source = $field->getLazySource()){
                    $lazySource[$field->getName()] = $source;
                }
            }
            if(!empty($lazySource)){
                $lazyValues = [];
                foreach($lazySource as $field => $source){
                    $lazyValues[$field] = [];
                }
                foreach($this->pagination->items() as $item){
                    foreach($lazySource as $field => $source){
                        $value = $item->$field ?? null;
                        if($value > 0){
                            $lazyValues[$field][] = $value;
                        }
                    }
                }
                foreach($lazyValues as $field => $values){
                    $model = $lazySource[$field]['model'];
                    $labelField = $lazySource[$field]['field'];
                    $lazyValues[$field] = $model::select('id', $labelField)
                        ->whereIn('id', array_unique($values))
                        ->pluck($labelField,'id')->toArray();
                }
                foreach($this->pagination->items() as $item){
                    foreach($lazyValues as $field => $values){
                        $value = $item->$field ?? null;
                        if(isset($values[$value])){
                            $item->$field = $values[$value];
                        }elseif(!empty($value)){
                            $item->$field = "deleted #{$value}";
                        }
                    }
                }
            }
        }
        return $this->pagination;
    }
    public function links(): Htmlable
    {
        return $this->pagination()->links();
    }

    public function applyFilters()
    {
        foreach($this->getFields(true) as $field){
            if(method_exists($field,'applyFilter')){
                $field->applyFilter($this->query);
            }
        }
    }
}
