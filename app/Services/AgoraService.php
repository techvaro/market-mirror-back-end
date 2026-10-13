<?php

namespace App\Services;

use InvalidArgumentException;
use Peterujah\Agora\Agora;
use Peterujah\Agora\Builders\RtcToken;
use Peterujah\Agora\Roles;
use Peterujah\Agora\User;

class AgoraService
{
    public function createRtcToken(string $channelName, string $uid, string $role, string $callType): array
    {
        $appId = trim((string) config('services.agora.app_id'));
        $certificate = trim((string) config('services.agora.app_certificate'));

        if ($appId === '' || $certificate === '') {
            throw new InvalidArgumentException('Agora is not configured. Set AGORA_APP_ID and AGORA_APP_CERTIFICATE.');
        }

        $agoraRole = $role === 'publisher' ? Roles::RTC_PUBLISHER : Roles::RTC_SUBSCRIBER;
        $expiresAt = time() + 3600;
        $agora = (new Agora($appId, $certificate))
            ->setChannel($channelName)
            ->setIdentifier($uid)
            ->setRole($agoraRole)
            ->setExpiration($expiresAt);
        $user = (new User($uid))
            ->setChannel($channelName)
            ->setRole($agoraRole)
            ->setPrivilegeExpire($expiresAt);

        return [
            'app_id' => $appId,
            'channel_name' => $channelName,
            'token' => RtcToken::buildTokenWithUserAccount($agora, $user),
            'uid' => $uid,
            'role' => $role,
            'call_type' => $callType,
            'expires_at' => $expiresAt,
        ];
    }
}
