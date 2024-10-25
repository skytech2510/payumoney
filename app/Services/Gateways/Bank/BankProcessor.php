<?php

namespace App\Services\Gateways\Bank;

use App\Services\Gateways\Gateway\Exceptions\{
    PaymentReqeustValidationException,
    GatewayInitializeFailedException,
    PaymentFailedException
};
use App\Services\Gateways\Gateway\PaymentProcessor;
use Illuminate\Support\Facades\Validator;
use App\Models\File;
use Throwable;

class BankProcessor extends PaymentProcessor
{
    /**
     * Confirm payment for stripe
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws PaymentFailedException
     */
    public function pay(array $data): array
    {
        $this->validatePaymentConfirmRequest($data);

        isTransactionExist($data['uuid']);

        try {
            $file = $this->handleFile();

            return [
                "action" => "success",
                "message" => __("Payment successful."),
                "attachment" => $file ? $file->id : null,
                "type" => "bank",
                "bank" => $data["bank_id"],
                'redirect_url' => $data['redirect_url']
            ];
        } catch (Throwable $th) {
            throw new PaymentFailedException($th->getMessage());
        }
    }

    /**
     * Handle attachment
     *
     * @return File|null
     */
    private function handleFile()
    {
        if (!request()->hasFile('file')) {
            return null;
        }

        $fileName = request()->file('file');
        $response = uploadImage($fileName, 'public/uploads/files/bank_attached_files/');

        if ($response['status'] === true) {
            $file = new File();
            $file->user_id = auth()->id();
            $file->filename = $response['file_name'];
            $file->originalname = $fileName->getClientOriginalName();
            $file->type = strtolower($fileName->getClientOriginalExtension());
            if($file->save()){
                return $file;
            }
        } else {
            throw new GatewayInitializeFailedException(__("Bank attachment upload failed."));
        }
    }

    /**
     * Get gateway alias name
     *
     * @return string
     */
    public function gateway(): string
    {
        return "bank";
    }

    public function paymentView()
    {
        return 'gateways.'.$this->gateway();
    }


    /**
     * Validate payment confirm request
     *
     * @param array $data
     *
     * @return array
     */
    private function validatePaymentConfirmRequest($data)
    {
        $rules = [
            'payment_method_id'  => 'required',
            'bank_id' => 'required|exists:App\Models\Bank,id',
            'total_amount' => 'required',
            'amount' => 'required',
        ];
        return $this->validateData($data, $rules);
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

    public function getPaymentType(): string
    {
        if (!is_string($this->paymentType)) {
            throw new GatewayInitializeFailedException(__("Payment type not set."));
        }
        return $this->paymentType;
    }

    public function setPaymentType($type): void
    {
        $this->paymentType = $type;
    }
}
