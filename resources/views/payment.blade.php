<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Payment</title>


    <link rel="stylesheet" href="https://checkoutshopper-live.adyen.com/checkoutshopper/sdk/3.6.1/adyen.css"
            integrity="sha384-l5/gSrWMFWCKnEqoG1F21fvhDesLnZt/JlXjkA0FWp6E68Pc/9mxg+nPvvx+uB4G"
            crossorigin="anonymous">
    <!-- Adyen provides the SRI hash that you include as the integrity attribute. Refer to our release notes to get the SRI hash for the specific version. https://docs.adyen.com/checkout/components-drop-in-release-notes -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

</head>
<body>

    <div class="container">
        <div class="row">
            <div class="col-5 mt-3">
                <h2>Payment</h2>
                <button onclick="return pay()" class="btn btn-primary">Pay</button>
                <button onclick="return payByLink()" class="btn btn-primary">Pay by Link</button>
            </div>
        </div>
    </div>

    @include('components.modal-payment')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

    <script src="https://checkoutshopper-live.adyen.com/checkoutshopper/sdk/3.6.1/adyen.js"
            integrity="sha384-hUb/CFxzLJZWUbDBmQfccbVjE3LFxAx3Wt4O37edYVLZmNhcmVUyYLgn6kWk3Hz+"
            crossorigin="anonymous"></script>
    <!-- Adyen provides the SRI hash that you include as the integrity attribute. Refer to our release notes to get the SRI hash for the specific version. https://docs.adyen.com/checkout/components-drop-in-release-notes -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>

    <script>

        window.payByLink = function() {
            Swal.fire({
                title: 'Proccesing Payment...',
                showConfirmButton: false
            })

            fetch('/payment/create-link').then(function(res){ return res.json() })
            .then( async function(response){
                swal.close()

                
                var strWindowFeatures = "location=yes,height=600,width=520,scrollbars=no,status=no";
                var URL = response.url
                var win = window.open(URL, "_blank", strWindowFeatures);

                setTimeout(function(){
                    window.location = '/'
                }, 2000 )

                // console.log('ss')
            })
        }

        window.makePayment = function(data) {
            return fetch('/payment/make', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify({
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    payment_data: data
                })
            }).then(function(res){ return res.json() });
        }

        window.pay = function () {

            Swal.fire({
                title: 'Proccesing Payment...',
                showConfirmButton: false
            })

            fetch('/payment/method').then(function(res){
                return res.json()
            }).then(function(paymentMethodsResponse){
                $('#modal-payment').modal('show')
                Swal.close()

                const configuration = {
                    locale: "en-US", // The shopper's locale. For a list of supported locales, see https://docs.adyen.com/checkout/components-web/localization-components.
                    environment: "test", // When you're ready to accept live payments, change the value to one of our live environments https://docs.adyen.com/checkout/drop-in-web#testing-your-integration.  
                    originKey: "pub.v2.8015845077190523.aHR0cDovL2xvY2FsaG9zdDo4MDAw.4yuH_4C3jQPEEUhyh7L560SWy4E7uUc7R8Q7hb0mt-g",
                    paymentMethodsResponse: paymentMethodsResponse // The payment methods response returned in step 1.
                };
                const checkout = new AdyenCheckout(configuration);

                const dropin = checkout.create('dropin', {
                paymentMethodsConfiguration: {
                    card: { // Example optional configuration for Cards
                        hasHolderName: true,
                        holderNameRequired: true,
                        enableStoreDetails: true,
                        hideCVC: false, // Change this to true to hide the CVC field for stored cards
                        name: 'Credit or debit card'
                    }
                },
                onSubmit: (state, dropin) => {
                    // console.log(state.data)
                    makePayment(state.data)
                    .then(payment_response => {
                        // dropin.handleAction(action);
                        if(payment_response.resultCode == 'Authorised') {
                            dropin.setStatus('success', { message: 'Payment successful!' });

                            setTimeout(function(){
                                window.location = '/'
                            }, 2000 )
                        }
                        // Drop-in handles the action object from the /payments response
                    })
                    .catch(error => {
                        console.log('payment', error)
                    });
                },
                })
                .mount('#dropin');
            })

            return false;
        }

    </script>
    
</body>
</html>