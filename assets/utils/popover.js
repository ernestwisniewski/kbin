import Popover from 'bootstrap/js/dist/popover';

export default class KPopover {
    constructor() {
        this.build();

        document.addEventListener('turbo:load', (event) => {
            this.build();
        });
    }

    build() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new Popover(popoverTriggerEl)
        })
    }
}
