<?php

namespace App\Entity;

class Challenge
{
    private string $amount;
    private string $currency;
    private string $order;
    private string $merchantCode;
    private string $terminal;
    private string $transactionType;

    private OutDsEmv3DS $outDsEmv3DS;

    /**
     * @return OutDsEmv3DS
     */
    public function getOutDsEmv3DS(): OutDsEmv3DS
    {
        return $this->outDsEmv3DS;
    }

    /**
     * @param OutDsEmv3DS $outDsEmv3DS
     * @return Challenge
     */
    public function setOutDsEmv3DS(array $outDsEmv3DS): Challenge
    {
        //TODO colocar valores de la devolucion

        $emv3ds = new OutDsEmv3DS();

        dd($outDsEmv3DS);

        $emv3ds->setAcsURL($outDsEmv3DS['acsURL'])
            ->setCreq($outDsEmv3DS['PAReq'])
            ->setProtocolVersion('2.1.0')
            ->setThreeDSInfo('ChallengeRequest');

        $this->outDsEmv3DS = $emv3ds;

        return $this;
    }


    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return Challenge
     */
    public function setAmount(string $amount): Challenge
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Challenge
     */
    public function setCurrency(string $currency): Challenge
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return Challenge
     */
    public function setOrder(string $order): Challenge
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    /**
     * @param string $merchantCode
     * @return Challenge
     */
    public function setMerchantCode(string $merchantCode): Challenge
    {
        $this->merchantCode = $merchantCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getTerminal(): string
    {
        return $this->terminal;
    }

    /**
     * @param string $terminal
     * @return Challenge
     */
    public function setTerminal(string $terminal): Challenge
    {
        $this->terminal = $terminal;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionType(): string
    {
        return $this->transactionType;
    }

    /**
     * @param string $transactionType
     * @return Challenge
     */
    public function setTransactionType(string $transactionType): Challenge
    {
        $this->transactionType = $transactionType;
        return $this;
    }


}