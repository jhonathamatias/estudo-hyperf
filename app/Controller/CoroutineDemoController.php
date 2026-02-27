<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\UserServiceStateful;
use App\Services\UserServiceStateless;
use Hyperf\Di\Annotation\Inject;

/**
 * Controller de demonstração sobre poluição de estado em corrotinas.
 * 
 * Este controller demonstra o problema de "state pollution" (poluição de estado)
 * que pode ocorrer em aplicações Swoole/Hyperf quando serviços compartilham estado
 * entre diferentes corrotinas.
 * 
 * Exemplos disponíveis:
 * - testStatePolluted: Demonstra o problema com serviço stateful
 * - testStateUnpolluted: Demonstra a solução usando Context
 * - testStateUnpollutedEmail: Demonstra que objetos instanciados não sofrem poluição
 */
class CoroutineDemoController extends AbstractController
{
    #[Inject]
    protected UserServiceStateful $serviceStateful;

    #[Inject]
    protected UserServiceStateless $serviceStateless;

    /**
     * Testa o problema de poluição de estado com serviço stateful.
     * 
     * Este endpoint demonstra como um serviço com estado compartilhado
     * pode retornar valores incorretos quando múltiplas requisições
     * são processadas simultaneamente.
     * 
     * @return array{requested: string, returned: string, is_polluted: bool}
     */
    public function testStatePolluted()
    {
        $name = $this->request->query('name', 'Guest');
        $resultName = $this->serviceStateful->identifyUser($name);

        $result = [
            'requested' => $name,
            'returned' => $resultName,
            'is_polluted' => $name !== $resultName,
            'message' => $name !== $resultName 
                ? 'Estado foi poluído! O valor retornado não corresponde ao solicitado.' 
                : 'Estado não foi poluído (pode ser sorte se testado isoladamente)'
        ];

        file_put_contents('runtime/debug-polluted.log', json_encode($result) . PHP_EOL, FILE_APPEND);

        return $result;
    }

    /**
     * Testa a solução usando Context do Hyperf.
     * 
     * Este endpoint demonstra como usar o Context do Hyperf para evitar
     * poluição de estado, mantendo dados isolados por corrotina.
     * 
     * @return array{requested: string, returned: string, is_polluted: bool}
     */
    public function testStateUnpolluted()
    {
        $name = $this->request->query('name', 'Guest');
        $resultName = $this->serviceStateless->identifyUser($name);

        $result = [
            'requested' => $name,
            'returned' => $resultName,
            'is_polluted' => $name !== $resultName,
            'message' => $name !== $resultName 
                ? 'Estado foi poluído (não deveria acontecer com Context)'
                : 'Estado não foi poluído - Context funcionou corretamente!'
        ];

        file_put_contents('runtime/debug-unpolluted.log', json_encode($result) . PHP_EOL, FILE_APPEND);

        return $result;
    }

    /**
     * Testa que objetos instanciados não sofrem poluição de estado.
     * 
     * Este endpoint demonstra que quando você cria uma nova instância
     * de um objeto dentro de uma corrotina, ela não compartilha estado
     * com outras corrotinas, pois cada instância é independente.
     * 
     * @return array{requested: string, returned: string, is_polluted: bool}
     */
    public function testStateUnpollutedEmail()
    {
        $email = $this->request->query('email', 'guest@example.com');
        $resultEmail = $this->serviceStateless->identifyEmail($email);

        $result = [
            'requested' => $email,
            'returned' => $resultEmail,
            'is_polluted' => $email !== $resultEmail,
            'message' => $email !== $resultEmail 
                ? 'Estado foi poluído (não deveria acontecer com nova instância)' 
                : 'Estado não foi poluído - Nova instância é isolada!'
        ];

        file_put_contents('runtime/debug-unpolluted.log', json_encode($result) . PHP_EOL, FILE_APPEND);

        return $result;
    }
}
