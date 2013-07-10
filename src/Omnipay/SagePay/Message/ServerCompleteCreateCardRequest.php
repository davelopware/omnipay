<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Dave Amphlett <dave@davelopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\SagePay\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Sage Pay Server Complete Authorize Request
 */
class ServerCompleteCreateCardRequest extends AbstractRequest
{
     /**
     * This retrieves the transaction id from the raw request so that you can
     * retrieve the full transactionReference from your stored
     * and setTransactionReference() into this request before calling getData()
     * since getData will validate the signature of this request and needs
     * the VPSTxId, VendorTxCode and SecurityKey that were stashed in the
     * transactionReference!
     * @return string
     */
    public function getRequestTransactionId()
    {
        return $this->httpRequest->request->get('VendorTxCode');
    }

    /**
     * The token generated by SagePay and provided in response to the
     * registration phase
     * @return string
     */
    public function getCardReference()
    {
        return isset($this->data['Token']) ? $this->data['Token'] : null;
    }

    /**
     * VISA, MC, DELTA, MAESTRO, UKE, AMEX, DC, JCB, LASER, PAYPAL
     * @return string
     */
    public function getCardType()
    {
        return isset($this->data['CardType']) ? $this->data['CardType'] : null;
    }

    /**
     * The last 4 digits of the card number used in this transaction.
     * PayPal transactions have 0000
     * @return string
     */
    public function getLast4Digits()
    {
        return isset($this->data['Last4Digits']) ? $this->data['Last4Digits'] : null;
    }

    /**
     * The Expiy date (required for ALL cards) in MMYY format
     * @return string
     */
    public function getExpiryDate()
    {
        return isset($this->data['ExpiryDate']) ? $this->data['ExpiryDate'] : null;
    }

    public function getData()
    {
        $this->validate('vendor', 'transactionReference');

        $reference = json_decode($this->getTransactionReference(), true);

        // validate VPSSignature
        $signature = md5(
            $reference['VPSTxId'].
            $reference['VendorTxCode'].
            $this->httpRequest->request->get('Status').
            $this->httpRequest->request->get('TxAuthNo').
            $this->getVendor().
            $this->httpRequest->request->get('Token').
            $reference['SecurityKey']
        );

        if (strtolower($this->httpRequest->request->get('VPSSignature')) !== $signature) {
            throw new InvalidResponseException;
        }

        return $this->httpRequest->request->all();
    }

    public function send()
    {
        return $this->response = new ServerCompleteCreateCardResponse($this, $this->getData());
    }
}
