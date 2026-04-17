document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('toggle-button');
    var markdownBody = document.getElementById('markdown-body');

    if(toggleBtn && markdownBody) {
        toggleBtn.addEventListener('click', function() {
            if (markdownBody.style.display === 'none') {
                markdownBody.style.display = 'block';
            } else {
                markdownBody.style.display = 'none';
            }
        });
    }
});