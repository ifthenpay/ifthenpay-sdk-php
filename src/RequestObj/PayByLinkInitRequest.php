<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\DateTools;
use Ifthenpay\PaymentGateway\Utils\Validation;

class PayByLinkInitRequest
{
    public string $payByLinkKey;
    public string $orderId;
    public string $amount;
    public string $methodAccounts;
    public ?string $description;
    public ?string $defaultMethod;
    public ?int $daysToExpire;
    public ?bool $isOneTimePayment;
    public ?string $successUrl;
    public ?string $errorUrl;
    public ?string $cancelUrl;
    public ?string $closeButtonLabel;
    public ?string $closeButtonUrl;
    public ?string $language;








    public function __construct(
        string $payByLinkKey,
        string $orderId,
        string $amount,
        string $methodAccounts,
        ?string $defaultMethod = null,
        ?int $daysToExpire = null,
        ?string $successUrl = null,
        ?string $errorUrl = null,
        ?string $cancelUrl = null,
        ?string $closeButtonUrl = null,
        ?string $description = null,
        ?string $closeButtonLabel = null,
        ?string $language = null,
        ?bool $isOneTimePayment = false,
    ) {

        $this->payByLinkKey     = $payByLinkKey;
        $this->orderId          = $orderId;
        $this->amount           = $amount;
        $this->description      = $description;
        $this->methodAccounts   = $methodAccounts;
        $this->defaultMethod    = $defaultMethod;
        $this->daysToExpire     = $daysToExpire;
        $this->isOneTimePayment = $isOneTimePayment;
        $this->successUrl       = $successUrl;
        $this->errorUrl         = $errorUrl;
        $this->cancelUrl        = $cancelUrl;
        $this->closeButtonLabel = $closeButtonLabel;
        $this->closeButtonUrl   = $closeButtonUrl;
        $this->language         = $language;




        Validation::validate(
            [
                'payByLinkKey'     => $this->payByLinkKey,
                'orderId'          => $this->orderId,
                'amount'           => $this->amount,
                'methodAccounts'   => $this->methodAccounts,
                'successUrl'       => $this->successUrl,
                'errorUrl'         => $this->errorUrl,
                'cancelUrl'        => $this->cancelUrl,
                'closeButtonUrl'   => $this->closeButtonUrl,
                'description'      => $this->description,
                'defaultMethod'    => $this->defaultMethod,
                'daysToExpire'     => $this->daysToExpire,
                'isOneTimePayment' => $this->isOneTimePayment,
                'closeButtonLabel' => $this->closeButtonLabel,
                'language'         => $this->language,
            ],
            [
                'payByLinkKey'     => ['required', 'regex_gateway_key'],
                'orderId'          => ['required', 'max_len:25'],
                'amount'           => ['required', 'regex_money', 'max_len:10'],
                'methodAccounts'   => ['required', 'regex_method_accounts', 'regex_no_repeated_methods'],
                'successUrl'       => ['nullable', 'url', 'max_len:2000'],
                'errorUrl'         => ['nullable', 'url', 'max_len:2000'],
                'cancelUrl'        => ['nullable', 'url', 'max_len:2000'],
                'closeButtonUrl'   => ['nullable', 'url', 'max_len:2000'],
                'closeButtonLabel' => ['nullable', 'max_len:50'],
                'description'      => ['nullable', 'max_len:200'],
                'defaultMethod'    => ['nullable', 'min_val:1', 'max_val:8'],
                'daysToExpire'     => ['nullable', 'integer', 'min_val:0', 'max_val:365'],
                'isOneTimePayment' => ['boolean'],
                'language'         => ['nullable', 'enum:Language'],
            ]
        );
    }


    /**
     * Converts the PayByLinkInitRequest object to an associative array.
     * @return array<string, string|null>
     */
    public function toPayload(): array
    {
        return [
            "id"              => $this->orderId,
            "amount"          => $this->amount,
            "description"     => $this->description,
            "accounts"        => $this->methodAccounts,
            "selected_method" => $this->defaultMethod,
            "expiredate"      => $this->daysToExpire !== null ? DateTools::getFutureDate($this->daysToExpire + 1)->format('Ymd') : null,
            "successUrl"      => $this->successUrl,
            "errorUrl"        => $this->errorUrl,
            "cancelUrl"       => $this->cancelUrl,
            "btnCloseUrl"     => $this->closeButtonUrl,
            "btnCloseLabel"   => $this->closeButtonLabel,
            "otp"             => $this->isOneTimePayment ? "true" : "false",
            "language"        => $this->language,
        ];
    }
}
