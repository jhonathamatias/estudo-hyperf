-- atropelamento.lua
request = function()
    -- Lista de nomes para simular diferentes utilizadores
    local email = {"admin@admin.com", "@ig", "@gmail", "@teste", "@hotmail", "@yahoo", "@outlook", "@uol", "@bol", "@terra"}
    local email_sorteado = email[math.random(#email)]

    -- Monta a URL com o query param
    local path = "/test-unpolluted-email?email=" .. email_sorteado
    return wrk.format("GET", path)
end