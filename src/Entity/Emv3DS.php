<?php

namespace App\Entity;

class Emv3DS
{
    private string $protocolVersion;
    private string $threeDServerTransID;
    private string $threeDSInfo;
    private ?string $threeDSMethodURL;

    private string $cardPSD2;

    /**
     * @return string
     */
    public function getCardPSD2(): string
    {
        return $this->cardPSD2;
    }

    /**
     * @param string $cardPSD2
     * @return Emv3DS
     */
    public function setCardPSD2(string $cardPSD2): Emv3DS
    {
        $this->cardPSD2 = $cardPSD2;
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
     * @return Emv3DS
     */
    public function setProtocolVersion(string $protocolVersion): Emv3DS
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDServerTransID(): string
    {
        return $this->threeDServerTransID;
    }

    /**
     * @param string $threeDServerTransID
     * @return Emv3DS
     */
    public function setThreeDServerTransID(string $threeDServerTransID): Emv3DS
    {
        $this->threeDServerTransID = $threeDServerTransID;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSInfo(): string
    {
        return $this->threeDSInfo;
    }

    /**
     * @param string $threeDSInfo
     * @return Emv3DS
     */
    public function setThreeDSInfo(string $threeDSInfo): Emv3DS
    {
        $this->threeDSInfo = $threeDSInfo;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSMethodURL(): string
    {
        return $this->threeDSMethodURL;
    }

    /**
     * @param string $threeDSMethodURL
     * @return Emv3DS
     */
    public function setThreeDSMethodURL(?string $threeDSMethodURL): Emv3DS
    {
        $this->threeDSMethodURL = $threeDSMethodURL;
        return $this;
    }





}