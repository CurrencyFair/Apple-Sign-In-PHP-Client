<?php

namespace CurrencyFair\AppleId\Response;

use stdClass;

class JwtVerifyResponse
{
    /** @var string */
    private $issuer;

    /** @var string */
    private $audience;

    /** @var string */
    private $issuedAt;

    /** @var string */
    private $expiry;

    /** @var string */
    private $subject;

    /** @var string */
    private $accessTokenHash;

    /** @var string */
    private $email;

    /** @var bool */
    private $emailVerified;

    /** @var bool */
    private $isPrivateEmail;

    /** @var string */
    private $authTime;

    /** @var bool */
    private $nonceSupported;

    /** @var stdClass */
    private $decodedTokenObject;

    /** @var string */
    private $codeHash;

    public function __construct(stdClass $response)
    {
        $this->issuer = isset($response->iss) ? $response->iss : null;
        $this->audience = isset($response->aud) ? $response->aud : null;
        $this->issuedAt = isset($response->iat) ? $response->iat : null;
        $this->expiry = isset($response->exp) ? $response->exp : null;
        $this->subject = isset($response->sub) ? $response->sub : null;
        $this->accessTokenHash = isset($response->at_hash) ? $response->at_hash : null;
        $this->codeHash = isset($response->c_hash) ? $response->c_hash : null;
        $this->email = isset($response->email) ? $response->email : null;
        $this->emailVerified = isset($response->email_verified) ? $response->email_verified : false;
        $this->isPrivateEmail = isset($response->is_private_email) ? $response->is_private_email : false;
        $this->authTime = isset($response->auth_time) ? $response->auth_time : null;
        $this->nonceSupported = isset($response->nonce_supported) ? $response->nonce_supported : false;
        $this->decodedTokenObject = $response;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function getAudience()
    {
        return $this->audience;
    }

    public function getIssuedAt()
    {
        return $this->issuedAt;
    }

    public function getExpiry()
    {
        return $this->expiry;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getAccessTokenHash()
    {
        return $this->accessTokenHash;
    }

    public function getCodeHash()
    {
        return $this->codeHash;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getEmailVerified()
    {
        return $this->emailVerified === 'true';
    }

    public function getIsPrivateEmail()
    {
        return $this->isPrivateEmail === 'true';
    }

    public function getAuthTime()
    {
        return $this->authTime;
    }

    public function getNonceSupported()
    {
        return $this->nonceSupported === 'true';
    }

    /**
     * Return the unmodified decoded token
     *
     * @return stdClass
     */
    public function getDecodedTokenObject()
    {
        return $this->decodedTokenObject;
    }
}
