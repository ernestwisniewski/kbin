import {Controller} from '@hotwired/stimulus';
import * as timeago from 'timeago.js';
import da from 'timeago.js/lib/lang/da';
import de from 'timeago.js/lib/lang/de';
import el from 'timeago.js/lib/lang/el';
import eo from '../utils/timeago-esperanto';
import es from 'timeago.js/lib/lang/es';
import fr from 'timeago.js/lib/lang/fr';
import gl from 'timeago.js/lib/lang/gl';
import it from 'timeago.js/lib/lang/it';
import ja from 'timeago.js/lib/lang/ja';
import nl from 'timeago.js/lib/lang/nl';
import pl from 'timeago.js/lib/lang/pl';
import pt from 'timeago.js/lib/lang/pt_BR';
import ru from 'timeago.js/lib/lang/ru';
import tr from 'timeago.js/lib/lang/tr';
import uk from 'timeago.js/lib/lang/uk';
import zh_TW from 'timeago.js/lib/lang/zh_TW';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const elems = document.querySelectorAll('.timeago');

        if (!elems.length) {
            return;
        }

        const lang = document.documentElement.lang;
        const languages = { da, de, el, eo, es, fr, gl, it, ja, nl, pl, pt, ru, tr, zh_TW, uk };

        if (languages[lang]) {
            timeago.register(lang, languages[lang]);
            timeago.render(elems, lang);
        } else {
            timeago.render(elems);
        }
    }
}