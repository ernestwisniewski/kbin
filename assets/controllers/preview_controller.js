// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/en\>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";
import router from "../utils/routing";
import { useThrottle } from 'stimulus-use'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        loading: Boolean,
    }

    static throttles = ['show']

    connect(){
        useThrottle(this, {wait: 1000});
    }

    async show(event) {
        event.preventDefault();

        let element = this.element;

        if (element.classList.contains('preview')) {
            element = element.parentElement.previousElementSibling;
            this.element.remove();
        } else {
            let container = this.element.nextElementSibling && this.element.nextElementSibling.classList.contains('js-container')
                ? this.element.nextElementSibling : null;

            if (null === container) {
                container = document.createElement('div');
                container.classList.add('js-container');
                container.style.display = 'none';
                this.element.insertAdjacentHTML('afterend', container.outerHTML);
            } else {
                if (container.querySelector('.preview')) {
                    container.querySelector('.preview').remove();
                    if (0 === container.children.length) {
                        container.remove();
                    }
                    return;
                }
            }
        }

        try {
            this.loadingValue = true;

            let response = await fetch(router().generate('ajax_fetch_embed', {url: event.params.url}), {method: 'GET'});

            response = await ok(response);
            response = await response.json();

            element.nextElementSibling.insertAdjacentHTML('afterbegin', response.html);
            element.nextElementSibling.style.display = 'block';
            if (event.params.ratio) {
                element.nextElementSibling.querySelector('.preview').classList.add('ratio');
            }
            this.loadScripts(response.html);
        } catch (e) {
            const failedHtml = '<div class="preview" data-controller="preview">' + 
                                '<a class="retry-failed" href="#" ' + 
                                    'data-action="preview#show" data-preview-url-param="' + event.params.url +
                                    '" data-preview-ratio-param="' + event.params.ratio + '">' +
                                'Failed to load. Click to retry.</a></div>';
            element.nextElementSibling.insertAdjacentHTML('afterbegin', failedHtml);
            element.nextElementSibling.style.display = 'block';
        } finally {
            this.loadingValue = false;
        }
    }

    loadScripts(response) {
        let tmp = document.createElement("div");
        tmp.innerHTML = response;
        let el = tmp.getElementsByTagName('script');

        if (el.length) {
            let script = document.createElement("script");
            script.setAttribute("src", el[0].getAttribute('src'));
            script.setAttribute("async", "false");

            // let exists = [...document.head.querySelectorAll('script')]
            //     .filter(value => value.getAttribute('src') >= script.getAttribute('src'));
            //
            // if (exists.length) {
            //     return;
            // }

            let head = document.head;
            head.insertBefore(script, head.firstElementChild);
        }
    }

    loadingValueChanged(val) {
        const subject = this.element.closest('.subject');
        if (null !== subject) {
            const subjectController = this.application.getControllerForElementAndIdentifier(subject, 'subject');
            subjectController.loadingValue = val;
        }
    }
}