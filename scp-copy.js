document.addEventListener('DOMContentLoaded', function() {
    function copyCode(e) {
        var button = e.target.closest('.scp-copy-btn');
        if (!button) return;

        var targetId = button.getAttribute('data-target');
        var codeElem = document.getElementById(targetId);
        if (!codeElem) return;

        var text = codeElem.textContent || codeElem.innerText;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                var original = button.textContent;
                button.textContent = 'Zkopírováno!';
                setTimeout(function() {
                    button.textContent = original;
                }, 2000);
            }).catch(function() {
                fallbackCopy(text, button);
            });
        } else {
            fallbackCopy(text, button);
        }

        function fallbackCopy(text, button) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            textarea.style.top = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();

            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    var original = button.textContent;
                    button.textContent = 'Zkopírováno!';
                    setTimeout(function() {
                        button.textContent = original;
                    }, 2000);
                } else {
                    alert('Kopírování do schránky selhalo, zkopírujte kód ručně.');
                }
            } catch (err) {
                alert('Kopírování do schránky není podporováno.');
            }

            document.body.removeChild(textarea);
        }

        e.preventDefault();
    }

    document.body.addEventListener('click', copyCode);
    document.body.addEventListener('touchend', copyCode);
});