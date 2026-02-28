# Estudo Swoole/Hyperf - Poluição de Estado em Corrotinas

Este é um projeto de estudo focado em demonstrar o problema de **poluição de estado (state pollution)** em aplicações que utilizam corrotinas do Swoole/Hyperf, e como resolver esse problema.

## 📚 Sobre o Projeto

Em aplicações que utilizam corrotinas (como Swoole/Hyperf), os serviços são singletons por padrão. Isso significa que a mesma instância do serviço é compartilhada entre todas as corrotinas que processam requisições simultâneas.

Quando você armazena estado em propriedades da classe, esse estado pode ser sobrescrito por outra corrotina antes que a primeira termine seu processamento, resultando em dados incorretos sendo retornados.

### 🎯 Objetivos do Projeto

- Demonstrar o problema de poluição de estado em corrotinas
- Mostrar exemplos práticos de código problemático
- Apresentar soluções corretas usando Context do Hyperf
- Fornecer exemplos de teste para validar o comportamento

## 🏗️ Estrutura do Projeto

```
estudo-swoole-hyperf/
├── app/
│   ├── Controller/
│   │   ├── CoroutineDemoController.php    # Endpoints de demonstração
│   │   └── IndexController.php
│   ├── Services/
│   │   ├── UserServiceStateful.php        # ⚠️ Demonstra o PROBLEMA
│   │   ├── UserServiceStateless.php       # ✅ Demonstra a SOLUÇÃO
│   │   └── CompanyService.php             # Exemplo adicional
│   ├── Model/
│   │   └── User.php                       # Modelo de exemplo
│   ├── Exception/
│   └── Constants/
├── scripts/
│   └── load-test.lua                      # Script para testes de carga
├── config/
└── README.md
```

## 🚀 Como Usar

### Pré-requisitos

- PHP >= 8.1
- Swoole PHP extension >= 5.0
- Composer

### Instalação

```bash
composer install
```

### Executar o Servidor

```bash
php bin/hyperf.php start
```

O servidor estará disponível em `http://localhost:9501`

## 📖 Endpoints de Demonstração

### 1. Estado Poluído (Problema)

**GET** `/demo/state-polluted?name=Alice`

Demonstra o problema de poluição de estado. Quando múltiplas requisições são processadas simultaneamente, o valor retornado pode não corresponder ao valor solicitado.

**Exemplo:**
```bash
curl "http://localhost:9501/demo/state-polluted?name=Alice"
```

**Resposta:**
```json
{
  "requested": "Alice",
  "returned": "Bob",  // ⚠️ Valor incorreto devido à poluição
  "is_polluted": true,
  "message": "⚠️ Estado foi poluído! O valor retornado não corresponde ao solicitado."
}
```

### 2. Estado Não Poluído - Usando Context (Solução)

**GET** `/demo/state-unpolluted?name=Alice`

Demonstra a solução usando `Context` do Hyperf, que mantém dados isolados por corrotina.

**Exemplo:**
```bash
curl "http://localhost:9501/demo/state-unpolluted?name=Alice"
```

**Resposta:**
```json
{
  "requested": "Alice",
  "returned": "Alice",  // ✅ Valor correto
  "is_polluted": false,
  "message": "✅ Estado não foi poluído - Context funcionou corretamente!"
}
```

### 3. Estado Não Poluído - Usando Nova Instância (Solução)

**GET** `/demo/state-unpolluted-email?email=alice@example.com`

Demonstra que criar novas instâncias de objetos dentro da corrotina também evita poluição de estado.

**Exemplo:**
```bash
curl "http://localhost:9501/demo/state-unpolluted-email?email=alice@example.com"
```

## 🧪 Testando o Problema

Para realmente ver o problema de poluição de estado, você precisa fazer múltiplas requisições simultâneas.

### Usando wrk (Recomendado)

```bash
# Instalar wrk (se necessário)
# Ubuntu/Debian: sudo apt-get install wrk
# macOS: brew install wrk

# Testar endpoint com problema
wrk -t12 -c400 -d30s "http://localhost:9501/demo/state-polluted?name=User1"

# Testar endpoint com solução
wrk -t12 -c400 -d30s -s scripts/state-unpolluted.lua http://localhost:9501
```

### Usando Script Lua Personalizado

O arquivo `scripts/load-test.lua` contém um script para testar com diferentes emails:

```bash
wrk -t12 -c400 -d30s -s scripts/state-unpolluted.lua http://localhost:9501
```

### Usando curl em Paralelo

```bash
# Fazer 10 requisições simultâneas
for i in {1..10}; do
  curl "http://localhost:9501/demo/state-polluted?name=User$i" &
done
wait
```

Depois, verifique os logs em `runtime/debug-polluted.log` para ver os resultados.

## 🔍 Entendendo o Problema

### O Problema: State Pollution

**Cenário do problema:**

1. Corrotina A recebe requisição com nome "Alice"
2. Corrotina A define `$this->currentUser = "Alice"`
3. Corrotina A entra em `sleep(1)` (simulando I/O)
4. Corrotina B recebe requisição com nome "Bob"
5. Corrotina B define `$this->currentUser = "Bob"` (SOBRESCREVE!)
6. Corrotina A acorda e retorna `$this->currentUser`, mas agora é "Bob"!

**Resultado:** Corrotina A retorna "Bob" quando deveria retornar "Alice"

### A Solução: Context do Hyperf

O `Context` do Hyperf é uma estrutura de dados única para cada corrotina. Ele permite armazenar e recuperar dados específicos da corrotina sem risco de interferência entre elas.

**Como funciona:**

```php
// Armazena no Context da corrotina atual
Context::set('user_name', $name);

// Recupera do Context da corrotina atual (sempre correto)
$name = Context::get('user_name');
```

### Outra Solução: Nova Instância

Criar uma nova instância de objeto dentro da corrotina também funciona, pois cada instância é independente:

```php
// Cada corrotina cria sua própria instância
$user = new User();
$user->email = $email;
```

## 📝 Logs

Os endpoints geram logs em:

- `runtime/debug-polluted.log` - Logs do endpoint com problema
- `runtime/debug-unpolluted.log` - Logs dos endpoints com solução

Você também pode ver os logs no console quando o servidor está rodando.

## 🎓 Conceitos Aprendidos

- ✅ Entendimento de corrotinas no Swoole/Hyperf
- ✅ Problema de poluição de estado em singletons
- ✅ Uso do Context do Hyperf para isolamento de dados
- ✅ Boas práticas para serviços stateless
- ✅ Testes de carga para validar comportamento

## 📚 Recursos Adicionais

- [Documentação do Hyperf](https://hyperf.wiki/)
- [Documentação do Swoole](https://www.swoole.co.uk/docs)
- [Hyperf Context](https://hyperf.wiki/3.1/#/en/context)

## 📄 Licença

Este projeto é apenas para fins educacionais.
