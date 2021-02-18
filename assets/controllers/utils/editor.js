import SimpleMDE from 'simplemde';

export default class KChoices {
    constructor() {
        document.querySelectorAll('.kbin-editor').forEach(el => {
            new SimpleMDE({
                element: el,
            });
        });
    }
}
