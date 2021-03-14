import {ApplicationController} from 'stimulus-use'

export default class extends ApplicationController {
    static classes = ['loading']
    static values = {
        loading: Boolean,
        isFull: Boolean,
        full: String,
        thumb: String
    };

    async toggle() {
        if (this.isFullValue) {
            this.element.src = this.thumbValue;
            this.isFullValue = false;
        } else {
            this.dispatch('expand', 'true')

            this.loadingValue = true;

            this.element.src = this.fullValue;
            this.isFullValue = true;

            let self = this;
            this.element.onload = (() => {
                self.loadingValue = false;
            })
        }
    }

    loadingValueChanged() {
        if (this.loadingValue) {
            this.element.classList.add(this.loadingClass)
        } else {
            this.element.classList.remove(this.loadingClass)
        }
    }
}
