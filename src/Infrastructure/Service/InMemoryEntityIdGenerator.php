<?php

namespace App\Infrastructure\Service;

use App\Domain\EntityId;
use App\Domain\Service\EntityIdGeneratorInterface;
use Webmozart\Assert\Assert;

class InMemoryEntityIdGenerator implements EntityIdGeneratorInterface
{
    private string $uid;
    private mixed $callback;

    public function __construct()
    {
        $this->uid = '00000000-0000-0000-0000-000000000001';
        $this->callback = static function (string $oldUid): string {
            $n = hexdec(substr($oldUid, -12));

            return substr($oldUid, 0, -12).sprintf('%012s', dechex($n + 1));
        };
    }

    public function setUid(string $uid): void
    {
        Assert::uuid($uid);
        $this->uid = $uid;
    }

    public function setNextUidGenerator(callable $callback): void
    {
        $this->callback = $callback;
    }

    public function generateEntityId(string $class): EntityId
    {
        Assert::isAOf($class, EntityId::class);

        $id = $class::fromString($this->uid);

        $this->uid = ($this->callback)($this->uid);

        return $id;
    }
}
