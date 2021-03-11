import {Controller} from 'stimulus';

export default class extends Controller {
    connect() {
        this.element.style.maxHeight = '25rem'

        if (this.element.scrollHeight > this.element.clientHeight
            || this.element.scrollWidth > this.element.clientWidth) {

            let moreBtn = this.createMoreBtn();
            this.more(moreBtn);
        }
    }

    createMoreBtn() {
        let moreBtn = document.createElement('div')
        moreBtn.innerHTML = 'pokaż więcej';
        moreBtn.classList.add('kbin-more', 'text-center', 'font-weight-bold');

        this.element.parentNode.insertBefore(moreBtn, this.element.nextSibling);

        return moreBtn;
    }

    more(target) {
        target.addEventListener('click', e => {
            if (e.target.previousSibling.style.maxHeight) {
                e.target.previousSibling.style.maxHeight = null;
                e.target.innerHTML = 'ukryj';
            } else {
                e.target.previousSibling.style.maxHeight = '25rem';
                e.target.innerHTML = 'pokaż więcej';
                e.target.previousSibling.scrollIntoView()
            }
        })
    }
}
