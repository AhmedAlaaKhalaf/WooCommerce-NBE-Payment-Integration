jQuery(document).ready(function($) {
    // Configure the NBE checkout
    Checkout.configure({
        session: {
            id: nbe_params.session_id
        },
        merchant: nbe_params.merchant_id,
        order: {
            amount: nbe_params.amount,
            currency: nbe_params.currency,
            description: nbe_params.description,
            id: nbe_params.order_id
        },
        interaction: {
            operation: "PURCHASE",
            merchant: {
                name: nbe_params.merchant_name
            },
            displayControl: {
                billingAddress: "HIDE",
                customerEmail: "HIDE",
                orderSummary: "SHOW",
                shipping: "HIDE"
            },
            returnUrl: nbe_params.return_url
        }
    });

    // Automatically show the payment page
    Checkout.showPaymentPage();
    
    // Define callback functions
    window.errorCallback = function(error) {
        console.log(JSON.stringify(error));
        window.location.href = nbe_params.return_url + '&status=error';
    };
    
    window.cancelCallback = function() {
        console.log("Payment cancelled");
        window.location.href = nbe_params.return_url + '&status=cancelled';
    };
    
    window.completeCallback = function(resultIndicator, sessionVersion) {
        console.log("Payment completed");
        window.location.href = nbe_params.return_url + '&resultIndicator=' + resultIndicator;
    };
});