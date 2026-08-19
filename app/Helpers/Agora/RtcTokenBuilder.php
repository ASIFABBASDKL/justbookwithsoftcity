<?php

// Agora PHP Token Builder
// Source: https://github.com/AgoraIO/AgoraDynamicKey

class RtcTokenBuilder
{
    const RolePublisher = 1;
    const RoleSubscriber = 2;

    /**
     * Build token with uid.
     *
     * @param string $appID
     * @param string $appCertificate
     * @param string $channelName
     * @param int    $uid
     * @param int    $role
     * @param int    $privilegeExpiredTs
     *
     * @return string
     */
    public static function buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        return AccessToken::initWithUid(
            $appID, $appCertificate, $channelName, $uid
        )->build($role, $privilegeExpiredTs);
    }

    /**
     * Build token with account name.
     *
     * @param string $appID
     * @param string $appCertificate
     * @param string $channelName
     * @param string $account
     * @param int    $role
     * @param int    $privilegeExpiredTs
     *
     * @return string
     */
    public static function buildTokenWithAccount($appID, $appCertificate, $channelName, $account, $role, $privilegeExpiredTs)
    {
        return AccessToken::initWithAccount(
            $appID, $appCertificate, $channelName, $account
        )->build($role, $privilegeExpiredTs);
    }
}

/**
 * AccessToken class used internally.
 */
class AccessToken
{
    private $appID;
    private $appCertificate;
    private $channelName;
    private $uid;
    private $salt;
    private $ts;
    private $message = [];

    private function __construct($appID, $appCertificate, $channelName, $uid)
    {
        $this->appID = $appID;
        $this->appCertificate = $appCertificate;
        $this->channelName = $channelName;
        $this->uid = $uid;
        $this->ts = time();
        $this->salt = rand(1, 99999999);
    }

    public static function initWithUid($appID, $appCertificate, $channelName, $uid)
    {
        return new self($appID, $appCertificate, $channelName, $uid);
    }

    public static function initWithAccount($appID, $appCertificate, $channelName, $account)
    {
        return new self($appID, $appCertificate, $channelName, $account);
    }

    public function build($role, $privilegeExpiredTs)
    {
        $content = $this->appID . $this->appCertificate . $this->channelName . $this->uid . $this->ts . $this->salt;
        $signature = hash_hmac('sha256', $content, $this->appCertificate, true);

        return base64_encode($signature . $content . $privilegeExpiredTs . $role);
    }
}
