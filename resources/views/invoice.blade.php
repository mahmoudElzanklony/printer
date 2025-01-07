<!DOCTYPE html>
<html dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Invoice </title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
        <style>
            body { font-family: DejaVu Sans, serif; }
        </style>
    </head>
    <body class="antialiased">
    <section class="bill">
        <div class="container" style="text-align: left }}">
            <div class="receipt">
                <div id="admin_quotation_data">
                </div>
                <div class="mt-3 mb-3">
                    <div class="p-2">
                        <img class="d-block m-auto" style="width: 150px; "
                             src="http://printer.first-meeting.net/images/logo.png">
                        <p class="mt-3 mb-2 font-weight-bold">
                            Bill Number{{--{{ __('keywords.bill_no') }}--}}
                            #{{ $order->id }}
                        </p>
                        <p class="mb-2">
                            <span class="font-weight-bold">{{--{{ __('keywords.tax_no') }}--}}Tax number</span> : <span class="font-weight-bold">310188508400003</span>
                        </p>



                    </div>
                </div>

                <div class="card mb-2">
                    <div class="card-header p-2">
                        <p>{{--{{ __('keywords.client_info') }}--}}Client Information</p>
                    </div>
                    <div class="card-body p-2">
                        <p>
                            <span class="font-weight-bold">{{--{{ __('keywords.client_name') }}--}}Username</span>:
                            <span>{{ $order->user->username }}</span>
                        </p>
                        <p>
                            <span class="font-weight-bold">{{--{{ __('keywords.phone') }}--}}Phone</span>:
                            <span>{{ $order->house_number }}</span>
                        </p>
                        @if($order->user->email)
                            <p>
                                <span class="font-weight-bold">{{--{{ __('keywords.email') }}--}}Email</span>:
                                <span>{{ $order->user->email }}</span>
                            </p>
                        @endif

                        @if(false)
                            <p>
                                <span class="font-weight-bold">{{--{{ __('keywords.address') }}--}}Address</span>:
                                <span>{{ $order->location->address }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card mb-2">
                    <div class="card-header p-2">
                        <p>{{--{{ __('keywords.order_info') }}--}}Order Information</p>
                    </div>
                    <div class="card-body p-2">
                        <p class="mb-2">
                            <span class="font-weight-bold">{{--{{  __('keywords.order_no') }}--}}Order Number</span>:
                            <span>{{ $order->id  }}</span>
                        </p>
                        <p class="mb-2">
                            <span class="font-weight-bold">{{--{{ __('keywords.order_date') }}--}}Order Date</span>:
                            <span >{{ $order->created_at->format('Y-m-d H:i:s') }}</span>
                        </p>
                        <p>
                            <span class="font-weight-bold">{{--{{ __('keywords.order_status') }}--}}Order Status</span>:
                            <span>{{--{{ __('keywords.completed') }}--}}Completed</span>
                        </p>
                        <p>
                            <span class="font-weight-bold">{{--{{ __('keywords.total_money') }}--}}Paid Money</span>:
                            <span>{{ $order->payment->money }} SAR</span>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>
    </body>
</html>
