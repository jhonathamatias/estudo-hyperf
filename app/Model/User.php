<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Modelo de exemplo para demonstrar que objetos instanciados
 * dentro de uma corrotina não sofrem poluição de estado,
 * pois cada instância é independente.
 */
class User
{
    public string $email;
}
