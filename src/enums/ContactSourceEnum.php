<?php

namespace logisticdesign\formieactioncrm\enums;

enum ContactSourceEnum: string
{
    case EMAIL = 'EMAIL';
    case CHAT = 'CHAT';
    case TEL = 'TEL';
    case WALKIN = 'WALKIN';
}
