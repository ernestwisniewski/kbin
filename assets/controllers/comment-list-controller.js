import {ApplicationController} from 'stimulus-use'

export default class extends ApplicationController {
    static values = {
        magazineName: String
    };

    add(notification) {
        const magazine  = notification.detail.data.magazine.name;
        const html = notification.detail.html;

        if (this.hasMagazineNameValue && this.magazineNameValue !== magazine) {
            return;
        }

        let div = document.createElement('div');
        div.innerHTML = html;

        this.element.prepend(div);
    }
}
