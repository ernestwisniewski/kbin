import Timeago from "stimulus-timeago"
import { pl, en } from 'date-fns/locale'

export default class extends Timeago {
    load() {
        super.load();
        // @todo
        this.element.append(` ${document.documentElement.lang === 'pl' ? 'temu': 'ago'}`);
    }

    get locale () {
        return document.documentElement.lang === 'pl' ? pl : en
    }
}
