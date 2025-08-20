<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use Domain\Definitions\Card\Dto\ResourceChanges;

trait HasBanner
{
    public bool $isBannerVisible = false;
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

    public function showBanner(string $message, ResourceChanges $resourceChanges = null): void
    {
        $this->isBannerVisible = false;
        $this->bannerMessage = $message;
        $this->bannerResourceChanges = $resourceChanges?->jsonSerialize() ?? null;
        $this->isBannerVisible = true;
    }

    public function closeBanner(): void
    {
        $this->isBannerVisible = false;
        $this->bannerResourceChanges = null;
        $this->bannerMessage = '';
    }

    public function getBannerResourceChanges(): ?ResourceChanges
    {
        return $this->bannerResourceChanges !== null ? ResourceChanges::fromArray($this->bannerResourceChanges) : null;
    }
}
