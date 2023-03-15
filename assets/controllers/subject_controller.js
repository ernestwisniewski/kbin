import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['more']

    connect() {
        const self = this;
        this.moreTarget.addEventListener('focusin', () => {
            self.element.parentNode
                .querySelectorAll('.z-100')
                .forEach((el) => {
                    el.classList.remove('z-100');
                });
            this.element.classList.add('z-100');
        });
    }
}