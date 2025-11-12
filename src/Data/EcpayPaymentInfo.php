<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Windwalker\Data\RecordTrait;

class EcpayPaymentInfo
{
    use RecordTrait;

    public string $RtnMsg = '';

    public int $RtnCode = 0 {
        set(int|string $value) => (int) $value;
    }

    public string $StoreID = '';

    public string $TradeNo = '';

    public string $BankCode = '';

    public int $TradeAmt = 0 {
        set(int|string $value) => (int) $value;
    }

    public string $vAccount = '';

    public string $TradeDate = '';

    public string $ExpireDate = '';

    public string $MerchantID = '';

    public string $PaymentType = '';

    public string $CustomField1 = '';

    public string $CustomField2 = '';

    public string $CustomField3 = '';

    public string $CustomField4 = '';

    public string $CheckMacValue = '';

    public string $MerchantTradeNo = '';
}
