<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>STK Query Response</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Add custom CSS styles here if needed */
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>STK Query Response</h2>
        <!-- Checkout ID Form -->
        <form action="{{ route('stkstatus') }}" method="POST" class="mb-4">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="checkoutId">Enter Checkout ID:</label>
                <input type="text" class="form-control" id="checkoutId" name="CheckoutRequestID" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <!-- STK Query Response -->
        @if(isset($response))
        <h3>STK Query Response</h3>
        <pre>{{ json_encode($response, JSON_PRETTY_PRINT) }}</pre>
        @endif
    </div>

    <!-- Bootstrap JS (Optional: You can include this if you need Bootstrap JavaScript functionality) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
