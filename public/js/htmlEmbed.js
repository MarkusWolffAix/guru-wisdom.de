/**
 * Auto height for own HTML pages embedded with the [html:Name|Title|Height] placeholder.
 * Only loaded on wisdoms that embed such a page (see WordsOfWisdom/template.php).
 *
 * A page in /html/ can report its content height, then this script resizes the
 * matching iframe, so there is no inner scroll bar. Pages that do not report keep
 * the height from the placeholder. Only messages from the same origin and from one
 * of our iframes are accepted.
 *
 * Snippet for a page in /html/ (at the end of its <body>):
 *
 *   <script>
 *   (function () {
 *       if (window.parent === window) return;   // opened directly, not embedded
 *       function report() {
 *           var h = Math.ceil(document.documentElement.getBoundingClientRect().height);
 *           window.parent.postMessage({ type: 'guru-html-height', height: h }, window.location.origin);
 *       }
 *       if ('ResizeObserver' in window) new ResizeObserver(report).observe(document.body);
 *       window.addEventListener('load', report);
 *       report();
 *   })();
 *   </script>
 */
(function () {
    'use strict';

    var MIN_HEIGHT = 200;
    var MAX_HEIGHT = 3000;

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin) return;

        var data = event.data;
        if (!data || data.type !== 'guru-html-height') return;

        var height = Math.round(Number(data.height));
        if (!isFinite(height)) return;
        height = Math.max(MIN_HEIGHT, Math.min(MAX_HEIGHT, height));

        var frames = document.querySelectorAll('iframe[data-autoheight="true"]');
        for (var i = 0; i < frames.length; i++) {
            if (frames[i].contentWindow === event.source) {
                frames[i].style.height = height + 'px';
                break;
            }
        }
    });
})();
