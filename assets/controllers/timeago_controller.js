import {Controller} from '@hotwired/stimulus';
import * as timeago from 'timeago.js';
import de from 'timeago.js/lib/lang/de';
import el from 'timeago.js/lib/lang/el';
import fr from 'timeago.js/lib/lang/fr';
import it from 'timeago.js/lib/lang/it';
import ja from 'timeago.js/lib/lang/ja';
import nl from 'timeago.js/lib/lang/nl';
import pl from 'timeago.js/lib/lang/pl';
import tr from 'timeago.js/lib/lang/tr';
import pt from 'timeago.js/lib/lang/pt_BR';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const elems = document.querySelectorAll('.timeago');
        const lang = document.documentElement.lang;

        if (elems.length) {
            if (lang === 'de') {
                timeago.register('de', de);
                timeago.render(elems, 'de');
            } else if (lang === 'es') {
                timeago.register('el', el);
                timeago.render(elems, 'el');
            } else if (lang === 'fr') {
                timeago.register('fr', fr);
                timeago.render(elems, 'fr');
            } else if (lang === 'it') {
                timeago.register('it', it);
                timeago.render(elems, 'it');
            } else if (lang === 'ja') {
                timeago.register('ja', ja);
                timeago.render(elems, 'ja');
            } else if (lang === 'nl') {
                timeago.register('nl', nl);
                timeago.render(elems, 'nl');
            } else if (lang === 'pl') {
                timeago.register('pl', pl);
                timeago.render(elems, 'pl');
            } else if (lang === 'pt') {
                timeago.register('pt', pt);
                timeago.render(elems, 'pt');
            } else if (lang === 'tr') {
                timeago.register('tr', tr);
                timeago.render(elems, 'tr');
            } else {
                timeago.render(elems);
            }
        }
    }
}