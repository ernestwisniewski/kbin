import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import router from "./utils/routing";
import KEditor from "./utils/editor";

export default class extends Controller {
    static targets = ['reply'];
    static values = {
        loading: Boolean,
        magazine: String,
        entry: Number,
        parent: Number,
        form: String
    };

    async reply(event) {
        event.preventDefault();

        this.loadingValue = true;

        try {
            let url = router().generate('entry_comment_create', {
                magazine_name: this.magazineValue,
                entry_id: this.entryValue,
                parent_comment_id: this.parentValue
            });

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.formValue = response.form;
        } catch (e) {
            throw e;
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
