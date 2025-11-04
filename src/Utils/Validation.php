<?php

namespace Ifthenpay\PaymentGateway\Utils;

use Ifthenpay\PaymentGateway\Enums\Language;
use Ifthenpay\PaymentGateway\Enums\MethodCode;
use InvalidArgumentException;

class Validation
{
    /**
     * Validates data against specified rules.
     * @param array<string, mixed> $data The data to validate.
     * @param array<string, array<string>|string> $rules The validation rules.
     * @throws InvalidArgumentException if validation fails.
     */
    public static function validate(array $data, array $rules): void
    {
        foreach ($rules as $field => $fieldRules) {
            $value     = $data[$field] ?? null;
            $rulesList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            // If is optional (nullable)
            if (in_array('nullable', $rulesList, true) && ($value === null || $value === '')) {
                continue;
            }

            foreach ($rulesList as $rule) {
                $param = null;
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule, 2);
                }


                switch ($rule) {
                    case 'nullable':
                        // Handled above
                        break;
                    case 'required':
                        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                            throw new InvalidArgumentException("'{$field}' is required.");
                        }
                        break;
                    case 'boolean':
                        if (!is_bool($value)) {
                            throw new InvalidArgumentException("'{$field}' must be a boolean.");
                        }
                        break;
                    case 'integer':
                        if (!is_int($value)) {
                            throw new InvalidArgumentException("'{$field}' must be an integer.");
                        }
                        break;
                    case 'min_len':
                        if (strlen((string)$value) < (int)$param) {
                            throw new InvalidArgumentException("'{$field}' length must be equal or greater than {$param} characters.");
                        }
                        break;
                    case 'max_len':
                        if (strlen((string)$value) > (int)$param) {
                            throw new InvalidArgumentException("'{$field}' length must be equal or less than {$param} characters.");
                        }
                        break;

                    case 'min_val':
                        if ((int)$value < (int)$param) {
                            throw new InvalidArgumentException("'{$field}' must be equal or greater than {$param}.");
                        }
                        break;

                    case 'max_val':
                        if ((int)$value > (int)$param) {
                            throw new InvalidArgumentException("'{$field}' must be equal or less than {$param}.");
                        }
                        break;

                    case 'len':
                        if (strlen((string)$value) !== (int)$param) {
                            throw new InvalidArgumentException("'{$field}' length must be exactly {$param} characters.");
                        }
                        break;

                    case 'numeric':
                        if (!is_numeric($value)) {
                            throw new InvalidArgumentException("'{$field}' must be numeric.");
                        }
                        break;
                    case 'positive':
                        if (!is_numeric($value) || (float)$value <= 0) {
                            throw new InvalidArgumentException("'{$field}' must be a positive number greater than 0.");
                        }
                        break;

                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid URL.");
                        }
                        break;

                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid email address.");
                        }
                        break;
                    case 'regex_bokey':
                        if (!preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid backoffice key in the format (e.g. 1111-1111-1111-1111)");
                        }
                        break;

                    case 'regex_key':
                        if (!preg_match('/^[A-Z]{3}-\d{6}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid key (e.g. ITP-000000).");
                        }
                        break;

                    case 'regex_gateway_key':
                        if (!preg_match('/^[A-Z]{4}-\d{6}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid gateway key (e.g. AITP-000000).");
                        }
                        break;
                    case 'regex_method_accounts':
                        if (!preg_match('/^(?:(?:\d{5}\|\d{1,3})|(?:MB|MBWAY|PAYSHOP|CCARD|COFIDIS|GOOGLE|APPLE|PIX)\|[A-Z]{3}-\d{6})(?:;\s?(?:(?:\d{5}\|\d{1,3})|(?:MB|MBWAY|PAYSHOP|CCARD|COFIDIS|GOOGLE|APPLE|PIX)\|[A-Z]{3}-\d{6}))*;?$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid method accounts string (e.g. MBWAY|ITP-000000;PIX|ITP-000000).");
                        }
                        break;
                    case 'regex_no_repeated_methods':
                        $methods = array_map(fn ($part) => explode('|', $part)[0], explode(';', str_replace(' ', '', (string)$value)));
                        if (count($methods) !== count(array_unique($methods))) {
                            throw new InvalidArgumentException("'{$field}' must not contain repeated payment methods.");
                        }
                        break;
                    case 'regex_money':
                        if (!preg_match('/^\d+\.\d{2}$/', (string)$value) || (float)$value <= 0) {
                            throw new InvalidArgumentException("'{$field}' must be a positive decimal number with a '.' as the separator (e.g. 10.50)");
                        }
                        break;

                    case 'regex_date':
                        $format = $param ?: 'Ymd';
                        $d      = \DateTime::createFromFormat($format, (string)$value);
                        if (!($d && $d->format($format) === (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid date in the format {$format}.");
                        }
                        break;

                    case 'regex_mobile':
                        if (!preg_match('/^(351#)?9[123689]\d{7}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid mobile number in the format (e.g. 912345678 or 351#912345678)");
                        }
                        break;

                    case 'regex_cpf':
                        if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid CPF in the format (e.g. 111.111.111-11)");
                        }
                        break;

                    case 'regex_mb_expire_days':
                        if (!preg_match('/^(?:0|[1-9]|[1-2]\d|3[0-2]|45|60|90|120)$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be an integer matching 1 to 32 or 45, 60, 90, 120.");
                        }
                        break;

                    case 'regex_mb_entity':
                        if (!preg_match('/^\d{5}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid Multibanco Offline entity in the format (e.g. 12345)");
                        }
                        break;

                    case 'regex_mb_subentity':
                        if (!preg_match('/^\d{2,3}$/', (string)$value)) {
                            throw new InvalidArgumentException("'{$field}' must be a valid Multibanco Offline subentity in the format (e.g. 12 or 123)");
                        }
                        break;


                    case 'enum':

                        if ($param === 'MethodCode') {
                            if (!MethodCode::tryFrom($value)) {
                                $allowed = array_map(fn ($e) => $e->value, MethodCode::cases());
                                throw new InvalidArgumentException(
                                    "'{$field}' must be one of the following values: " . implode(', ', $allowed) . "."
                                );
                            }
                        } elseif ($param === 'Language') {
                            if (!Language::tryFrom($value)) {
                                $allowed = array_map(fn ($e) => $e->value, Language::cases());
                                throw new InvalidArgumentException(
                                    "'{$field}' must be one of the following values: " . implode(', ', $allowed) . "."
                                );
                            }
                        } else {
                            throw new InvalidArgumentException("Unknown enum parameter '{$param}' for field '{$field}'.");
                        }
                        break;

                    default:
                        throw new InvalidArgumentException("Unknown validation rule '{$rule}' for field '{$field}'.");
                }
            }
        }
    }
}
