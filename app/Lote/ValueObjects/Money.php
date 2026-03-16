<?php

namespace App\Lote\ValueObjects;

class Money implements \JsonSerializable
{
    public const int SCALE = 100;

    public function __construct(
        public readonly int $amount
    ) {}

    public static function fromFloat(float $value): self
    {
        return new self((int) round($value * self::SCALE));
    }

    public function toFloat(): float
    {
        return $this->amount / self::SCALE;
    }

    public function formatted(): string
    {
        return '€ '.number_format($this->toFloat(), 2, '.', ' ');
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function sub(self $other): self
    {
        return new self($this->amount - $other->amount);
    }

    public function multiply(int $qty): self
    {
        return new self($this->amount * $qty);
    }

    public function divide(int $qty): self
    {
        return new self(intdiv($this->amount, $qty));
    }

    public function percent(Percent $percent): self
    {
        return new self(intdiv($this->amount * $percent->value, 100 * $percent::SCALE));
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2, '.', '');
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'number' => $this->toFloat(),
            'formatted' => $this->formatted(),
        ];
    }
}
