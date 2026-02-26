<?php

namespace App\Controller;

use App\Services\CompanyService;
use App\Services\UserServiceStateful;
use App\Services\UserServiceStateless;
use Hyperf\Di\Annotation\Inject;

class StatePolluted extends AbstractController
{
    #[Inject]
    protected UserServiceStateful $serviceStateful;
    #[Inject]
    protected UserServiceStateless $serviceStateless;


    public function testStatePolluted()
    {
        $name = $this->request->query('name');
        $resultName = $this->serviceStateful->identifyUser($name);

        $result = [
            'requested' => $name,
            'returned' => $resultName,
            'is_polluted' => $name !== $resultName
        ];

        file_put_contents('runtime/debug-poluted.log', json_encode($result) . PHP_EOL, FILE_APPEND);

        return $result;
    }

    public function testStateUnpolluted()
    {
        $name = $this->request->query('name');
        $resultName = $this->serviceStateless->identifyUser($name);

        $result = [
            'requested' => $name,
            'returned' => $resultName,
            'is_polluted' => $name !== $resultName
        ];

        file_put_contents('runtime/debug-unpolluted.log', json_encode($result) . PHP_EOL, FILE_APPEND);

        return $result;
    }

    public function testStateUnpollutedEmail()
    {
        $email = $this->request->query('email');
        $resultEmail = $this->serviceStateless->identifyEmail($email);

        $result = [
            'requested' => $email,
            'returned' => $resultEmail,
            'is_polluted' => $email !== $resultEmail
        ];

        file_put_contents('runtime/debug-unpolluted.log', json_encode($result) . PHP_EOL, FILE_APPEND);

        return $result;
    }
}
