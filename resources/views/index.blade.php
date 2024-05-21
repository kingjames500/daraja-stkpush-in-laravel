<!DOCTYPE html>
<html>
<head>
    <title>STK Push Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Center the form */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="POST" action="{{ route('stkpush') }}" class="needs-validation">
            @csrf
            @method('POST')
            <div class="form-group">
                <label for="number">Phone Number:</label>
                <input type="text" name="number" id="number" class="form-control" required>
                <div class="invalid-feedback">
                    Please enter a valid phone number.
                </div>
            </div>
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="text" name="amount" id="amount" class="form-control" required>
                <div class="invalid-feedback">
                    Please enter a valid amount.
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <!-- Bootstrap JS (Optional: You can include this if you need Bootstrap JavaScript functionality) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
