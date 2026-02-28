-- Script de load test para demonstrar poluição de estado em corrotinas
-- Uso: wrk -t12 -c400 -d30s -s scripts/load-test.lua http://localhost:9501
--
-- Este script simula múltiplas requisições simultâneas para testar
-- se há poluição de estado entre corrotinas.

request = function()
    -- Lista de emails para simular diferentes utilizadores
    local emails = {
        "admin@admin.com",
        "user1@gmail.com",
        "user2@hotmail.com",
        "user3@yahoo.com",
        "user4@outlook.com",
        "user5@teste.com",
        "user6@uol.com",
        "user7@bol.com",
        "user8@terra.com",
        "user9@example.com"
    }
    local email_sorteado = emails[math.random(#emails)]

    -- Testa o endpoint que demonstra que objetos instanciados não sofrem poluição
    local path = "/demo/state-unpolluted-email?email=" .. email_sorteado
    return wrk.format("GET", path)
end