<select {{ $attributes }}>
    @foreach ($options as $key => $label)
        <option value="{{ $key }}" @selected($key == $value)>{{ $label }}</option>
    @endforeach
</select>
@isset($source['url'])
<script type="module">
    (function () {
        const source = @json($source);
        const attributes = @json($attributes);
        const select = document.getElementById(attributes.id);

        const getFormValues = () => {
            return Array.from(select.form.elements)
                .filter(el => el.name && el.type !== 'file')
                .reduce((acc, el) => {
                    acc[el.name] = el.value;
                    return acc;
                }, {});
        };

        const updateSelect = (options, selectedValue) => {
            if(select.tomselect){
                select.tomselect.addOptions([...options].map(([value, text]) => {
                    return {value, text};
                }));
            }else{
                select.innerHTML = ''; // Очистка списка

                [...options].forEach(([value, label]) => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    if (value === selectedValue) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }
        };

        function refreshOptions(){
            axios.post(source.url, getFormValues())
                .then((response) => {
                    const options = response.data.options;
                    const currentValue = select.value;

                    if (options) {
                        updateSelect(options, options[currentValue] ? currentValue : null);
                    }
                });
        }
        if(source.onchange){
            [...source.onchange].forEach((field) => {
                document.querySelector(`[name="${field}"]`).addEventListener('change',refreshOptions);
            });
        }
        refreshOptions();
    })();
</script>
@endisset
