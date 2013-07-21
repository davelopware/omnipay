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

use Omnipay\Common\Message\RequestInterface;

/**
 * Sage Pay Server Complete Authorize Response
 */
class ServerCompleteCreateCardResponse extends Response
{
    const STATUS_OK = 'OK';
    const STATUS_MALFORMED = 'MALFORMED';
    const STATUS_INVALID = 'INVALID';
    const STATUS_ERROR = 'ERROR';
    
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
    }
    
    /**
     * The unique id allocated by your code to represent this transaction
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getRequest()->getTransactionId();
    }

    /**
     * The token generated by SagePay and provided in response to the
     * registration phase
     * @return string
     */
    public function getCardReference()
    {
        return (isset($this->data['Token'])) ? $this->data['Token'] : null;
    }


    /**
     * ServerCompleteCreateCardResponse::STATUS_OK
     *    Process executed without error
     * ServerCompleteCreateCardResponse::STATUS_MALFORMED
     *    ??? Input message was missing fields or badly formatted - should
     *    only really occur during development!
     * ServerCompleteCreateCardResponse::STATUS_INVALID
     *    ??? Transaction was not registered because although the POST format
     *    was valid, some information supplied was invalid. eg. incorrect
     *    vendor name or currency.
     * ServerCompleteCreateCardResponse::STATUS_ERROR
     *    A problem occurred at Sage Pay which prevented transaction registration
     * @return string
     */
    public function getStatus()
    {
        return (isset($this->data['Status'])) ? $this->data['Status'] : null;
    }

    /**
     * VISA, MC, DELTA, MAESTRO, UKE, AMEX, DC, JCB, LASER, PAYPAL
     * @return string
     */
    public function getCardType()
    {
        return (isset($this->data['CardType'])) ? $this->data['CardType'] : null;
    }
    
    /**
     * The last 4 digits of the card number used in this transaction.
     * PayPal transactions have 0000
     * @return string
     */
    public function getLast4Digits()
    {
        return (isset($this->data['Last4Digits'])) ? $this->data['Last4Digits'] : null;
    }
    
    /**
     * The Expiy date (required for ALL cards) in MMYY format
     * @return string
     */
    public function getExpiryDate()
    {
        return (isset($this->data['ExpiryDate'])) ? $this->data['ExpiryDate'] : null;
    }

    /**
     * Confirm (Sage Pay Server only)
     *
     * Sage Pay Server does things backwards compared to every other gateway (including Sage Pay
     * Direct). The return URL is called by their server, and they expect you to confirm receipt
     * and then pass a URL for them to forward the customer to.
     *
     * Because of this, an extra step is required. In your return controller, after calling
     * $gateway->completePurchase(), you should update your database with details of the
     * successful payment. You must then call $response->confirm() to notify Sage Pay you
     * received the payment details, and provide a URL to forward the customer to.
     *
     * Keep in mind your original confirmPurchase() script is being called by Sage Pay, not
     * the customer.
     *
     * @param string URL to foward the customer to. Note this is different to your standard
     *               return controller action URL.
     */
    public function confirm($nextUrl)
    {
        $responseMsg = "Status=OK\r\nStatusDetail=All Good\r\nRedirectURL=".$nextUrl;
        exit($responseMsg);
    }
}
