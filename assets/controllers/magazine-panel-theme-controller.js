import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['basic', 'advanced']

    changeMode(event) {
        event.preventDefault();

        if (this.advancedTarget.classList.contains('visually-hidden')) {
            this.advancedTarget.classList.remove('visually-hidden')
            this.basicTarget.classList.add('visually-hidden')
        } else {
            this.basicTarget.classList.remove('visually-hidden')
            this.advancedTarget.classList.add('visually-hidden')
        }
    }
}
