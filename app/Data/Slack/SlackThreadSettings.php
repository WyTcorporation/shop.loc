<?php

namespace App\Data\Slack;

class SlackThreadSettings
{
    public function __construct(
        public readonly string $webhookUrl,
        public readonly ?string $channel = null,
        public readonly ?string $threadTs = null,
    ) {
    }

    public static function fromConfig(array $config): ?self
    {
        $webhookUrl = (string) ($config['webhook_url'] ?? '');

        if ($webhookUrl === '') {
            return null;
        }

        $channel = $config['channel'] ?? null;
        $threadTs = $config['thread_ts'] ?? null;

        return new self(
            $webhookUrl,
            is_string($channel) && $channel !== '' ? $channel : null,
            is_string($threadTs) && $threadTs !== '' ? $threadTs : null,
        );
    }

    public function applyToPayload(array $payload): array
    {
        $settings = [];

        if ($this->channel !== null) {
            $settings['channel'] = $this->channel;
        }

        if ($this->threadTs !== null) {
            $settings['thread_ts'] = $this->threadTs;
        }

        return array_merge($payload, $settings);
    }
}
