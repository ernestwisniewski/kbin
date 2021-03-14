import { ApplicationController } from 'stimulus-use'
import { useDispatch } from 'stimulus-use'

export default class extends ApplicationController {
    static values = {
        loading: Boolean,
        isFull: Boolean,
        full: String,
        thumb: String
    };

    connect() {
        useDispatch(this);
    }

    toggle() {
        if (this.isFullValue) {
            this.element.src = this.thumbValue;
            this.isFullValue = false;
        } else {
            this.dispatch('expand', 'true')

            this.element.src = this.fullValue;
            this.isFullValue = true;
        }
    }
}
