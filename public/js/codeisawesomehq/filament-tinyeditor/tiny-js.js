window.addEventListener('beforeunload', (event) => {
    if (tinymce.activeEditor.isDirty()) {
        event.returnValue = 'Are you sure you want to leave?';
    }
});
