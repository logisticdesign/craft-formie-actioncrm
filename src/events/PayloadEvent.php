<?php

namespace logisticdesign\formieactioncrm\events;

use yii\base\Event;

class PayloadEvent extends Event
{
    // Properties
    // =========================================================================

    public ?string $formHandle = null;

    public array $formValues = [];

    public array $payload = [];

    public mixed $response = null;
}
