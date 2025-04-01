<?php

namespace Arhx\Builder\Form;

use Illuminate\Support\HtmlString;

abstract class HtmlElement
{
    protected array $attributes = [];
    protected array $classes = [];
    protected ?string $template = null;

    /**
     * Установка атрибутов (можно передавать массив)
     */
    public function setAttr(string|array $key, $value = null): static
    {
        if (is_array($key)) {
            foreach ($key as $attr => $val) {
                $this->setAttr($attr, $val);
            }
        } else {
            if($value === true){
                $this->attributes[$key] = $key;
            }elseif($value === false){
                 unset($this->attributes[$key]);
            }else{
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Получение атрибута
     */
    public function getAttr(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Удаление атрибута (или массива атрибутов)
     */
    public function removeAttr(string|array $key): static
    {
        foreach ((array) $key as $attr) {
            unset($this->attributes[$attr]);
        }
        return $this;
    }

    /**
     * Полная замена классов
     */
    public function setClass(string|array $class): static
    {
        $this->classes = is_array($class) ? $class : explode(' ', trim($class));
        return $this;
    }

    /**
     * Добавление классов
     */
    public function addClass(string|array $class): static
    {
        $newClasses = is_array($class) ? $class : explode(' ', trim($class));
        $this->classes = array_unique(array_merge($this->classes, $newClasses));
        return $this;
    }

    /**
     * Удаление классов
     */
    public function removeClass(string|array $class): static
    {
        $removeClasses = is_array($class) ? $class : explode(' ', trim($class));
        $this->classes = array_diff($this->classes, $removeClasses);
        return $this;
    }

    /**
     * Установка шаблона Blade
     */
    public function setTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Получение шаблона
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * Генерация строки атрибутов
     */
    protected function renderAttributes(): string
    {
        $attributes = $this->attributes;
        if (!empty($this->classes)) {
            $attributes['class'] = implode(' ', $this->classes);
        }
        return implode(' ', array_map(fn($k, $v) => "$k=\"$v\"", array_keys($attributes), $attributes));
    }

    /**
     * Генерация HTML-элемента (по умолчанию input)
     */
    public function toHtml(): HtmlString
    {
        return new HtmlString('<input ' . $this->renderAttributes() . '>');
    }

    /**
     * Финальный рендер
     */
    public function render(): HtmlString
    {
        if ($this->template) {
            return new HtmlString(view($this->template, ['element' => $this])->render());
        }
        return $this->toHtml();
    }
}
