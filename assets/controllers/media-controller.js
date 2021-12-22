import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modalButton', 'canvas', 'close']

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
        this.modalButtonTarget.classList.add('d-none');

        this.closeTarget.click();
    }
}
