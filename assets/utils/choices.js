import Choices from "choices.js";

export default class KChoices {
    constructor(form) {
        if (form) {
            return this.build(form.querySelector('.kbin-choices'));
        }

        document.querySelectorAll('.kbin-choices').forEach(el => {
            this.build(el);
        });

        document.addEventListener('turbo:load', (event) => {
            event.target.querySelectorAll('.kbin-choices').forEach(el => {
                this.build(el);
            });
        });
    }

    build(el) {
        let options = {
            loadingText: 'Czekaj...',
            noResultsText: 'Brak wyników',
            noChoicesText: 'Brak wyników',
            itemSelectText: 'Wybierz',
            addItemText: 'Wciśnij enter aby dodać'
        };

        if (el.classList.contains('kbin-choices-text')) {
            options = {
                ...{
                    delimiter: ',',
                    editItems: true,
                    maxItemCount: 6,
                    removeItemButton: true,
                }, ...options
            };
        }

        return new Choices(el, options);
    }
}
