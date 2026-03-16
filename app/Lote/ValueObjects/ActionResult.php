<?php

namespace App\Lote\ValueObjects;

use Illuminate\Support\HtmlString;

class ActionResult implements \JsonSerializable
{
    public bool $success;
    public string|HtmlString|null $reason;

    public function __construct(bool $success, string|HtmlString|null $reason = null)
    {
        $this->success = $success;
        $this->reason = $reason;
    }

    public function jsonSerialize(): array
    {
        $res = [];
        $res['success'] = $this->success;
        $res['reason'] = $this->reason;

        return $res;
    }
}