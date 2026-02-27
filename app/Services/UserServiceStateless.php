<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\User;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\Inject;

/**
 * Serviço Stateless - DEMONSTRA A SOLUÇÃO PARA POLUIÇÃO DE ESTADO
 *
 * Este serviço demonstra as formas corretas de evitar poluição de estado:
 *
 * 1. Usando Context do Hyperf: Dados isolados por corrotina
 * 2. Criando novas instâncias: Cada objeto é independente
 *
 * O Context do Hyperf é uma estrutura de dados que é única para cada corrotina.
 * Ele permite armazenar e recuperar dados específicos da corrotina sem risco
 * de interferência entre elas.
 */
class UserServiceStateless
{
    #[Inject]
    protected StdoutLoggerInterface $logger;

    /**
     * Identifica o usuário - VERSÃO CORRETA usando Context
     * 
     * SOLUÇÃO: Usa Context do Hyperf para armazenar dados isolados por corrotina.
     * O Context é uma estrutura thread-local que garante que cada corrotina
     * tenha seu próprio espaço de dados, mesmo que o serviço seja um singleton.
     * 
     * @param string $name Nome do usuário a ser identificado
     * @return string Nome do usuário (sempre correto, isolado por corrotina)
     */
    public function identifyUser(string $name): string
    {
        $cid = Coroutine::id();

        $this->logger->info("[Stateless] [Início] Coro: $cid | Nome recebido: $name");
        
        // SOLUÇÃO: Armazena no Context, que é isolado por corrotina
        Context::set('user_name', $name);

        // Simulando I/O bloqueante (espera de DB/API/externa)
        // Mesmo que outra corrotina entre aqui, ela terá seu próprio Context
        Coroutine::sleep(1);

        // Recupera do Context da corrotina atual (sempre correto)
        $nameFromContext = Context::get('user_name');

        $this->logger->info("[Stateless] [Fim] Coro: $cid | Nome retornado: {$nameFromContext}");
        
        return $nameFromContext;
    }

    /**
     * Identifica o email - VERSÃO CORRETA usando nova instância
     * 
     * SOLUÇÃO: Cria uma nova instância do objeto User dentro da corrotina.
     * Como cada instância é independente, não há compartilhamento de estado
     * entre corrotinas, mesmo que o serviço seja um singleton.
     * 
     * @param string $email Email do usuário a ser identificado
     * @return string Email do usuário (sempre correto, instância isolada)
     */
    public function identifyEmail(string $email): string
    {
        $cid = Coroutine::id();

        $this->logger->info("[Stateless] [Início] Coro: $cid | Email recebido: $email");

        // SOLUÇÃO: Cria nova instância dentro da corrotina
        // Cada corrotina terá sua própria instância, sem compartilhamento
        $user = new User();
        $user->email = $email;

        // Simulando I/O bloqueante
        // Mesmo que outra corrotina entre, ela criará sua própria instância
        Coroutine::sleep(1);

        $this->logger->info("[Stateless] [Fim] Coro: $cid | Email retornado: {$user->email}");
        
        return $user->email;
    }
}
