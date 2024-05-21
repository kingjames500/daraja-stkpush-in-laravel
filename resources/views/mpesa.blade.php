<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Transaction Filter</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Filter Transactions</h2>
        <form action="{{ route('transactions') }}" method="GET">
            <div class="form-group row">
                <label for="status" class="col-sm-2 col-form-label">Filter by Status:</label>
                <div class="col-sm-10">
                    <select name="status" id="status" class="form-control">
                        <option value="">All</option>
                        <option value="failed">Failed</option>
                        <option value="requested">Requested</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>

        <!-- Display transactions -->
        <h2 class="mt-5">Transactions</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Receipt Number</th>
                    <th>Checkout ID</th>
                    <th>Transaction Date</th>
                    <th>Amount</th>
                    <th>Phone Number</th>
                    <th>Results Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $mpesa)
                <tr>
                    <td>{{$mpesa->MpesaReceiptNumber}}</td>
                    <td>{{ $mpesa->CheckoutRequestID }}</td>
                    <td>{{ Carbon\Carbon::parse($mpesa->TransactionDate)->format('Y-m-d H:i:s')}}</td>
                    <td>{{$mpesa->amount}}</td>
                    <td>{{$mpesa->phone}}</td>
                    <td>{{$mpesa->ResultsDesc}}</td> 
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS (Optional: You can include this if you need Bootstrap JavaScript functionality) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
