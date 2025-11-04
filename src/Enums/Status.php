<?php

namespace Ifthenpay\PaymentGateway\Enums;

enum Status: string
{
    case PENDING          = 'pending';
    case ERROR            = 'error';
    case INITIALIZED      = 'initialized';
    case PAID             = 'paid';
    case CANCELED         = 'canceled';
    case REJECTED_BY_USER = 'rejected_by_user';
    case EXPIRED          = 'expired';
    case DECLINED         = 'declined';
}
