<!-- resources/views/checkout.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="{{ asset('js/stripe.js') }}" defer></script>

</head>

<body>
    <h1>Checkout Page</h1>

    <form id="payment-form">
        <!-- Cardholder's Name -->
        <label for="cardholder-name">Cardholder's Name</label>
        <input type="text" id="cardholder-name" required>

        <!-- Card Number -->
        <label for="card-number">Card Number</label>
        <div id="card-element"></div>

        <!-- Expiration Date -->
        <label for="card-expiry">Expiration Date</label>
        <input type="text" id="card-expiry" placeholder="MM / YY" required>

        <!-- CVC -->
        <label for="card-cvc">CVC</label>
        <input type="text" id="card-cvc" required>

        <!-- Display card errors -->
        <div id="card-errors" role="alert"></div>

        <!-- Submit Button -->
        <button type="submit">Submit Payment</button>
    </form>
</body>

</html>