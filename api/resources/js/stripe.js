// resources/js/stripe.js

document.addEventListener("DOMContentLoaded", function () {
    const stripe = Stripe(
        "pk_test_51OBtQnGs87jOC3DQC9v31YZDbZSm5PT1MGiLDfu5l3o9XIPKtMFyfFvyy7qG0drg8EZTfmFh0m9dbawjpMGyqWKH00US71mcVf"
    );
    const elements = stripe.elements();

    const card = elements.create("card");
    card.mount("#card-element");

    const form = document.getElementById("payment-form");

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        stripe
            .confirmCardPayment("your-client-secret", {
                payment_method: {
                    card: card,
                    billing_details: {
                        name: document.getElementById("cardholder-name").value,
                    },
                },
            })
            .then(function (result) {
                if (result.error) {
                    // Show error to your customer
                    const errorElement = document.getElementById("card-errors");
                    errorElement.textContent = result.error.message;
                } else {
                    // The payment was successful
                    if (result.paymentIntent.status === "succeeded") {
                        // Handle the success, e.g., redirect to a success page
                        window.location.href = "/success";
                    }
                }
            });
    });
});
