# Exemplos Práticos - Poluição de Estado

Este documento contém exemplos práticos de como testar e entender o problema de poluição de estado.

## 🧪 Cenários de Teste

### Teste 1: Poluição de Estado Básica

Execute múltiplas requisições simultâneas para o endpoint problemático:

```bash
# Terminal 1
curl "http://localhost:9501/demo/state-polluted?name=Alice"

# Terminal 2 (executar quase simultaneamente)
curl "http://localhost:9501/demo/state-polluted?name=Bob"

# Terminal 3
curl "http://localhost:9501/demo/state-polluted?name=Charlie"
```

**Resultado esperado:** Algumas requisições podem retornar nomes incorretos.

### Teste 2: Comparação Stateful vs Stateless

Execute o mesmo teste nos dois endpoints:

```bash
# Teste com problema
for i in {1..5}; do
  curl "http://localhost:9501/demo/state-polluted?name=User$i" &
done
wait

# Teste com solução
for i in {1..5}; do
  curl "http://localhost:9501/demo/state-unpolluted?name=User$i" &
done
wait
```

Compare os logs em `runtime/debug-polluted.log` e `runtime/debug-unpolluted.log`.

### Teste 3: Load Test com wrk

#### Teste do Endpoint Problemático

```bash
wrk -t12 -c400 -d30s "http://localhost:9501/demo/state-polluted?name=TestUser"
```

#### Teste do Endpoint com Solução

```bash
wrk -t12 -c400 -d30s "http://localhost:9501/demo/state-unpolluted?name=TestUser"
```

#### Teste com Script Lua (Múltiplos Valores)

```bash
wrk -t12 -c400 -d30s -s scripts/state-unpolluted.lua http://localhost:9501
```

## 📊 Analisando os Resultados

### Verificar Logs

```bash
# Ver logs de poluição
tail -f runtime/debug-polluted.log

# Ver logs sem poluição
tail -f runtime/debug-unpolluted.log
```

### Contar Poluições

```bash
# Contar quantas vezes houve poluição
grep -c '"is_polluted":true' runtime/debug-polluted.log

# Ver exemplos de poluição
grep '"is_polluted":true' runtime/debug-polluted.log | head -5
```

## 🔍 Entendendo o Código

### Código Problemático (UserServiceStateful)

```php
class UserServiceStateful
{
    private ?string $currentUser = null; // ⚠️ Compartilhado entre corrotinas
    
    public function identifyUser(string $name): string
    {
        $this->currentUser = $name; // ⚠️ Pode ser sobrescrito
        Coroutine::sleep(1);        // Durante o sleep, outra corrotina pode mudar
        return $this->currentUser;   // ⚠️ Pode retornar valor incorreto
    }
}
```

### Código Correto - Usando Context (UserServiceStateless)

```php
class UserServiceStateless
{
    public function identifyUser(string $name): string
    {
        Context::set('user_name', $name); // ✅ Isolado por corrotina
        Coroutine::sleep(1);
        return Context::get('user_name'); // ✅ Sempre correto
    }
}
```

### Código Correto - Usando Nova Instância

```php
public function identifyEmail(string $email): string
{
    $user = new User();        // ✅ Nova instância por corrotina
    $user->email = $email;     // ✅ Sem compartilhamento
    Coroutine::sleep(1);
    return $user->email;       // ✅ Sempre correto
}
```

## 💡 Dicas

1. **Sempre use Context** para dados que precisam ser isolados por corrotina
2. **Evite propriedades de instância** em serviços singleton que armazenam estado de requisição
3. **Crie novas instâncias** quando precisar de objetos com estado isolado
4. **Teste com carga** para identificar problemas de poluição de estado

## 🐛 Debugging

### Habilitar Logs Detalhados

Os serviços já incluem logs que mostram:
- ID da corrotina
- Valor recebido
- Valor retornado
- Timestamp

### Verificar Corrotinas Simultâneas

```bash
# Ver logs do servidor
tail -f runtime/logs/hyperf.log | grep "Coro:"
```

Isso mostrará quando múltiplas corrotinas estão processando ao mesmo tempo.
