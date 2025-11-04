<?php

namespace Ifthenpay\PaymentGateway\RequestObj;

use Ifthenpay\PaymentGateway\Utils\Validation;

class RegisterWebhookRequest
{
    public string $backofficeKey;
    public string $entity;
    public string $subEntity;
    public string $antiPhishingKey;
    public string $callbackUrl;


    public function __construct(
        string $backofficeKey,
        string $entity,
        string $subEntity,
        string $antiPhishingKey,
        string $callbackUrl
    ) {
        $this->backofficeKey   = $backofficeKey;
        $this->entity          = $entity;
        $this->subEntity       = $subEntity;
        $this->antiPhishingKey = $antiPhishingKey;
        $this->callbackUrl     = $callbackUrl;

        $validationRules = [
            'backofficeKey'   => ['required', 'regex_bokey'],
            'entity'          => ['required', 'enum:MethodCode'],
            'subEntity'       => ['required', 'regex_key'],
            'antiPhishingKey' => ['required', 'min_len:10', 'max_len:50'],
            'callbackUrl'     => ['required', 'max_len:300', 'url'],
        ];

        // this will handle multibanco offline accounts
        if (is_numeric($this->entity)) {
            $validationRules['entity']    = ['required', 'numeric', 'len:5'];
            $validationRules['subEntity'] = ['required', 'numeric', 'min_len:3', 'max_len:4'];
        }


        Validation::validate(
            [
                'backofficeKey'   => $this->backofficeKey,
                'entity'          => $this->entity,
                'subEntity'       => $this->subEntity,
                'antiPhishingKey' => $this->antiPhishingKey,
                'callbackUrl'     => $this->callbackUrl,
            ],
            $validationRules
        );
    }


    /**
     * Converts the RegisterWebhookRequest object to an associative array.
     * @return array<string, string>
     */
    public function toPayload(): array
    {
        return [
            'chave'           => $this->backofficeKey,
            'entidade'        => $this->entity,
            'subentidade'     => $this->subEntity,
            'antiPhishingKey' => $this->antiPhishingKey,
            'urlCb'           => $this->callbackUrl
        ];
    }
}
