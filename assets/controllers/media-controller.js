import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

export default class extends Controller {
    static targets = ['modalButton', 'deleteButton', 'fileInput', 'canvas', 'thumb', 'close']

    add(e) {
        let reader = new FileReader();
        let self = this;
        reader.onload = function (event) {
            let img = new Image();
            let ctx = self.canvasTarget.getContext('2d');
            img.onload = function () {
                self.canvasTarget.width = img.width;
                self.canvasTarget.height = img.height;
                ctx.drawImage(img, 0, 0);
            }
            img.src = event.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);

        this.canvasTarget.classList.remove('d-none');
        this.deleteButtonTarget.classList.remove('d-none');
        this.modalButtonTarget.classList.add('d-none');

        this.closeTarget.click();
    }

    async delete(event) {
        event.preventDefault();

        if (confirm('Jesteś pewien?') === false) {
            return;
        }

        if (this.hasCanvasTarget) {
            this.canvasTarget.getContext('2d').clearRect(0, 0, 0, 0);

            this.canvasTarget.classList.add('d-none');
            this.deleteButtonTarget.classList.add('d-none');
            this.modalButtonTarget.classList.remove('d-none');

            this.fileInputTarget.value = '';

            return;
        }

        try {
            let response = await fetch(event.params.url, {method: 'POST'});

            response = await ok(response);
            response = await response.json();

            this.thumbTarget.classList.add('d-none');
            this.deleteButtonTarget.classList.add('d-none');
            this.modalButtonTarget.classList.remove('d-none');

            this.fileInputTarget.value = '';
        } catch (e) {
            alert('Oops, something went wrong.');
            throw e;
        }
    }
}
