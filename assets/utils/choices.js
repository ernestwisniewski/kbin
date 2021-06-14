import Choices from "choices.js";

export default class KChoices {
    constructor(form) {
        if (form) {
            return this.build(form.querySelector('.kbin-choices'));
        }

        document.querySelectorAll('.kbin-choices').forEach(el => {
            this.build(el);
        });
    }

    build(el) {
        return new Choices(el, {
            loadingText: 'Czekaj...',
            noResultsText: 'Brak wyników',
            noChoicesText: 'Brak wyników',
            itemSelectText: 'Wybierz',
        });
    }
}
