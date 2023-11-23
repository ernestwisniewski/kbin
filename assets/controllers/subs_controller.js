// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/en\>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from '@hotwired/stimulus';
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        loading: Boolean
    };

    async send(event) {
        event.preventDefault();

        const form = event.target.closest('form');

        try {
            this.loadingValue = true;

            let response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form)
            });

            response = await ok(response);
            response = await response.json();

            this.element.outerHTML = response.html;
        } catch (e) {
            form.submit();
        } finally {
            this.loadingValue = false;
        }
    }
}