import SimpleMDE from 'simplemde';

export default class KChoices {
    constructor() {
        document.querySelectorAll('.kbin-editor').forEach(el => {
            new SimpleMDE({
                element: el,
                hideIcons: ['guide', 'fullscreen', 'side-by-side', 'preview', 'heading'],
                showIcons: ['code', 'table'],
                spellChecker: false,
                status: false,
                toolbarTips: false,
                styleSelectedText: false
            });
        });
    }
}
