(function() {
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        const url = args[0];
        if (typeof url === 'string' && url.includes('simple-history')) {
            /* Simple History uses AJAX to apply filters, handle them here */
            const params = new URLSearchParams(window.location.search);
            const bodyParams = new URLSearchParams({
                action: 'persistent_filters_update',
                nonce: pf_data.nonce
            });
            for (const [key, value] of params) {
                bodyParams.append(key, value);
            }
            fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: bodyParams.toString()
            }).then(response => response.json())
              .then(data => console.log('Persistent Filters updated:', data))
              .catch(error => console.error('Persistent Filters update error:', error));
        }
        return originalFetch.apply(this, args);
    };
})();
