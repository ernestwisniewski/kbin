// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/en\>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from '@hotwired/stimulus';
import {getLevel} from "../utils/kbin";

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect() {
        if (!this.element.firstElementChild.classList.contains('comment')) {
            return;
        }

        let currentElement = this.element.firstElementChild;
        while (currentElement) {
            let nextElement = currentElement.nextElementSibling;
            if (getLevel(nextElement) > getLevel(currentElement)) {
                currentElement.classList.add('comment-has-children');
                if (!nextElement.classList.contains('comment')) {
                    return;
                }

                this.createButton(currentElement);
            }

            currentElement = currentElement.nextElementSibling;
        }
    }

    createButton(currentElement) {
        let div = document.createElement('li');
        div.classList.add('comment-wrap');
        div.innerHTML = '<i class="fa-solid fa-minus" aria-label="Wrap comments" title="Wrap comments"></i>';

        let footer = currentElement.querySelector('footer menu');
        footer.insertBefore(div, footer.firstChild);

        let self = this;
        div.addEventListener('click', function () {
            if (div.classList.contains('comment-wrap-closed')) {
                self.unwrapChildren(currentElement, div);
            } else {
                self.wrapChildren(currentElement, div);
            }
        });
    }

    wrapChildren(currentElement, div) {
        div.classList.add('comment-wrap-closed');
        div.innerHTML = '<i class="fa-solid fa-plus" aria-label="Unwrap comments" title="Unwrap comments"></i>';
        let nextElement = currentElement.nextElementSibling;
        while (nextElement) {
            if (getLevel(nextElement) > getLevel(currentElement)) {
                nextElement.classList.add('hidden');
            } else {
                break;
            }

            nextElement = nextElement.nextElementSibling;
        }
    }

    unwrapChildren(currentElement, div) {
        div.classList.remove('comment-wrap-closed');
        div.innerHTML = '<i class="fa-solid fa-minus" aria-label="Wrap comments" title="Wrap comments"></i>';
        let nextElement = currentElement.nextElementSibling;
        while (nextElement) {
            if (getLevel(nextElement) > getLevel(currentElement)) {
                nextElement.classList.remove('hidden');
                // if in next element exists .comment-wrap-closed then add class unwrapped
                let btn = nextElement.querySelector('.comment-wrap-closed');
                if (btn) {
                    btn.innerHTML = '<i class="fa-solid fa-minus" aria-label="Wrap comments" title="Wrap comments"></i>';
                }
            } else {
                break;
            }

            nextElement = nextElement.nextElementSibling;
        }
    }
}