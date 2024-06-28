<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    

</head>
<body>

<div class="container">
<div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice-title">
                        <h4 class="float-end font-size-15">Transaction #{{ $transaction->id }} - Paid</h4>
                        <div class="mb-4">
                           <h2 class="mb-1 text-muted">VCARD WEB APP</h2>
                        </div>
                        <div class="text-muted">
                            <p class="mb-1">P5MH+MJ, Campus 2 - Morro do Lena, Alto do Vieiro, Apt 4163, Edifício D, 2411-901 Leiria</p>
                            <p>244820300</p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="text-muted">
                                <h5 class="font-size-16 mb-3">From: {{ $transaction->type == 'C' && $transaction->pair_vcard == null ? $transaction->payment_type.' - '.$transaction->payment_reference : $transaction->vcardOfTransaction->name }}</h5>
                                <p class="mb-1">{{ $transaction->type == 'C' && $transaction->pair_vcard == null ? '' : $transaction->vcardOfTransaction->name}}</p>
                                <p class="mb-1">{{ $transaction->type == 'C' && $transaction->pair_vcard == null ? '' : $transaction->vcardOfTransaction->email }}</p>
                                <p>{{ $transaction->type == 'C' && $transaction->pair_vcard == null ? '' : $transaction->vcardOfTransaction->phone_number }}</p>
                                <h5 class="font-size-15 mb-3">To: {{ $transaction->pair_vcard != null ? $transaction->transactionPairVcard->name : ($transaction->type == 'D' ? $transaction->payment_type.' '.$transaction->payment_reference : $transaction->vcardOfTransaction->name) }}</h5>
                                <p class="mb-1">{{ $transaction->pair_vcard != null ? $transaction->transactionPairVcard->name : ($transaction->type == 'D' ? '' : $transaction->vcardOfTransaction->name) }}</p>
                                <p class="mb-1">{{ $transaction->pair_vcard != null ? $transaction->transactionPairVcard->email : ($transaction->type == 'D' ? '' : $transaction->vcardOfTransaction->email) }}</p>
                                <p>{{ $transaction->pair_vcard != null ? $transaction->transactionPairVcard->phone_number : ($transaction->type == 'D' ? '' : $transaction->vcardOfTransaction->phone_number) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row d-flex">
                        <div class="col-md-12">
                            <div class="text-muted">
                                <h5 class="font-size-15 mb-3">Transaction Information:</h5>
                                <p class="mb-1">Type: {{ $transaction->type == 'C' ? 'Credit' : 'Debit' }}</p>
                                <p class="mb-1">Date: {{ $transaction->created_at }}</p>
                                <p class="mb-1">Category: {{ $transaction->category == null ? 'No Category' : $transaction->category->name}}</p>
                                <p class="mb-1">Description: {{ $transaction->description == null ? 'No Description' : $transaction->description}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- end col -->
    </div>
</div>
    
</body>
</html>
