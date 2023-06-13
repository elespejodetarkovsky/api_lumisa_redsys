<?php

namespace App\Entity;

class ConfirmationPayLoad
{

    private string $orderId;
    private ?string $transactionType = '0';
    private string $amount;
    private string $idOper;
    private array $emv3DS;

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     * @return ConfirmationPayLoad
     */
    public function setOrderId(string $orderId): ConfirmationPayLoad
    {
        $this->orderId = $orderId;
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
     * @return ConfirmationPayLoad
     */
    public function setTransactionType(string $transactionType): ConfirmationPayLoad
    {
        $this->transactionType = $transactionType;
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
     * @return ConfirmationPayLoad
     */
    public function setAmount(string $amount): ConfirmationPayLoad
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdOper(): string
    {
        return $this->idOper;
    }

    /**
     * @param string $idOper
     * @return ConfirmationPayLoad
     */
    public function setIdOper(string $idOper): ConfirmationPayLoad
    {
        $this->idOper = $idOper;
        return $this;
    }

    /**
     * @return array
     */
    public function getEmv3DS(): array
    {
        return $this->emv3DS;
    }

    /**
     * @param array $emv3DS
     * @return ConfirmationPayLoad
     */
    public function setEmv3DS(array $emv3DS): ConfirmationPayLoad
    {
        $this->emv3DS = $emv3DS;
        return $this;
    }

    public function __toString(): string
    {
        return '{"orderId":"'.$this->getOrderId().'",'.
            '"amount":"'.$this->getAmount().'",'.
            '"idOper":"'.$this->getIdOper().'",'.
            '"transactionType":"'.$this->getTransactionType().'",'.
            '"emv3ds":'.json_encode($this->getEmv3DS());
    }

    public function toJson(): string
    {
        return '{"orderId":"'.$this->getOrderId().'",'.
            '"amount":"'.$this->getAmount().'",'.
            '"idOper":"'.$this->getIdOper().'",'.
            '"transactionType":"'.$this->getTransactionType().'",'.
            '"emv3ds"'.json_encode($this->getEmv3DS()).'}';
    }

}