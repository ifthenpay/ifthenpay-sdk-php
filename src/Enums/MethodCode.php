<?php

namespace Ifthenpay\PaymentGateway\Enums;

enum MethodCode: string
{
    case MBWAY              = 'MBWAY';
    case MULTIBANCO_DYNAMIC = 'MB';
    case MULTIBANCO_OFFLINE  = 'MULTIBANCO'; // Set for clarity, the entity number is internally used to identify the method
    case PAYSHOP            = 'PAYSHOP';
    case CREDIT_CARD        = 'CCARD';
    case PIX                = 'PIX';
    case COFIDIS            = 'COFIDIS';
    case GOOGLE             = 'GOOGLE';
    case APPLE              = 'APPLE';
    case PAY_BY_LINK        = 'PAYBYLINK';
}
