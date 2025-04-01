<?php
namespace Arhx\Builder\Form;

use Illuminate\Support\HtmlString;
class Select extends HtmlElement
{
    protected array $options = [];
    protected array $classes = ['form-select'];

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function toHtml(): HtmlString
    {
        $optionsHtml = array_map(fn($v, $k) => "<option value=\"$k\">$v</option>", $this->options, array_keys($this->options));
        return new HtmlString('<select ' . $this->renderAttributes() . '>' . implode('', $optionsHtml) . '</select>');
    }
}