<?php

namespace App\Services;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

class UserServiceStateful
{
    // O VILÃO: Propriedade num Singleton
    private $currentUser;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    /**
     * O PROBLEMA: Estado compartilhado entre corrotinas
     * Em um ambiente de servidor, como o Hyperf, as corrotinas são usadas para lidar com múltiplas requisições simultaneamente.
     * Se uma propriedade é compartilhada (como $currentUser), ela pode ser sobrescrita por outra corrotina antes que a primeira termine seu processamento.
     * Isso leva a resultados imprevisíveis e "poluição" de estado, onde o valor retornado pode não ser o esperado.
     */
    public function identifyUser(string $name)
    {
        $cid = \Hyperf\Coroutine\Coroutine::id();

        $this->logger->info("[Início] | Coro: $cid | Nome recebido: $name");
        $this->currentUser = $name;

        // Simulando I/O bloqueante (espera de DB/API)
        // Isso permite que outra corrotina entre e mude o $this->currentUser
        \Swoole\Coroutine::sleep(1);

        $this->logger->info("[Fim] Coro: $cid | Nome retornado: {$this->currentUser}");
        return $this->currentUser;
    }
}