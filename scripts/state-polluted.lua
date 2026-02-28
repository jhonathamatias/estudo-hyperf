-- Script de load test para demonstrar poluição de estado em corrotinas
-- Uso: wrk -t12 -c400 -d30s -s scripts/load-test.lua http://localhost:9501
--
-- Este script simula múltiplas requisições simultâneas para testar
-- se há poluição de estado entre corrotinas.

request = function()
    -- Lista de nomes para simular diferentes utilizadores
    local name = {
        "Alice",
        "Bob",
        "Charlie",
        "David",
        "Eve",
        "Frank",
        "Grace",
        "Heidi",
        "Ivan",
        "Judy"
    }
    local name_sorteado = name[math.random(#name)]

    -- Testa o endpoint que demonstra que objetos instanciados não sofrem poluição
    local path = "/demo/state-polluted?name=" .. name_sorteado
    return wrk.format("GET", path)
end