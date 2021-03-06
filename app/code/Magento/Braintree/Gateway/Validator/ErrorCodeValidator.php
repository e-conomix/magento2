<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Validator;

use Braintree\Error\ErrorCollection;
use Braintree\Error\Validation;
use Braintree\Result\Error;
use Braintree\Result\Successful;

/**
 * Processes errors codes from Braintree response.
 */
class ErrorCodeValidator
{
    /**
     * Invokes validation.
     *
     * @param Successful|Error $response
     * @return array
     */
    public function __invoke($response)
    {
        if (!$response instanceof Error) {
            return [true, [__('Transaction is successful.')]];
        }

        $errorCodes = $this->getErrorCodes($response->errors);
        if (isset($response->transaction->status) && $response->transaction->status === 'gateway_rejected') {
            $errorCodes[] = $response->transaction->gatewayRejectionReason;
        }
        if (isset($response->transaction->status) && $response->transaction->status === 'processor_declined') {
            $errorCodes[] = $response->transaction->processorResponseCode;
        }

        return [false, $errorCodes];
    }

    /**
     * Retrieves list of error codes from Braintree response.
     *
     * @param ErrorCollection $collection
     * @return array
     */
    private function getErrorCodes(ErrorCollection $collection)
    {
        $result = [];
        /** @var Validation $error */
        foreach ($collection->deepAll() as $error) {
            $result[] = $error->code;
        }

        return $result;
    }
}
