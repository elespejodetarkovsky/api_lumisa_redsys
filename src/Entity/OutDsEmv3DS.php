<?php

namespace App\Entity;

class OutDsEmv3DS
{
    private string $threeDSInfo;
    private string $protocolVersion;
    private ?string $acsURL;
    private ?string $creq;
    private ?string $pareq;
    private ?string $md;


    /**
     * @return string|null
     */
    public function getPareq(): ?string
    {
        return $this->pareq;
    }

    /**
     * @param string|null $pareq
     * @return OutDsEmv3DS
     */
    public function setPareq(?string $pareq): OutDsEmv3DS
    {
        $this->pareq = $pareq;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMd(): ?string
    {
        return $this->md;
    }

    /**
     * @param string|null $md
     * @return OutDsEmv3DS
     */
    public function setMd(?string $md): OutDsEmv3DS
    {
        $this->md = $md;
        return $this;
    }


    /**
     * @return string
     */
    public function getCreq(): ?string
    {
        return $this->creq;
    }

    /**
     * @param string $creq
     * @return OutDsEmv3DS
     */
    public function setCreq(?string $creq): OutDsEmv3DS
    {
        $this->creq = $creq;
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
     * @return OutDsEmv3DS
     */
    public function setThreeDSInfo(string $threeDSInfo): OutDsEmv3DS
    {
        $this->threeDSInfo = $threeDSInfo;
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
     * @return OutDsEmv3DS
     */
    public function setProtocolVersion(string $protocolVersion): OutDsEmv3DS
    {
        $this->protocolVersion = $protocolVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcsURL(): ?string
    {
        return $this->acsURL;
    }

    /**
     * @param string $acsURL
     * @return OutDsEmv3DS
     */
    public function setAcsURL(?string $acsURL): OutDsEmv3DS
    {
        $this->acsURL = $acsURL;
        return $this;
    }




}