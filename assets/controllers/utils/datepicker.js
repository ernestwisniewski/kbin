import { Datepicker } from 'vanillajs-datepicker';

export default class KChoices {
    constructor() {
        const dp = document.querySelectorAll('.kbin-date').forEach(el => {
            new Datepicker(el, {
                format: 'yyyy-mm-dd 00:00'
            })
        });
    }
}
