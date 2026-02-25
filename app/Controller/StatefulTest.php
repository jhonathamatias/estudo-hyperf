<?php

namespace App\Controller;

use App\Services\CompanyService;
use Hyperf\Di\Annotation\Inject;

class StatefulTest extends AbstractController
{
    #[Inject]
    protected CompanyService $companyService;

    public function index()
    {
        $companyId = $this->request->getAttribute('id');

        $this->companyService->process($companyId);

        return [
            'message' => "Processed company with ID: {$companyId}",
        ];
    }
}
