import bootstrap from "bootstrap/dist/js/bootstrap.min";
import {ApplicationController, useDebounce} from 'stimulus-use'
import Subscribe from '../utils/notification';

export default class extends ApplicationController {
    static values = {
        name: String
    };

    connect() {
        if (!this.hasNameValue) {
            return;
        }

        let self = this;
        Subscribe('/api/magazines/' + this.nameValue, function (e) {
            let data = JSON.parse(e.data);
            self.toast(data.notificationHtml);
        });
    }

    toast(html) {
        let div = document.createElement('div');
        div.innerHTML = html;
        div = div.firstElementChild;

        let container = document.querySelector('.kbin-toast-container')
        container.append(div);

        let t = new bootstrap.Toast(div);
        t.show();
    }
}
