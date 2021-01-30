import {Controller} from 'stimulus';

export default class extends Controller {
    static targets = ['quickLinks', 'showButton', 'closeButton']
    static classes = ["hide"]

    toggle(event) {
        this.showButtonTarget.classList.toggle(this.hideClass)
        this.closeButtonTarget.classList.toggle(this.hideClass)
        this.quickLinksTarget.classList.toggle(this.hideClass)
    }
}
