import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import router from "./utils/routing";
import KEditor from "./utils/editor";

export default class extends Controller {
    static targets = ['reply'];
    static values = {
        loading: Boolean,
        url: String,
        form: String
    };

    async reply(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let response = await fetch(this.urlValue, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.formValue = response.form;
        } catch (e) {
            alert('Nie możesz dodać komentarza.');
        } finally {
            this.loadingValue = false;
        }
    }

    formValueChanged(val) {
        if (!val) {
            return;
        }

        this.replyTarget.innerHTML = val;
        new KEditor(this.replyTarget);
    }
}
