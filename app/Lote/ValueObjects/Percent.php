<?php

namespace App\Lote\ValueObjects;

class Percent implements \JsonSerializable
{
    public const int SCALE = 100;

    public function __construct(
        public readonly int $value
    ) {}

    public static function fromFloat(float $percent): self
    {
        return new self((int) round($percent * self::SCALE));
    }

    public function toFloat(): float
    {
        return $this->value / self::SCALE;
    }

    public function formatted(): string
    {
        return $this->__toString().'%';
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2, '.', '');
    }

    public function jsonSerialize(): array
    {
        return [
            'value' => $this->value,
            'number' => $this->toFloat(),
            'formatted' => $this->formatted(),
        ];
    }
}
