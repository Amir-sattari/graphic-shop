<?php

namespace App\Services\Payment;

use App\Services\Payment\Contracts\RequestInterface;
use App\Services\Payment\Exceptions\ProviderNotFoundException;

class PaymentProvider
{
    public const IDPAY = 'IDPayProvider';
    public const ZARINPAL = 'ZarinpalProvider';

    public function __construct(private string $providerName, private RequestInterface $request)
    {
        
    }

    public function pay()
    {
        return $this->findProvider()->pay();
    }

    private function findProvider()
    {
        $className = 'App\\Services\\Payment\\Providers\\' . $this->providerName;

        if(!class_exists($className))
            throw new ProviderNotFoundException('درگاه پرداخت انتخاب شده یافت نشد');

        return $className($this->request);
    }
}