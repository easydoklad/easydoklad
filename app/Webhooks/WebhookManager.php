<?php


namespace App\Webhooks;


use Illuminate\Support\Collection;

class WebhookManager
{
    /**
     * List of available webhook events.
     */
    protected array $events = [];

    /**
     * Get list of available webhook definitions.
     *
     * @return \Illuminate\Support\Collection<int, \App\Webhooks\WebhookDefinition>
     */
    public function getAvailableEventDefinitions(): Collection
    {
        return collect($this->events)->map(fn (string $event) => $event::define());
    }

    /**
     * Register a Webhook event.
     */
    public function registerEvent(string $event): static
    {
        $this->events[] = $event;

        return $this;
    }
}
