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
        if (form && form.classList.contains('auto-submit')) {
            [...form.querySelectorAll('input,select,textarea')].forEach((element) => {
                if (!element.value) {
                    element.disabled = true;
                }
            });

            const ajaxSelector = form.dataset.ajax;
            if (ajaxSelector) {
                event.preventDefault();
                const formAction = form.getAttribute('action') || window.location.href;
                const url = new URL(formAction, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                url.search = params.toString();

                axios.get(url.toString())
                    .then(response => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(response.data, 'text/html');
                        const newElement = doc.querySelector(ajaxSelector);
                        const currentElement = document.querySelector(ajaxSelector);
                        if (currentElement && newElement) {
                            currentElement.replaceWith(newElement);
                            form.dispatchEvent(new Event('ajax-updated', { bubbles: true }));
                        }
                    })
                    .catch(error => console.error('Ошибка при отправке запроса:', error));
            } else {
                form.submit();
            }
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
