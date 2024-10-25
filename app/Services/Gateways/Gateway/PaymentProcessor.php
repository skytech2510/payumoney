<?php

/**
 * @package PaymentProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */


namespace App\Services\Gateways\Gateway;

use App\Services\Gateways\Gateway\Exceptions\{
    PaymentReqeustValidationException,
    GatewayInitializeFailedException
};

use App\Models\{
    Currency,
    CurrencyPaymentMethod
};


use BadMethodCallException;
use Exception;
use Illuminate\Support\Facades\Validator;

abstract class PaymentProcessor
{
    protected string $paymentType;

    protected $currency;

    /**
     * Process the payment
     *
     * @param array $data Required data for payment process
     *
     * @return array
     */
    abstract protected function pay(array $data): array;

    /**
     * Provides the gateway name
     *
     * @return string
     */
    abstract public function gateway(): string;


    /**
     * Provides the gateway name
     *
     * @param string $type
     *
     * @return void
     */
    public function setPaymentType($type): void
    {
        $this->paymentType = $type;
    }

    /**
     * Provides the gateway name
     *
     * @return string
     *
     * @throws GatewayInitializeFailedException
     */
    public function getPaymentType(): string
    {
        if (!is_string($this->paymentType)) {
            throw new GatewayInitializeFailedException(__("Payment type not set."));
        }
        return $this->paymentType;
    }


    /**
     * Validate data against rules
     *
     * @param array $data
     * @param array $rules
     *
     * @return array
     *
     * @throws PaymentReqeustValidationException
     */
    public function validateData($data, $rules)
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new PaymentReqeustValidationException(__("Request validation failed."), $validator->errors());
        }

        return $validator->validated();
    }

    public function paymentCurrency()
    {
        $currency = Currency::whereId($this->data['currency_id'])->first();

        if (is_null($currency)) {
            throw new GatewayInitializeFailedException(__("Currency method not found."));
        }

        $this->currency = $currency->code;
    }

    public function paymentMethodCredentials()
    {
        $paymentMethod = CurrencyPaymentMethod::query()
            ->where([
                'currency_id' => $this->data['currency_id'],
                'method_id' => $this->data['payment_method_id'],
            ])->where('activated_for', 'like', "%" . $this->getPaymentType() . "%")
            ->first(['method_data']);

        if (is_null($paymentMethod)) {
            throw new GatewayInitializeFailedException(__("Payment method not found."));
        }
        return json_decode($paymentMethod->method_data);
    }


    public function __call($name, $arguments)
    {
        if (!method_exists($this, $name)) {
            throw new BadMethodCallException(__("Invalid payment method called."));
        }
        if ($name !== 'setPaymentType') {
            $this->getPaymentType();
        }
        return call_user_func([$this, $name], ...$arguments);
    }
}
