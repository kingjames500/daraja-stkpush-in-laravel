<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Daraja sample codes</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }

        h1 {
            margin-bottom: 30px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sample codes for Daraja API</h1>
        <ul class="list-group">
            <li class="list-group-item"><a href="{{ route('stkpush') }}">STK Push</a></li>
            <li class="list-group-item"><a href="{{ route('transactions') }}">Filter Transactions</a></li>
            <li class="list-group-item"><a href="{{ route('stkstatus') }}">Check the status of the results</a></li>
        </ul>
    </div>

    <!-- Bootstrap JS (Optional: You can include this if you need Bootstrap JavaScript functionality) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
