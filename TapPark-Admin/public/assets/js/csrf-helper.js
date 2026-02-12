/**
 * Global CSRF Token Helper
 * Gets CSRF token for cookie-based CSRF protection
 */
function getCSRFToken() {
    console.log('🔍 Getting CSRF token...');

    // Try to get from cookie first (for cookie-based CSRF)
    const cookies = document.cookie.split(';');
    console.log('🍪 Available cookies:', document.cookie);

    for (let cookie of cookies) {
        const [name, value] = cookie.trim().split('=');
        if (name === 'csrf_cookie_name') {
            console.log('✅ Found CSRF in cookie:', decodeURIComponent(value));
            return decodeURIComponent(value);
        }
    }

    // Try to get from hidden input field
    const input = document.querySelector('input[name="csrf_test_name"]');
    if (input) {
        console.log('✅ Found CSRF in hidden input:', input.value);
        return input.value;
    }

    // Try to get from meta tag
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        console.log('✅ Found CSRF in meta tag:', metaTag.getAttribute('content'));
        return metaTag.getAttribute('content');
    }

    // Try to get from any input with csrf in the name
    const csrfInputs = document.querySelectorAll('input[name*="csrf"]');
    for (let input of csrfInputs) {
        if (input.value) {
            console.log('✅ Found CSRF in csrf input:', input.value);
            return input.value;
        }
    }

    console.log('❌ NO CSRF TOKEN FOUND!');
    return null;
}

/**
 * Add CSRF token to form data
 */
function addCSRFToken(formData) {
    console.log('🔧 Adding CSRF token to form data:', formData);

    // Ensure formData is an object
    if (!formData || typeof formData !== 'object') {
        formData = {};
    }

    const token = getCSRFToken();
    if (token) {
        formData.csrf_test_name = token;
        console.log('✅ CSRF token added to form data:', formData);
    } else {
        console.log('❌ NO CSRF TOKEN TO ADD!');
    }

    return formData;
}

/**
 * Make AJAX request with CSRF token
 */
function ajaxWithCSRF(url, options = {}) {
    const defaultOptions = {
        method: 'POST',
        data: {}
    };

    // Merge options while preserving specific ones like success, error, complete
    const mergedOptions = $.extend(true, {}, defaultOptions, options);

    // Add CSRF token to data
    mergedOptions.data = addCSRFToken(mergedOptions.data);

    return $.ajax({
        url: url,
        method: mergedOptions.method,
        data: mergedOptions.data,
        ...mergedOptions // Spread remaining options like success, error, complete, etc.
    });
}

/**
 * Refresh CSRF token after successful AJAX request
 * Updates all CSRF token sources with the new token
 */
function refreshCSRFToken() {
    // Get the latest token from cookie
    const token = getCSRFToken();
    if (token) {
        // Update meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', token);
        }

        // Update hidden input
        const input = document.querySelector('input[name="csrf_test_name"]');
        if (input) {
            input.value = token;
        }

        console.log('CSRF token refreshed');
    }
}

// Make functions globally available
window.getCSRFToken = getCSRFToken;
window.addCSRFToken = addCSRFToken;
window.ajaxWithCSRF = ajaxWithCSRF;
window.refreshCSRFToken = refreshCSRFToken;
