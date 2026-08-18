<?php

namespace App\Support;

/**
 * Holds the active buyer market for the current request. Populated by the
 * SetMarketContext middleware so services can scope buyer queries.
 */
class MarketContext
{
    private ?int $marketId = null;

    public function set(?int $marketId): void
    {
        $this->marketId = $marketId;
    }

    public function get(): ?int
    {
        return $this->marketId;
    }

    public function has(): bool
    {
        return $this->marketId !== null;
    }

    public function clear(): void
    {
        $this->marketId = null;
    }
}
