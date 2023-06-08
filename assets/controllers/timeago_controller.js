import {Controller} from '@hotwired/stimulus';
import * as timeago from 'timeago.js';
import es from 'timeago.js/lib/lang/es';
import fr from 'timeago.js/lib/lang/fr';
import it from 'timeago.js/lib/lang/it';
import ja from 'timeago.js/lib/lang/ja';
import nl from 'timeago.js/lib/lang/nl';
import pl from 'timeago.js/lib/lang/pl';
import pt from 'timeago.js/lib/lang/pt_br';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const elems = document.querySelectorAll('.timeago');

        if (elems.length) {
            if (document.documentElement.lang === 'es') { // @todo
                timeago.register('es', es);
                timeago.render(document.querySelectorAll('.timeago'), 'es');
            } else if (document.documentElement.lang === 'fr') {
                timeago.register('fr', fr);
                timeago.render(document.querySelectorAll('.timeago'), 'fr');
            } else if (document.documentElement.lang === 'it') {
                timeago.register('it', it);
                timeago.render(document.querySelectorAll('.timeago'), 'it');
            } else if (document.documentElement.lang === 'ja') {
                timeago.register('ja', ja);
                timeago.render(document.querySelectorAll('.timeago'), 'ja');
            } else if (document.documentElement.lang === 'nl') {
                timeago.register('nl', nl);
                timeago.render(document.querySelectorAll('.timeago'), 'nl');
            } else if (document.documentElement.lang === 'pl') {
                timeago.register('pl', pl);
                timeago.render(document.querySelectorAll('.timeago'), 'pl');
            } else if (document.documentElement.lang === 'pt') {
                timeago.register('pt', pt);
                timeago.render(document.querySelectorAll('.timeago'), 'pt');
            } else {
                timeago.render(document.querySelectorAll('.timeago'));
            }
        }
    }
}