// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {Controller} from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    copy(event) {
        event.preventDefault();

        const url = event.target.href;
        navigator.clipboard.writeText(url);
    }
}