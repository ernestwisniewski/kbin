import bootstrap from "bootstrap/dist/js/bootstrap.min";
import {ApplicationController} from 'stimulus-use'
import Subscribe from '../utils/notification';

export default class extends ApplicationController {
    static values = {
        magazineName: String
    };

    connect() {
        let url = '*';
        if (this.hasMagazineNameValue) {
            url = '/api/magazines/' + this.magazineNameValue;
        }

        let self = this;
        Subscribe(url, function (e) {
            let data = JSON.parse(e.data);
            self.toast(data.notification);
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
