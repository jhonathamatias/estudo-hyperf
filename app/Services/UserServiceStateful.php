<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\Inject;

/**
 * Serviço Stateful - DEMONSTRA O PROBLEMA DE POLUIÇÃO DE ESTADO
 * 
 * ATENÇÃO: Este serviço demonstra um ANTI-PADRÃO!
 * 
 * O problema: Em aplicações Swoole/Hyperf, os serviços são singletons por padrão.
 * Isso significa que a mesma instância do serviço é compartilhada entre todas as corrotinas.
 * Quando você armazena estado em propriedades da classe (como $currentUser), esse estado
 * pode ser sobrescrito por outra corrotina antes que a primeira termine seu processamento.
 * 
 * Cenário do problema:
 * 1. Corrotina A recebe requisição com nome "Alice"
 * 2. Corrotina A define $this->currentUser = "Alice"
 * 3. Corrotina A entra em sleep (simulando I/O)
 * 4. Corrotina B recebe requisição com nome "Bob"
 * 5. Corrotina B define $this->currentUser = "Bob" (SOBRESCREVE!)
 * 6. Corrotina A acorda e retorna $this->currentUser, mas agora é "Bob"!
 * 
 * Resultado: Corrotina A retorna "Bob" quando deveria retornar "Alice"
 */
class UserServiceStateful
{
    /**
     * O VILÃO: Propriedade compartilhada entre todas as corrotinas
     * Como o serviço é um singleton, esta propriedade é compartilhada
     * entre todas as requisições simultâneas.
     */
    private ?string $currentUser = null;

    #[Inject]
    protected StdoutLoggerInterface $logger;

    /**
     * Identifica o usuário - VERSÃO COM PROBLEMA DE POLUIÇÃO DE ESTADO
     * 
     * Este método demonstra o problema de poluição de estado.
     * Quando múltiplas requisições são processadas simultaneamente,
     * o valor de $currentUser pode ser sobrescrito por outra corrotina.
     * 
     * @param string $name Nome do usuário a ser identificado
     * @return string Nome do usuário (pode estar incorreto devido à poluição)
     */
    public function identifyUser(string $name): string
    {
        $cid = Coroutine::id();

        $this->logger->info("[Stateful] [Início] Coro: $cid | Nome recebido: $name");
        
        // PROBLEMA: Armazena em propriedade compartilhada
        $this->currentUser = $name;

        // Simulando I/O bloqueante (espera de DB/API/externa)
        // Durante este sleep, outra corrotina pode entrar e mudar $this->currentUser
        Coroutine::sleep(1);

        $this->logger->info("[Stateful] [Fim] Coro: $cid | Nome retornado: {$this->currentUser}");
        
        // Pode retornar valor incorreto se outra corrotina sobrescreveu $this->currentUser
        return $this->currentUser;
    }
}