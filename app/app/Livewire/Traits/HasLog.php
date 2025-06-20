<?php

declare(strict_types=1);

namespace App\Livewire\Traits;


use Domain\CoreGameLogic\EventStore\GameEventInterface;

trait HasLog
{
    public bool $isLogVisible = false;

    public function showLog(): void
    {
        $this->isLogVisible = true;
    }

    public function closeLog(): void
    {
        $this->isLogVisible = false;
    }

    /**
     * @return array<array<mixed>>
     */
    private function getPrettyEvents(): array
    {
        return array_map(function (GameEventInterface $event) {
            $items = [];
            $classPath = explode('\\', $event::class);
            $items['eventClass'] = end($classPath);
            foreach ($event->jsonSerialize() as $key => $item) {
                $items[$key] = json_encode($item);
            }
            return $items;
        }, $this->gameEvents->events);
    }
}
