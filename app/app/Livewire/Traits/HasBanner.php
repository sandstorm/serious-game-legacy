<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\Definitions\Card\Dto\ResourceChanges;

trait HasBanner
{
    public string $bannerMessage = '';

    /**
     * We cannot use ResourceChanges directly here because Livewire serializes the properties to JSON.
     * To avoid serialization issues, we use an array instead and handle serialization manually.
     *
     * @var array{
     *      guthabenChange: float,
     *      zeitsteineChange: int,
     *      bildungKompetenzsteinChange: float,
     *      freizeitKompetenzsteinChange: int,
     *  }|null
     */
    public ?array $bannerResourceChanges = null;

    public function showBanner(string $message, ?ResourceChanges $resourceChanges = null): void
    {
        $this->bannerMessage = $message;
        $this->bannerResourceChanges = $resourceChanges?->jsonSerialize() ?? null;
    }

    public function getBannerResourceChanges(): ?ResourceChanges
    {
        return $this->bannerResourceChanges !== null ? ResourceChanges::fromArray($this->bannerResourceChanges) : null;
    }
}
