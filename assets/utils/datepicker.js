import {Datepicker} from 'vanillajs-datepicker';
// import pl from 'vanillajs-datepicker/dist/js/locales/pl';

export default class KDatepicker {
    constructor() {
        Object.assign(Datepicker.locales, this.getLocale());

        document.querySelectorAll('.kbin-date').forEach(el => {
            this.build(el);
        });
        
        document.addEventListener('turbo:load', (event) => {
            event.target.querySelectorAll('.kbin-date').forEach(el => {
                this.build(el);
            });
        });
    }

    build(el) {
        let minDate = el.classList.contains('kbin-ban') ? Date.now() : null;
        new Datepicker(el, {
            format: 'yyyy-mm-dd 00:00',
            minDate: minDate,
            language: "pl"
        })
    }
    getLocale() {
        return {
            pl: {
                days: ["Niedziela", "Poniedziałek", "Wtorek", "Środa", "Czwartek", "Piątek", "Sobota"],
                daysShort: ["Niedz.", "Pon.", "Wt.", "Śr.", "Czw.", "Piąt.", "Sob."],
                daysMin: ["Ndz.", "Pn.", "Wt.", "Śr.", "Czw.", "Pt.", "Sob."],
                months: ["Styczeń", "Luty", "Marzec", "Kwiecień", "Maj", "Czerwiec", "Lipiec", "Sierpień", "Wrzesień", "Październik", "Listopad", "Grudzień"],
                monthsShort: ["Sty.", "Lut.", "Mar.", "Kwi.", "Maj", "Cze.", "Lip.", "Sie.", "Wrz.", "Paź.", "Lis.", "Gru."],
                today: "Dzisiaj",
                weekStart: 1,
                clear: "Wyczyść",
                format: "dd.mm.yyyy"
            }
        }
    }
}
