<?php

namespace App\Domain;

class UserShip
{
    private ShipId $shipId;
    private string $model;
    private ?string $imageUrl;
    private int $quantity;

    public function __construct(ShipId $shipId, string $model, ?string $imageUrl, int $quantity)
    {
        $this->shipId = $shipId;
        $this->model = $model;
        $this->imageUrl = $imageUrl;
        $this->quantity = $quantity;
    }

    public function getShipId(): ShipId
    {
        return $this->shipId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
