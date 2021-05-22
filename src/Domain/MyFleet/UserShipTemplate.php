<?php

namespace App\Domain\MyFleet;

use App\Domain\ShipTemplateId;

class UserShipTemplate
{
    private ShipTemplateId $id;
    private string $model;
    public ?string $pictureUrl;

    public function __construct(ShipTemplateId $id, string $model, ?string $pictureUrl)
    {
        $this->id = $id;
        $this->model = $model;
        $this->pictureUrl = $pictureUrl;
    }

    public function getId(): ShipTemplateId
    {
        return $this->id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }
}
