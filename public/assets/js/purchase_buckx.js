/**
 * Purchase BuckX - Direct Stripe Checkout
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Purchase BuckX script loaded');
    
    const selectButtons = document.querySelectorAll('.btn-select-package');
    console.log('Found ' + selectButtons.length + ' package buttons');
    
    selectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const packageId = this.dataset.packageId;
            const packageName = this.dataset.packageName;
            
            console.log('Button clicked for package:', packageId, packageName);
            
            // Disable button immediately
            this.disabled = true;
            this.textContent = 'Processing...';
            
            // Create Stripe checkout session
            createCheckoutSession(packageId, this);
        });
    });
    
    /**
     * Create Stripe checkout session and redirect to Stripe
     */
    function createCheckoutSession(packageId, button) {
        // Get current page URL to build correct endpoint
        const currentUrl = window.location.href;
        const baseUrl = currentUrl.split('/wallet/')[0];
        const url = baseUrl + '/wallet/createCheckoutSession';
        
        console.log('Current URL:', currentUrl);
        console.log('Base URL:', baseUrl);
        console.log('Calling endpoint:', url);
        console.log('Package ID:', packageId);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'package_id=' + packageId
        })
        .then(response => {
            console.log('Response received');
            console.log('Status:', response.status);
            console.log('Status Text:', response.statusText);
            console.log('Content-Type:', response.headers.get('content-type'));
            
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('Raw response length:', text.length);
            console.log('Raw response (first 500 chars):', text.substring(0, 500));
            
            // Try to parse JSON
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON successfully:', data);
                
                if (data.success && data.checkout_url) {
                    console.log('Redirecting to Stripe:', data.checkout_url);
                    // Redirect to Stripe payment page
                    window.location.href = data.checkout_url;
                } else {
                    console.error('Payment failed:', data.message);
                    alert('Error: ' + (data.message || 'Payment initialization failed'));
                    button.disabled = false;
                    button.textContent = 'Select Package';
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was not JSON:', text.substring(0, 1000));
                
                // Check if it's an HTML error page
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                    console.error('Received HTML instead of JSON - this means the endpoint returned an error page');
                    
                    // Try to extract error message from HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');
                    const body = doc.body.textContent || doc.body.innerText;
                    console.error('HTML content:', body.substring(0, 500));
                    
                    if (body.includes('Not Found')) {
                        alert('Error: Payment endpoint not found.\n\nThe URL might be incorrect or the method might not be accessible.\n\nPlease check the console for details.');
                    } else {
                        alert('Server Error: Received HTML instead of JSON response.\n\nCheck the console for details.');
                    }
                } else {
                    alert('Server Error: Invalid JSON response.\n\nCheck the console for details.');
                }
                
                button.disabled = false;
                button.textContent = 'Select Package';
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Connection error: ' + error.message + '\n\nCheck the console for details.');
            button.disabled = false;
            button.textContent = 'Select Package';
        });
    }
});