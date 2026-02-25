<?php

namespace App\Services;

use Hyperf\Coroutine\Coroutine;

class CompanyService
{
    protected string $companyId;

    public function process(int $id): mixed
    {
        $this->companyId = (string) $id;

        Coroutine::sleep(2);
        return "Company ID: {$this->companyId}";
    }
}
