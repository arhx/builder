import '../css/builder.css';
import TomSelect from "tom-select";

function debounce(fn, delay) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), delay);
    };
}
function initAutocomplete(){
    document.querySelectorAll("select.autocomplete:not(.initialized)").forEach(select => {
        select.classList.add('initialized');
        new TomSelect(select, {
            create: select.dataset.create || false,
        });
    });
    document.querySelectorAll("[data-autocomplete]").forEach(element => {
        element.classList.add('initialized');
        new TomSelect(element, {
            create: element.dataset.create || false,
            load: debounce(function(query, callback) {
                if (query.length < 3) return callback(); // Отправлять запрос только если 3+ символа
                fetch(element.dataset.dataAutocomplete + `?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => callback(data))
                    .catch(() => callback());
            }, 300)
        });
    });
}

document.addEventListener("DOMContentLoaded", function() {
    initAutocomplete();
    document.addEventListener("change", function(event) {
        const form = event.target.form;
        if(form && form.classList.contains('auto-submit')){
            [...form.querySelectorAll('input,select,textarea')].forEach((element) => {
                if(!element.value){
                    element.disabled = true;
                }
            })
            form.submit();
        }
    });
    document.querySelectorAll(".auto-submit").forEach(form => {
        form.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
            }
        });
    });
});
