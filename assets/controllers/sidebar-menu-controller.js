import {Controller} from 'stimulus';

export default class extends Controller {
    static targets = ['content', 'showButton', 'closeButton']
    static classes = ["hide"]

    toggle() {
        this.showButtonTarget.classList.toggle(this.hideClass)
        this.closeButtonTarget.classList.toggle(this.hideClass)
        this.contentTarget.classList.toggle(this.hideClass)
    }
}
