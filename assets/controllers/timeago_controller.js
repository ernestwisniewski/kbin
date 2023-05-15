import {Controller} from '@hotwired/stimulus';
import * as timeago from 'timeago.js';
import pl from 'timeago.js/lib/lang/pl';
import nl from 'timeago.js/lib/lang/nl';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const elems = document.querySelectorAll('.timeago');

        if (elems.length) {
            if (document.documentElement.lang === 'pl') { // @todo
                timeago.register('pl', pl);
                timeago.render(document.querySelectorAll('.timeago'), 'pl');
            } else if (document.documentElement.lang === 'nl') {
                timeago.register('nl', nl);
                timeago.render(document.querySelectorAll('.timeago'), 'nl');
            } else {
                timeago.render(document.querySelectorAll('.timeago'));
            }
        }
    }
}