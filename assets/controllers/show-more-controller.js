import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        isExpanded: Boolean
    };

    connect() {
        this.checkHeight();
    }

    checkHeight() {
        this.isExpandedValue = false;
        this.element.style.maxHeight = '25rem'

        if (this.element.scrollHeight - 30 > this.element.clientHeight
            || this.element.scrollWidth > this.element.clientWidth) {

            this.moreBtn = this.createMoreBtn();
            this.more();
        } else {
            this.element.style.maxHeight = null;
        }
    }

    createMoreBtn() {
        let moreBtn = document.createElement('div')
        moreBtn.innerHTML = 'pokaż więcej';
        moreBtn.classList.add('kbin-more', 'text-center', 'font-weight-bold');

        this.element.parentNode.insertBefore(moreBtn, this.element.nextSibling);

        return moreBtn;
    }

    more() {
        this.moreBtn.addEventListener('click', e => {
            if (e.target.previousSibling.style.maxHeight) {
                e.target.previousSibling.style.maxHeight = null;
                e.target.innerHTML = 'ukryj';
                this.isExpandedValue = true;
            } else {
                e.target.previousSibling.style.maxHeight = '25rem';
                e.target.innerHTML = 'pokaż więcej';
                e.target.previousSibling.scrollIntoView();
                this.isExpandedValue = false;
            }
        })
    }

    expand() {
        if (!this.isExpandedValue) {
            this.moreBtn.click();
        }
    }
}
