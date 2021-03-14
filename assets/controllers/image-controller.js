import {ApplicationController} from 'stimulus-use'
import {useDispatch} from 'stimulus-use'
import {fetch, ok} from "./utils/http";
import KEditor from "./utils/editor";

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
            this.element.classList.add(this.loadingClass)
            try {
                let response = await fetch(this.fullValue, {method: 'GET'});
                response = await ok(response);

                this.element.src = this.fullValue;
                this.isFullValue = true;
            } catch (e) {
            } finally {
                this.loadingValue = false;
                this.element.classList.remove(this.loadingClass)
            }
        }
    }
}
