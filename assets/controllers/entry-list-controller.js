// import {Controller} from 'stimulus';
import {ApplicationController, useDebounce} from 'stimulus-use'
import Subscribe from '../utils/notification';

export default class extends ApplicationController {
    static values = {
        magazineName: String
    };

    connect() {
        const sub = Subscribe('/api/magazines/' + this.magazineNameValue, function (e) {
            console.log('abc');
            console.log(e);
        });
    }
}
