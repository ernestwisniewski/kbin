import Choices from "choices.js";

export default class KChoices {
    constructor() {
        const choices = document.querySelectorAll('.choices').forEach(el => {
            new Choices(el, {
                loadingText: 'Czekaj...',
                noResultsText: 'Brak wyników',
                noChoicesText: 'Brak wyników',
                itemSelectText: 'Wybierz',
            });
        });
    }
}
