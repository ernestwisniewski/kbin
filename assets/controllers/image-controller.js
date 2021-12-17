import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static classes = ['loading']
    static values = {
        loading: Boolean,
        isFull: Boolean,
        full: String,
        thumb: String
    };

    connect() {
        this.element.onload = (() => {
            this.dispatch('thumbLoaded', true)
        });
    }

    async toggle() {
        if (this.isFullValue) {
            this.element.src = this.thumbValue;
            this.isFullValue = false;
        } else {
            this.dispatch('thumbSelected', true)

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
