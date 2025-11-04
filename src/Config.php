<?php

namespace Ifthenpay\PaymentGateway;

class Config
{
    // general
    private string $backofficeKey;
    private string $antiPhishingKey;
    private string $language = 'pt';

    // mbway
    private string $mbwayKey;

    // multibanco dynamic
    private string $multibancoDynamicKey;

    // multibanco offline
    private string $multibancoOfflineEntity;
    private string $multibancoOfflineSubEntity;

    // payshop
    private string $payshopKey;

    // pixkey
    private string $pixKey;

    // creditcard
    private string $creditCardKey;
    private string $creditCardSuccessUrl;
    private string $creditCardCancelUrl;
    private string $creditCardErrorUrl;

    // cofidis
    private string $cofidisKey;
    private string $cofidisReturnUrl;

    // paybylink
    private string $payByLinkKey;
    /** @var array<string, string> */
    private array $payByLinkMethodAccounts;
    private ?string $payByLinkDefaultMethod = null;
    private string $payByLinkSuccessUrl;
    private string $payByLinkErrorUrl;
    private string $payByLinkCancelUrl;
    private string $payByLinkBtnCloseUrl;
    private ?string $payByLinkBtnCloseLabel = null;
    private bool $payByLinkIsOneTimePayment = false;

    // expiration
    private ?int $mbwayMinutesToExpire          = null;
    private ?int $pixMinutesToExpire            = null;
    private ?int $creditCardMinutesToExpire     = null;
    private ?int $cofidisMinutesToExpire        = null;
    private ?int $multibancoDynamicDaysToExpire = null;
    private ?int $multibancoOfflineDaysToExpire = null;
    private ?int $payshopDaysToExpire           = null;
    private ?int $payByLinkDaysToExpire         = null;

    // endpoints
    /** @var array<string, string> */
    private array $endpoints = [
        'mbway_init'       => 'https://api.ifthenpay.com/spg/payment/mbway',
        'mbway_status'     => 'https://api.ifthenpay.com/spg/payment/mbway/status',
        'multibanco_init'  => 'https://api.ifthenpay.com/multibanco/reference/init',
        'payshop_init'     => 'https://ifthenpay.com/api/payshop/reference/',
        'pix_init'         => 'https://api.ifthenpay.com/pix/init/',
        'creditcard_init'  => 'https://api.ifthenpay.com/creditcard/init/',
        'cofidis_init'     => 'https://api.ifthenpay.com/cofidis/init/',
        'cofidis_status'   => 'https://api.ifthenpay.com/cofidis/status',
        'paybylink_init'   => 'https://api.ifthenpay.com/gateway/pinpay/',
        'paybylink_status' => 'https://api.ifthenpay.com/gateway/transaction/status/get',
        'register_webhook' => 'https://ifthenpay.com/api/endpoint/callback/activation',
        'list_payments'    => 'https://api.ifthenpay.com/v2/payments/read',
    ];


    private function __construct()
    {
        // Private constructor to prevent direct instantiation
    }


