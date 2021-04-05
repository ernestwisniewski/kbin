import bootstrap from "bootstrap/dist/js/bootstrap.min";
import {ApplicationController, useDebounce} from 'stimulus-use'
import Subscribe from '../utils/notification';

export default class extends ApplicationController {
    static values = {
        magazineName: String
    };

    connect() {
        Subscribe('/api/magazines/' + this.magazineNameValue, function (e) {
            let data = JSON.parse(e.data);

            let div = document.createElement('div');
            div.innerHTML = data.notificationHtml;
            div = div.firstElementChild;

            let container = document.querySelector('.kbin-toast-container')
            container.append(div);

            let t = new bootstrap.Toast(div);
            t.show();
        });
    }
}
