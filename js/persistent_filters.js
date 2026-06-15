(function() {
    const originalFetch = window.fetch;
    window.fetch = function(input, init) {
        // Determine URL string and whether input was a Request
        let urlString;
        let isRequest = false;
        if (input instanceof Request) {
            isRequest = true;
            urlString = input.url;
        } else {
            urlString = input;
        }

        // Helper: check if URL matches any configured endpoint substrings
        function matchesEndpoint(u) {
            if (!pf_data || !pf_data.endpoints) return false;
            try {
                const s = u.toString();
                return pf_data.endpoints.some(e => s.includes(e));
            } catch (e) {
                return false;
            }
        }

        // If the outgoing request matches one of the endpoints, merge saved filters
        if (typeof urlString === 'string' && matchesEndpoint(urlString)) {
            try {
                const u = new URL(urlString, window.location.origin);
                const params = new URLSearchParams(u.search);

                // Merge saved filters (do not overwrite existing params)
                const saved = (pf_data && pf_data.saved_filters) ? pf_data.saved_filters : {};
                Object.keys(saved).forEach(k => {
                    const val = saved[k];
                    if (Array.isArray(val)) {
                        val.forEach(v => { if (!params.has(k)) params.append(k, v); });
                    } else {
                        if (!params.has(k)) params.append(k, val);
                    }
                });

                u.search = params.toString();

                if (isRequest) {
                    input = new Request(u.toString(), input);
                } else {
                    input = u.toString();
                }
            } catch (e) {
                console.error('Persistent Filters: failed merging saved filters', e);
            }

            // Also trigger save of current page filters back to plugin (non-blocking)
            try {
                const paramsNow = new URLSearchParams(window.location.search);
                const bodyParams = new URLSearchParams({ action: 'persistent_filters_update', nonce: pf_data.nonce });
                for (const [key, value] of paramsNow) {
                    bodyParams.append(key, value);
                }
                // Fire-and-forget
                fetch(ajaxurl, { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: bodyParams.toString() })
                    .then(r => r.json())
                    .then(d => console.log('Persistent Filters saved:', d))
                    .catch(() => {});
            } catch (e) {
                // ignore
            }
        }

        return originalFetch.apply(this, [input, init]);
    };
})();
