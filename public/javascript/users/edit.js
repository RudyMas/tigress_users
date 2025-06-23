document.addEventListener('DOMContentLoaded', function () {
    const submitButton = document.getElementById('submit');
    const form = document.getElementById('form');

    if (submitButton && form) {
        submitButton.addEventListener('click', function () {
            form.submit();
        });
    }
});
