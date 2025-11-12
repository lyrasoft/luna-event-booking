<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Field;

use Lyrasoft\EventBooking\EventBookingPackage;
use Lyrasoft\EventBooking\Service\EventPaymentService;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\DI\Attributes\Inject;
use Windwalker\Form\Field\ListField;

class PaymentListField extends ListField
{
    use TranslatorTrait;

    #[Inject]
    protected EventPaymentService $paymentService;

    public function prepareOptions(): array
    {
        $gateways = $this->paymentService->getGateways();
        $options = [];

        foreach ($gateways as $alias => $gateway) {
            $options[$alias] = static::createOption($gateway->getTitle($this->lang), $alias);
        }

        return $options;
    }
}
