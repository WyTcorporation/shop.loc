<?php

namespace App\Services\Marketing;

use App\Models\CampaignTemplate;
use App\Models\MarketingCampaign;
use Illuminate\Support\Arr;

class TemplateService
{
    public function renderSubject(MarketingCampaign $campaign, ?CampaignTemplate $template = null, array $data = []): string
    {
        $template ??= $campaign->template;
        $subject = $template?->subject ?? $campaign->name;

        return $this->interpolate($subject, $data);
    }

    public function renderContent(MarketingCampaign $campaign, ?CampaignTemplate $template = null, array $data = []): string
    {
        $template ??= $campaign->template;
        $content = $template?->content ?? '';

        return $this->interpolate($content, $data);
    }

    /**
     * Very small helper to replace tokens like {{ user.name }} in template strings.
     */
    protected function interpolate(?string $content, array $data): string
    {
        $content ??= '';

        return (string) preg_replace_callback('/{{\s*([^}\s]+)\s*}}/', function (array $matches) use ($data): string {
            $key = $matches[1] ?? '';
            $value = Arr::get($data, $key);

            return is_scalar($value) ? (string) $value : '';
        }, $content);
    }
}
