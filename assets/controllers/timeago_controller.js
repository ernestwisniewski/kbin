import {Controller} from '@hotwired/stimulus';
import * as timeago from 'timeago.js';
import pl from 'timeago.js/lib/lang/pl';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const elems = document.querySelectorAll('.timeago');

        if (elems.length) {
            if(document.documentElement.lang === 'pl') { // @todo
                timeago.register('pl', pl);
                timeago.render(document.querySelectorAll('.timeago'), 'pl');
            } else {
                timeago.render(document.querySelectorAll('.timeago'));
            }
        }
    }
}