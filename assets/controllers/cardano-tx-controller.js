import {Controller} from '@hotwired/stimulus';
import {useDebounce} from 'stimulus-use'
import router from "../utils/routing";
import {ok} from "../utils/http";

export default class extends Controller {
    static debounces = ['calculateFee']
    static targets = ['fee', 'sum', 'address'];
    static values = {
        walletid: String
    }

    connect() {
        useDebounce(this, {wait: 500})
    }

    async send(event) {
    }

    async calculateFee(e) {
        try {
            const url = router().generate('cardano_estimate_fee');

            let response = await fetch(url, {
                method: 'POST',
                body: JSON.stringify({
                    'address': this.addressTarget.value,
                    'amount': e.target.value,
                    'walletId': this.walletidValue
                }),
            });

            response = await ok(response);
            response = await response.json();

            this.sumTarget.innerHTML = response.sum;
            this.feeTarget.value = response.fee;
        } catch (e) {
            throw e;
        }
    }
}
