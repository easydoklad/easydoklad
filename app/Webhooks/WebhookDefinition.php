<?php

namespace App\Webhooks;

use App\Enums\WebhookGroup;

final readonly class WebhookDefinition
{
    public function __construct(
        public string $id,
        public string $description,
        public WebhookGroup $group,
    ) {}
}
