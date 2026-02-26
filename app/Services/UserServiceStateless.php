<?php

namespace App\Services;

use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\Logger;
use Psr\Log\LoggerInterface;

class UserServiceStateless
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    /**
     * A SOLUÇÃO: Sem estado compartilhado, usando o Contexto
     * O Contexto é uma estrutura de dados que é única para cada corrotina.
     * Ele permite armazenar e recuperar dados específicos da corrotina sem risco de interferência entre elas.
     */
    public function identifyUser(string $name)
    {
        Context::set('user_name', $name);

        $cid = \Hyperf\Coroutine\Coroutine::id();

        $this->logger->info("[Início] Coroutine: $cid | Nome recebido: $name");
        \Swoole\Coroutine::sleep(1);

        // 2. Recuperamos o nome do Contexto
        // O Context::get sabe exatamente qual corrotina está a perguntar
        $nameFromContext = Context::get('user_name');

        $this->logger->info("[Fim] Coro: $cid | Nome retornado: {$nameFromContext}");
        return $nameFromContext;
    }

    public function identifyEmail(string $email)
    {
        $cid = \Hyperf\Coroutine\Coroutine::id();

        $this->logger->info("[Início] Coroutine: $cid | Email recebido: $email");

        $user = new User();
        $user->email = $email;

        \Swoole\Coroutine::sleep(1);

        $this->logger->info("[Fim] Coro: $cid | Email retornado: {$user->email}");
        return $user->email;
    }
}