<?php

namespace App\Services;

use Carbon\Carbon;

class AgoraService
{
    public function generateToken($channelName, $uid)
    {
        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $expireTimeInSeconds = 3600; // 1 hour

        $currentTimestamp = Carbon::now()->timestamp;
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        // Agora PHP SDK ke sath token generate
        $token = \RtcTokenBuilder::buildTokenWithUid(
            $appID,
            $appCertificate,
            $channelName,
            $uid,
            \RtcTokenBuilder::RolePublisher,
            $privilegeExpiredTs
        );

        return $token;
    }
}
