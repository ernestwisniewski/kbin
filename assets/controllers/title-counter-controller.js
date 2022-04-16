import CharacterCounter from 'stimulus-character-counter'

export default class extends CharacterCounter {
    static values = {
        max: Number
    }

    update() {
        super.update();

        if (this.count > this.maxValue) {
            this.counterTarget.classList.add('text-danger');
        } else {
            this.counterTarget.classList.remove('text-danger');
        }
    }
}
