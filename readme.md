# ifthenpay SDK (PHP)

A small SDK that aims to facilitate integration with the ifthenpay Gateway API using PHP.

* Generate payments for supported payment methods:
  * MB WAY
  * Multibanco (dynamic or offline)
  * Payshop
  * Credit Card
  * Cofidis
  * Pix
  * Pay By Link

* Check payment status
* Register Webhook
* Validate Webhook

---

* [⚙️ Installation](#%EF%B8%8F-installation)
* [📋 Requirements](#-requirements)
* [🚀 Quick Start and Configuration](#-quick-start-and-configuration)
* [📖 Available Methods](#-available-methods)
* [💻 Examples](#-examples)
* [❔ FAQ](#-faq)

---

## ⚙️ Installation

Add with composer:

```bash
composer require ifthenpay/sdk
```

---

## 📋 Requirements

This SDK works with PHP 8.4, 8.3, 8.2, 8.1.

---

## 🚀 Quick Start and Configuration

```php
require __DIR__ . '/vendor/autoload.php';

use Ifthenpay\PaymentGateway\IfthenpayGateway;

$config = [
    'mbway' => [
        'key' => 'ITP-000000',
    ],
];

$ifthenpayGateway = new IfthenpayGateway($config);

// you can then generate a payment (MB WAY in this case)
$payment = $ifthenpayGateway->mbway()->initPayment('0103', '10,99', '919999999');

```

The snippet below has all possible configuration properties in this SDK.
You are free to omit properties of the configuration, which means you only need to configure the properties of the payment method you are integrating or other functionality like the Webhook.

```php
// examples/configuration/configClosure.php

return [
    'backofficeKey'   => '1111-1111-1111-1111',
    'antiPhishingKey' => 'a0a0a0a0a0a0aa0a0a0a',
    'language'        => 'pt',
    'mbway'           => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 4,
    ],
    'multibancoDynamic' => [
        'key'          => 'ITP-000000',
        'daysToExpire' => 3,
    ],
    'multibancoOffline' => [
        'entity'       => '11111',
        'subEntity'    => '111',
        'daysToExpire' => 3,
    ],
    'payshop' => [
        'key'          => 'ITP-000000',
        'daysToExpire' => 3,
    ],
    'creditCard' => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 15,
        'successUrl'      => 'https://youraddress.com/sucess.php',
        'errorUrl'        => 'https://youraddress.com/error.php',
        'cancelUrl'       => 'https://youraddress.com/cancel.php',
    ],
    'pix' => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 15,
    ],
    'cofidis' => [
        'key'             => 'ITP-000000',
        'minutesToExpire' => 60,
        'returnUrl'      => 'https://youraddress.com/return.php',
    ],
    'payByLink' => [
        'key'            => 'ITPG-000000', // Gateway key, not the same as other account key
        'methodAccounts' => [
            '11111'   => '111',
            'MBWAY'   => 'ITP-000000',
            'PAYSHOP' => 'ITP-000000',
            'CCARD'   => 'ITP-000000',
            'COFIDIS' => 'ITP-000000',
            'GOOGLE'  => 'ITP-000000',
            'APPLE'   => 'ITP-000000',
            'PIX'     => 'ITP-000000',
        ],
        'defaultMethod' => 'CCARD', // MBWAY, MULTIBANCO_DYNAMIC, MULTIBANCO_OFFLINE, PAYSHOP, CREDIT_CARD, COFIDIS, GOOGLE, APPLE, PIX
        'daysToExpire'  => 3,
        'isOneTimePayment' => true,
        'successUrl'    => 'https://youraddress.com/sucess.php',
        'errorUrl'      => 'https://youraddress.com/error.php',
        'cancelUrl'     => 'https://youraddress.com/cancel.php',
        'btnCloseUrl'   => 'https://youraddress.com',
        'btnCloseLabel' => 'Close',
    ],
];
```

**Important Note:** bear in mind that if you try to call a method that relies on a given config, but is not set, it will throw a /ConfigException with a message that will help you realize a config property is missing.

### Using you own HTTP client

This package ships with its own HTTP client (compatible with PSR-18), if you wish to replace it with your own, you can inject it when instantiating the IfthenpayGateway class, as long as it is compatible with PSR-18. Find compatible HTTP clients at [packagist](https://packagist.org/providers/psr/http-client-implementation)

```php
$ifthenpayGateway = new IfthenpayGateway($config, $guzzle);
```

---

## 📖 Available Methods

To use any of the methods shown next, you must first instantiate the main class, after which you will have access to each payment method service object.
There is a payment method service for each of the payment methods that ifthenpay provides, like Multibanco Dynamic, MB WAY, etc.
Each service can be slightly different from its siblings, check the methods docs or refer to the examples for more information.

**Note:** access to a payment method object that does not have the required configuration will return an exception.

### Payment Services

Assuming you instantiated IfthenpayGateway to a variable `$ifthenpayGateway` you can then access the payment services:

```php
$multibancoDynamic = $ifthenpayGateway->multibancoDynamic();
$multibancoOffline = $ifthenpayGateway->multibancoOffline();
$mbway = $ifthenpayGateway->mbway();
$payshop = $ifthenpayGateway->payshop();
$creditCard = $ifthenpayGateway->creditCard();
$cofidis = $ifthenpayGateway->cofidis();
$pix = $ifthenpayGateway->pix();
$payByLink = $ifthenpayGateway->payByLink();
```

You can either assign the service object to a variable or use it directly.

```php
$mbway = $ifthenpayGateway->mbway();
$mbway->initPayment('0103', '10,99', '919999999');
// or 
$ifthenpayGateway->mbway()->initPayment('0103', '10,99', '919999999');
```

### Payment Services methods

With a payment service object, you gain access to its methods, some of which are common to other payment service objects.

* `initPayment()` initiates a payment for the current service and returns a payment model object, which contains all the details necessary to persist that payment in your application/project.
* `registerWebhook()` register a URL as a webhook, this must be a valid URL in your project that will handle the payment status update.
* `validateWebhook()` validates the default parameters of the webhook request, this is a simple parameter validation against your persisted payment model. You can use this method in the controller that receives the webhook to quickly validate the request.
* `isExpired()` checks if the payment has expired, if no expiration time is configured returns false.
* `isPaid()` checks if a payment is complete, that is, if it has been paid by the customer.  
*`getPaymentStatus()`*(MB WAY and Cofidis only)* gets the current status of the transaction.
  * MB WAY, when initialized with success, is in a "pending" status, it can then be "paid", "rejected_by_user", "declined" or "expired".
  * Cofidis, when initialized with success, is in a "pending" status, it can then be "paid", "declined", "canceled" or "error
*`verifyPayment()`*(Credit Card only)*verifies the secret key in the success redirect from the credit card page, allowing you to verify the payment in that way.
*`isTransactionPaid()`*(Pay By Link only)* used to verify the payment, it validates the secret key sent in the Pay By Link success redirect. This method has a very specific use, it requires the transaction ID which is only given when Pay By Link redirects to the success URL. Pay By Link only redirects to the success URL when confirming payment on the gateway page or being redirected from Pay By Link Credit Card or Pix.

#### Multibanco Dynamic

```php
// src/Interface/Service/MultibancoDynamicServiceInterface.php

public function initPayment(string $orderId, string $amount, ?string $description = null, ?int $daysToExpire = null): MultibancoDynamic;
public function isPaid(MultibancoDynamic $multibancoPayment): Bool;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, MultibancoDynamic $multibancoPayment): void;
public function isExpired(MultibancoDynamic $multibancoPayment): bool;
```

#### Multibanco Offline

```php
// src/Interface/Service/MultibancoOfflineServiceInterface.php

public function initPayment(string $orderId, string $amount): MultibancoOffline;
public function isPaid(MultibancoOffline $multibancoPayment): Bool;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, MultibancoOffline $multibancoPayment): void;
public function isExpired(MultibancoOffline $payment): bool;
```

#### MB WAY

```php
// src/Interface/Service/MbwayServiceInterface.php

public function initPayment(string $orderId, string $amount, string $mobileNumber, ?string $description = null, ?string $email = null): Mbway;
public function isPaid(Mbway $mbwayPayment): Bool;
public function getPaymentStatus(string $transactionId): Status;
public function registerWebhook(string $webhookUrl);
public function validateWebhook(WebhookRequest $webhookRequest, Mbway $mbwayPayment): void;
public function isExpired(Mbway $payment): bool;
```

#### Payshop

```php
// src/Interface/Service/PayshopServiceInterface.php

public function initPayment(string $orderId, string $amount, ?int $daysToExpire = null): Payshop;
public function isPaid(Payshop $payshopPayment): Bool;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, Payshop $payshopPayment): void;
public function isExpired(Payshop $payment): bool;
```

#### Credit Card

```php
// src/Interface/Service/CreditCardServiceInterface.php

public function initPayment(string $orderId, string $amount, string $returnUrl, string $language = 'pt'): CreditCard;
public function isPaid(CreditCard $creditCardPayment): Bool;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, CreditCard $creditCardPayment): void;
public function verifyPayment(string $secretKey, CreditCard $payment): void;
public function isExpired(CreditCard $payment): bool;
```

#### Pix

```php
// src/Interface/Service/PixServiceInterface.php

public function initPayment(string $orderId, string $amount, string $cpf, string $name, string $email, string $mobileNumber, string $redirect, ?string $description = null): Pix;
public function isPaid(Pix $pixPayment): Bool;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, Pix $pixPayment): void;
public function isExpired(Pix $payment): bool;
```

#### Cofidis

```php
// src/Interface/Service/CofidisServiceInterface.php

public function initPayment(string $orderId, string $amount, CofidisCustomerData $customerData, ?string $description = null, ?string $returnUrl = null): Cofidis;
public function isPaid(Cofidis $cofidisPayment): Bool;
public function getPaymentStatus(string $transactionId, int $numberOfAttempts = 3): Status;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, Cofidis $cofidisPayment): void;
public function isExpired(Cofidis $payment): bool;
```

#### Pay By Link

```php
// src/Interface/Service/PayByLinkServiceInterface.php

public function initPayment(string $orderId, string $amount, string $description, string $successUrl, string $errorUrl, string $cancelUrl, string $returnUrl, string $language = 'pt'): PayByLink;
public function registerWebhook(string $webhookUrl, ?array $extraParams = null): string;
public function validateWebhook(WebhookRequest $webhookRequest, PayByLink $payByLinkPayment): void;
public function isTransactionPaid(string $transactionId): bool|MethodCode;
public function isExpired(PayByLink $payment): bool;
```

### Payment object

A payment object is what is returned to you when generating a payment, and other methods like `validateWebhook` and `isExpire` expect it as a parameter.

```php
/** @var \Ifthenpay\PaymentGateway\Payments\Mbway\MbwayPayment $payment */
$payment = $ifthenpayGateway->mbway()->initPayment(...$mbwayRequestPayload);
```

All the payment objects implement the same methods:

```php
    // src/Interface/Model/PaymentInterface.php

    public function toArray(): array;
    public function getOrderId(): string;
    public function getAmount(): string;
    public function getTransactionId(): ?string;
    public function getReference(): ?string;
    public function getStatus(): Status;
    public function getExpireDate(): ?DateTimeImmutable;
    public function getCreatedAt(): ?DateTimeImmutable;
    public function getUpdatedAt(): ?DateTimeImmutable;
```

* `toArray()` returns an array version of the current object, it also converts the dates to strings and the Status enum to strings. This can be used if you prefer to deal with arrays and primitives.
* `getOrderId()` returns the order ID.
* `getAmount()` returns the amount.
* `getTransactionId()` returns the transaction ID, it will return null for Multibanco Offline and Pay By Link, since these do not use that property like other payment methods.
* `getReference()` returns the reference, it will return null for **all** payments except Multibanco Dynamic, Multibanco Offline, and Payshop, since only these use that property.
* `getStatus()` returns an enum of the status of the payment /ifthenpay-sdk/src/Enums/Status.php.
* `getExpireDate()` returns the generated date of expiration or null. Note that this date is only meant to facilitate state changing of the payment inside your logic, and it does not directly reflect the inherent expiration of a payment.
* `getCreatedAt()` returns the date of the creation of the payment, note that this uses the Europe/Lisbon timezone.
* `getUpdatedAt()` returns the date of the update of the payment, this property exists for when you wish to add logic that updates the payment status and wish to add an update timestamp.

## 💻 Examples

These are some common use cases shown in a simplified manner, more can be found in the examples folder of this project.

### Create a payment

Use the `initPayment()` method to create a payment using the current payment service.
It will return a payment object, which you can then use to store in your database or execute some logic.
Each payment service provides this method, with some expecting different parameters and return objects.

```php

$ifthenpayGateway = new IfthenpayGateway($config);
$payment = $ifthenpayGateway->mbway()->initPayment('100021', '15.00', '919999999', 'MB WAY Payment Description');
```

### Register webhook

Ifthenpay has a webhook system that, once registered, whenever a payment is completed, will send a GET request to the URL you have registered. You can use this to update a payment status on your implementation.
You may set additional webhook parameters by passing an assoc array in the second parameter, but keep in mind the webhook has a limit of 300 characters.

```php

$ifthenpayGateway = new IfthenpayGateway($config);
$ifthenpayGateway->mbway()->registerWebhook('https://your-payment-status-update-route.com');
```

### Validate webhook

If you registered the webhook using this SDK method, you can use the validateWebhook method to validate the webhook.

```php
// upon receiving the webhook you would get the amount (val), the transaction ID (tid),
// the order ID (oid), and the antiPhishingKey (apk) from the request
// and map them into a WebhookRequest object

$webhookRequest = new WebhookRequest($_GET['val'], $_GET['oid'], $_GET['tid'], $_GET['apk']);

// You would also get the payment record stored in your database (assume $dataArray)
// and map it to a payment object
$storedPayment = new Mbway($dataArray['amount'], $dataArray['orderId'], $dataArray['transactionId'], $dataArray['mobileNumber'], Status::tryFrom($dataArray['status']), $dataArray['expireDate']);

$ifthenpayGateway = new IfthenpayGateway($config);
try {
    $ifthenpayGateway->mbway()->validateWebhook($webhookRequest, $storedPayment);
}
catch {
    // your fail logic
}
// your success logic
```

### Check payment expiration

If you configured an expiration time in the configuration, you can use this method to verify if a payment has expired.
It is a simple function that compares the expiration date, generated during payment creation, against the current time.
Bear in mind that this expiration check serves only to help you update the payment status, for example, set it to Cancelled if not paid in time.

```php
// assuming getting a payment record stored in your database ($dataArray) maps it to a payment object
$storedPayment = new Mbway($dataArray['amount'], $dataArray['orderId'], $dataArray['transactionId'], $dataArray['mobileNumber'], Status::tryFrom($dataArray['status']), $dataArray['expireDate']);

$result = $ifthenpayGateway->mbway()->isExpired($payment);
```

### Check if payment is complete/paid

Allows you to check if the payment is complete.
Except for Pay By Link (which has its own method `isTransactionPaid()` ), each payment method service provides this method.
**Important Note:** this method is intended to be used to check the status at a given point in your payment logic. Because this method's endpoint is subject to rate limiting, it should **not** be used in low-interval cron jobs; in most scenarios, the webhook will do what you need.

```php

// assuming getting a payment record stored in your database ($dataArray) maps it to a payment object
$storedPayment = new Mbway($dataArray['amount'], $dataArray['orderId'], $dataArray['transactionId'], $dataArray['mobileNumber'], Status::tryFrom($dataArray['status']),

$result = $ifthenpayGateway->mbway()->isPaid($storedPayment);
```

### Check payment status (MB WAY and Cofidis)

This method is exclusive to MB WAY and Cofidis and is used to get a status enum of the transaction, since these payments can have different statuses at a given time.
MB WAY, when initialized with success, is in a "pending" status, it can then be "paid", "rejected_by_user", "declined" or "expired".
Cofidis, when initialized with success, is in a "pending" status, it can then be "paid", "declined", "canceled" or "error. D
A common use case in MB WAY is using this method together with a frontend countdown timer to display the transaction status to the buyer.
Cofidis's method lets you set the number of attempts, this was necessary since a few seconds delay may occur while the status is updated.

```php
// assuming getting a payment record stored in your database ($dataArray)
$transactionId = $dataArray['transactionId'];

/** @var \Ifthenpay\PaymentGateway\Enums\Status $mbwayPaymentStatus */
$mbwayPaymentStatus = $ifthenpayGateway->mbway()->getPaymentStatus($transactionId);
```

### Check if payment is complete/paid (Pay By Link)

Check if Pay By Link payment is complete.
**Important Note:** when using Pay By Link, the transaction ID is not readily available; only when the webhook is executed is the transaction ID known, normally in the form of a return on success.
This method is used to verify the success URL redirect.
If the payment is paid, it will return the enum of the payment method used, else, if not paid, it will return false.

```php

$paymentMethod = $ifthenpayGateway->payByLink()->isTransactionPaid($transactionId);

if($paymentMethod) {
    // payment complete using $paymentMethod
}else{
    // payment is pending
}
```

### Verify Credit Card Payment is complete (Credit Card)

This method is an alternative to isPaid().
While isPaid() communicates with the ifthenpay server to know if the payment is complete, this verifies the secret key returned in the success redirect.

```php
// assumes you are redirected from the credit card gateway page
$isPaid = $ifthenpayGateway->creditCard()->verifyPayment($_GET['sk'], $payment);
if($isPaid) {
    // payment successful logic
}else{
    // payment error logic
}
```

### Creating a factory

If you are using the SDK multiple times across your project, you may want to create a factory to handle the config for you instead of configuring it everytime.
Refer to /examples/configuration/factoryWithConfig.php.

```php
// examples/configuration/factoryWithConfig.php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

class IfthenpaySdkFactory
{
    public static function make(): IfthenpayGateway
    {
        $config = [
            'mbway' => [
                'key' => 'ITP-000000',
                'minutesToExpire' => 15,
            ],
        ];

        $ifthenpayGateway = new IfthenpayGateway($config);
        return $ifthenpayGateway;
    }
}

// Usage 
$ifthenpayGateway = IfthenpaySdkFactory::make();

// Now you can use the $ifthenpayGateway instance to access payment methods
// For example, to create a MB WAY payment:
$payment = $ifthenpayGateway->mbway()->initPayment('0103', '10,99', '919999999');

```

### Creating a factory with external config

Take it a step further and separate the config to its own file by creating a closure. This is useful if you want to keep all your project configurations in one folder.

```php
// examples/configuration/configClosure.php

return [
    'backofficeKey' => '1111-1111-1111-1111',
    'antiPhishingKey' => 'a0a0a0a0a0a0aa0a0a0a',
    'language' => 'pt',
    'mbway' => [
        'key' => 'ITP-000000',
        'minutesToExpire' => 15,
    ]
];

```

```php
// examples/configuration/factoryWithConfigClosure.php

use Ifthenpay\PaymentGateway\IfthenpayGateway;

class SdkFactory
{
    public static function make(): IfthenpayGateway
    {
        $config = require 'configClosure.php';

        $ifthenpayGateway = new IfthenpayGateway($config);
        return $ifthenpayGateway;
    }
}
```

## ❔ FAQ

**Q:** Do I need an ifthenpay account to use this SDK?  
**A:** Yes, the methods in this SDK won't work without the correct account keys.

**Q:** How can I test this SDK?  
**A:** This SDK does not have a sandbox functionality, you can ask for a test account from [ifthenpay support](https://helpdesk.ifthenpay.com/)

**Q:** How do I use this SDK in my integration?  
**A:** Use the examples provided in this repository as reference, the most common use cases are shown there.

**Q:** Can I change the code of this package?  
**A:** Sure, if you need to, you are free to customize it as you see fit.

**Q:** Can I use this SDK on versions of PHP older than 8.1?  
**A:** No, unless you refactor it in order to comply with older versions of PHP.

**Q:** If I set the daysToExpire/minutesToExpire and a payment goes without completion, can the customer still pay it?  
**A:** Only Multibanco Dynamic, Payshop, and Pay By Link use the expiration time in the endpoint request and will not be payable after it, meaning if you try to access the link or try to pay, you will encounter an error. The secondary purpose of daysToExpire/minutesToExpire is to help you know when to change the status of your order, in most cases, set it to canceled after the time has passed.

**Q:** How can i set the payments to never expire?  
**A:** You can pass `null` in the daysToExpire/minutesToExpire parameter, **but** beware, some methods have inherent expiration that cannot be changed and will not be payable after that expiration has passed, look up the table to know which ones.

| Payment method     | Inherent expiration                       | Expiration parameter prevents access to payment |
|--------------------|-------------------------------------------|-------------------------------------------------|
| Multibanco Dynamic | Configurable from 0 to 365 days or `null` | yes                                             |
| Multibanco Offline  | none                                     | no                                              |
| Payshop            | Configurable from 0 to 365 days or `null` | yes                                             |
| Ifthenpay Gateway  | Configurable from 0 to 365 days or `null` | yes                                             |
| MB WAY             | 4 minutes                                 | no                                              |
| Credit Card        | none                                      | no                                              |
| Cofidis            | 60 minutes                                | no                                              |
| Pix                | 5 minutes                                 | no                                              |

Note: an expiration of 0 days means it expires on the same day at 23:59.
