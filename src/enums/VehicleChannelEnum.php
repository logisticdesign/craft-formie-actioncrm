<?php

namespace logisticdesign\formieactioncrm\enums;

enum VehicleChannelEnum: string
{
    case NEW = 'NEW';
    case USED = 'USED';
    case KM0 = 'KM0';

    public function isNew(): bool
    {
        return $this === self::NEW;
    }

    public function isUsed(): bool
    {
        return $this === self::USED;
    }

    public function isKm0(): bool
    {
        return $this === self::KM0;
    }
}
