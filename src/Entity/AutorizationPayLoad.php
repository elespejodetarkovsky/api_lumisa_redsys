<?php

namespace App\Entity;

class AutorizationPayLoad
{

    private string $token;
    private string $amount;
    private string $order;
    private string $dsServerTransId;
    private string $protocolVersion;
    private ?string $dsMethodUrl = null;


    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }

    /**
     * @param string $order
     * @return AutorizationPayLoad
     */
    public function setOrder(string $order): AutorizationPayLoad
    {
        $this->order = $order;
        return $this;
    }


    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return AutorizationPayLoad
     */
    public function setToken(string $token): AutorizationPayLoad
    {
        $this->token = $token;
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
     * @return AutorizationPayLoad
     */
    public function setAmount(string $amount): AutorizationPayLoad
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getDsServerTransId(): string
    {
        return $this->dsServerTransId;
    }

    /**
     * @param string $dsServerTransId
     * @return AutorizationPayLoad
     */
    public function setDsServerTransId(string $dsServerTransId): AutorizationPayLoad
    {
        $this->dsServerTransId = $dsServerTransId;
        return $this;
    }

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $protocolVersion
     * @return AutorizationPayLoad
     */
    public function setProtocolVersion(string $protocolVersion): AutorizationPayLoad
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getDsMethodUrl(): ?string
    {
        return $this->dsMethodUrl;
    }

    /**
     * @param string $dsMethodUrl
     * @return AutorizationPayLoad
     */
    public function setDsMethodUrl(?string $dsMethodUrl = null): AutorizationPayLoad
    {
        $this->dsMethodUrl = $dsMethodUrl;

        return $this;
    }




}