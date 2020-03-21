<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;

use GuzzleHttp\Client;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    
    public function __construct()
    {
        $this->username         = 'admin';
        $this->password         = 'demoBaba3#';
        $this->appKey           = 'AQEmhmfuXNWTK0Qc+iSRuEUKhueYR55DGZNL/Lar5BzEzrX7b0PPQIMQwV1bDb7kfNy1WIxIIkxgBw==-4ci/ImKAmatuVGyIqgV4OW4C4hQOnPe364Gi4j0/Usw=-}3rM[Fg@Lm+~3^98';
        $this->merchantAccount  = 'AJARAccountECOM';
    }
    
    public function paymentMethod()
    {
        $client = new \Adyen\Client();
        $client->setApplicationName("Ajar.ID");
        $client->setUsername( $this->username );
        $client->setPassword( $this->password );
        $client->setXApiKey( $this->appKey);
        $client->setEnvironment(\Adyen\Environment::TEST);

        $service = new \Adyen\Service\Checkout($client);
 
        $params = array(
        "merchantAccount" => $this->merchantAccount,
        "countryCode" => "ID",
        "amount" => array(
            "currency" => "IDR",
            "value" => 10000
        ),
        "channel" => "Web"
        );
        
        $result = $service->paymentMethods($params);
        return response()->json($result);
    }

    public function makePayment(Request $req)
    {
        
        // dd($req->payment_data);
        $client = new \Adyen\Client();
        $client->setXApiKey($this->appKey);
        $client->setEnvironment(\Adyen\Environment::TEST);

        $service = new \Adyen\Service\Checkout($client);
        
        $paymentMethod = $req->payment_data['paymentMethod'];
        // Data object passed from onSubmit event of the front end parsed from JSON to an array
        // dd($paymentMethod);
        $params = array(
            "amount" => array(
                "currency" => "IDR",
                "value" => 300000
            ),
            "reference" => "93290002",
            "paymentMethod" => $paymentMethod,
            "returnUrl" => "http://localhost:8000",
            "merchantAccount" => $this->merchantAccount
        );

        $result = $service->payments($params);
        
        // dd($result); 
        // Check if further action is needed
        if ( array_key_exists("action", $result) ){
            // Pass the action object to your front end
            // $result["action"]
        } else {
            // No further action needed, pass the resultCode to your front end
            // $result['resultCode']
            return response()->json($result);
        }
    }

    public function createLink(Request $req)
    {

        $json = '{
            "reference": "91002323",
            "amount": {
              "value": 3000,
              "currency": "IDR"
            },
            "countryCode": "BR",
            "merchantAccount": "'. $this->merchantAccount .'",
            "shopperReference": "91",
            "shopperEmail": "test@email.com",
            "shopperLocale": "id_ID",
            "billingAddress": {
              "street": "Roque Petroni Jr",
              "postalCode": "59000060",
              "city": "São Paulo",
              "houseNumberOrName": "999",
              "country": "BR",
              "stateOrProvince": "SP"
            },
            "deliveryAddress": {
              "street": "Roque Petroni Jr",
              "postalCode": "59000060",
              "city": "São Paulo",
              "houseNumberOrName": "999",
              "country": "BR",
              "stateOrProvince": "SP"
            }
        }';


        $client = new Client(['base_uri' => 'https://checkout-test.adyen.com']);

        $options = [
            'headers' => [
                'X-Api-Key'     => $this->appKey,
                'Content-Type'  => 'application/json'
            ],
            'json' => json_decode($json,true)
        ];

        $res = $client->request('POST', '/v52/paymentLinks', $options);
        
        return response()->json(json_decode($res->getBody()));
        
    }
}
