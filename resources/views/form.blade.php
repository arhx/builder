<div class="builder-form grid grid-cols-12 gap-2">
    @foreach($form->getFields() as $field)
        <div class="{{ $field->getFormSizeClass() }}">
            <label class="block" for="field-{{ $field->getName() }}">{{ $field->getLabel() }}</label>
            {{ $field->toForm() }}
        </div>
    @endforeach
</div>
