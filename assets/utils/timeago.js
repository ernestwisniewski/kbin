import Timeago from "stimulus-timeago"
import { pl } from 'date-fns/locale'

export default class extends Timeago {
    load() {
        super.load();
        this.element.append(' temu');
    }

    get locale () {
        return pl;
    }
}
