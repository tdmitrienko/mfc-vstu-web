<?php

declare(strict_types=1);

namespace App\Service\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class EmailOtpStorageService
{
    private const string EMAIL_OTP_KEY = 'sid_%s_email_%s';

    public function __construct(
        private readonly AdapterInterface $adapter,
    )
    {
    }

    public function hasEmailOtp(string $sid, string $email): bool
    {
        return $this->adapter->hasItem($this->getEmailOtpKey($sid, $email));
    }

    public function getEmailOtp(string $sid, string $email): mixed
    {
        $item = $this->adapter->getItem($this->getEmailOtpKey($sid, $email));

        return $item->get();
    }

    public function setEmailOtp(string $sid, string $email, string $otp, int $ttlSec): void
    {
        $item = $this->adapter->getItem($this->getEmailOtpKey($sid, $email));

        $item->set($otp);
        $item->expiresAt(new \DateTimeImmutable("+$ttlSec seconds"));
        $this->adapter->save($item);
    }

    private function getEmailOtpKey(string $sid, string $email)
    {
        $emailNorm = mb_strtolower(trim($email));

        return \sprintf(self::EMAIL_OTP_KEY, $sid, hash('sha256', $emailNorm));
    }
}
