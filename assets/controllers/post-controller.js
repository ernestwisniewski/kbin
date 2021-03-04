import {Controller} from 'stimulus';
import {fetch, ok} from "./utils/http";
import router from "./utils/routing";

export default class extends Controller {
    static targets = ['expand'];
    static values = {
        loading: Boolean,
        id: Number,
        commentList: String
    };

    async expand(event) {
        event.preventDefault();

        this.loadingValue = true;

        let loader = document.createElement("span");
        loader.classList.add('spinner-border', 'me-2');

        event.target.parentNode.replaceChild(loader, event.target);

        try {
            let url = router().generate('ajax_fetch_post_comments', {'id': this.idValue});

            let response = await fetch(url, {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            this.element.parentElement
                .querySelectorAll(`[data-comment-post-id="${this.idValue}"]`)
                .forEach(e => {
                    e.remove()
                })
            this.element.insertAdjacentHTML('afterend', response.html);
        } catch (e) {

        } finally {
            loader.remove();
            this.loadingValue = false;
        }
    }

}