    /**
     * Create a Config instance from an associative array.
     * @param array<string, mixed> $configArray
     * @return Config
     * @throws \InvalidArgumentException
     */
    public static function fromArray(array $configArray): self
    {
        $instance = new self();

        foreach ($configArray as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    $property = $key . ucfirst($childKey); // or use camelCase if you prefer
                    if (!property_exists($instance, $property)) {
                        throw new \InvalidArgumentException("Property '$property' does not exist in " . __CLASS__);
                    }
                    $instance->$property = $childValue;
                }
            } else {
                if (!property_exists($instance, $key)) {
                    throw new \InvalidArgumentException("Property '$key' does not exist in " . __CLASS__);
                }
                $instance->$key = $value;
            }
        }

        return $instance;
    }


    public function mbwayKey(): string
    {
        return $this->mbwayKey;
    }

    public function mbwayMinutesToExpire(): ?int
    {
        return $this->mbwayMinutesToExpire;
    }

    public function multibancoDynamicKey(): string
    {
        return $this->multibancoDynamicKey;
    }

    public function multibancoDynamicDaysToExpire(): ?int
    {
        return $this->multibancoDynamicDaysToExpire;
    }

    public function multibancoOfflineEntity(): string
    {
        return $this->multibancoOfflineEntity;
    }

    public function multibancoOfflineSubEntity(): string
    {
        return $this->multibancoOfflineSubEntity;
    }

    public function multibancoOfflineDaysToExpire(): ?int
    {
        return $this->multibancoOfflineDaysToExpire;
    }

    public function payshopKey(): string
    {
        return $this->payshopKey;
    }

    public function payshopDaysToExpire(): ?int
    {
        return $this->payshopDaysToExpire;
    }

    public function pixKey(): string
    {
        return $this->pixKey;
    }

    public function pixMinutesToExpire(): ?int
    {
        return $this->pixMinutesToExpire;
    }

    public function creditCardKey(): string
    {
        return $this->creditCardKey;
    }

    public function creditCardSuccessUrl(): string
    {
        return $this->creditCardSuccessUrl;
    }

    public function creditCardErrorUrl(): string
    {
        return $this->creditCardErrorUrl;
    }

    public function creditCardCancelUrl(): string
    {
        return $this->creditCardCancelUrl;
    }

    public function creditCardMinutesToExpire(): ?int
    {
        return $this->creditCardMinutesToExpire;
    }

    public function cofidisKey(): string
    {
        return $this->cofidisKey;
    }

    public function cofidisMinutesToExpire(): ?int
    {
        return $this->cofidisMinutesToExpire;
    }

    public function cofidisReturnUrl(): string
    {
        return $this->cofidisReturnUrl;
    }

    public function payByLinkKey(): string
    {
        return $this->payByLinkKey;
    }

    /** @return array<string, string> */
    public function payByLinkMethodAccounts(): array
    {
        return $this->payByLinkMethodAccounts;
    }

    public function payByLinkDefaultMethod(): ?string
    {
        return $this->payByLinkDefaultMethod;
    }

    public function payByLinkIsOneTimePayment(): bool
    {
        return $this->payByLinkIsOneTimePayment;
    }

    public function payByLinkDaysToExpire(): ?int
    {
        return $this->payByLinkDaysToExpire;
    }

    public function payByLinkSuccessUrl(): string
    {
        return $this->payByLinkSuccessUrl;
    }

    public function payByLinkErrorUrl(): string
    {
        return $this->payByLinkErrorUrl;
    }

    public function payByLinkCancelUrl(): string
    {
        return $this->payByLinkCancelUrl;
    }

    public function payByLinkBtnCloseUrl(): string
    {
        return $this->payByLinkBtnCloseUrl;
    }

    public function payByLinkBtnCloseLabel(): ?string
    {
        return $this->payByLinkBtnCloseLabel;
    }

    public function language(): string
    {
        return $this->language;
    }



    public function endpoint(string $key): string
    {
        if (!isset($this->endpoints[$key])) {
            throw new \InvalidArgumentException("Endpoint key '{$key}' not found.");
        }

        $endpoint = $this->endpoints[$key];

        return $endpoint;
    }


    public function antiPhishingKey(): string
    {
        return $this->antiPhishingKey;
    }

    public function backofficeKey(): string
    {
        return $this->backofficeKey;
    }

    /**
     * Returns all configuration as an associative array.
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return [
            'backofficeKey'                 => $this->backofficeKey,
            'antiPhishingKey'               => $this->antiPhishingKey,
            'language'                      => $this->language,
            'mbwayKey'                      => $this->mbwayKey,
            'mbwayMinutesToExpire'          => $this->mbwayMinutesToExpire,
            'multibancoDynamicKey'          => $this->multibancoDynamicKey,
            'multibancoDynamicDaysToExpire' => $this->multibancoDynamicDaysToExpire,
            'multibancoOfflineEntity'       => $this->multibancoOfflineEntity,
            'multibancoOfflineSubEntity'    => $this->multibancoOfflineSubEntity,
            'multibancoOfflineDaysToExpire' => $this->multibancoOfflineDaysToExpire,
            'payshopKey'                    => $this->payshopKey,
            'payshopDaysToExpire'           => $this->payshopDaysToExpire,
            'pixKey'                        => $this->pixKey,
            'pixMinutesToExpire'            => $this->pixMinutesToExpire,
            'creditCardKey'                 => $this->creditCardKey,
            'creditCardSuccessUrl'          => $this->creditCardSuccessUrl,
            'creditCardCancelUrl'           => $this->creditCardCancelUrl,
            'creditCardErrorUrl'            => $this->creditCardErrorUrl,
            'creditCardMinutesToExpire'     => $this->creditCardMinutesToExpire,
            'cofidisKey'                    => $this->cofidisKey,
            'cofidisReturnUrl'              => $this->cofidisReturnUrl,
            'cofidisMinutesToExpire'        => $this->cofidisMinutesToExpire,
            'payByLinkKey'                  => $this->payByLinkKey,
            'payByLinkMethodAccounts'       => $this->payByLinkMethodAccounts,
            'payByLinkDefaultMethod'        => $this->payByLinkDefaultMethod,
            'payByLinkIsOneTimePayment'     => $this->payByLinkIsOneTimePayment,
            'payByLinkSuccessUrl'           => $this->payByLinkSuccessUrl,
            'payByLinkErrorUrl'             => $this->payByLinkErrorUrl,
            'payByLinkCancelUrl'            => $this->payByLinkCancelUrl,
            'payByLinkBtnCloseUrl'          => $this->payByLinkBtnCloseUrl,
            'payByLinkBtnCloseLabel'        => $this->payByLinkBtnCloseLabel,
            'payByLinkDaysToExpire'         => $this->payByLinkDaysToExpire,
            'endpoints'                     => $this->endpoints,
        ];
    }



    /**
     * Get a specific configuration value by key.
     * @param string $key The configuration key to retrieve.
     * @param mixed $default The default value to return if the key does not exist.
     * @return mixed The configuration value or the default value.
     */
    public function get(string $key, $default = null)
    {
        return $this->$key ?? $default;
    }
}
