<?php

function aplicarDesconto($preco, $percentual)
{
    $desconto = $preco * ($percentual / 100);

    return $preco - $desconto;
}

function buscarCorte($cortes, $codigo)
{
    foreach ($cortes as $corte)
    {
        if ($corte["codigo"] == $codigo)
        {
            return $corte;
        }
    }

    return null;
}

function filtrarCortesCaros($cortes)
{
    $resultado = [];

    foreach ($cortes as $corte)
    {
        if ($corte["preco"] > 35)
        {
            $resultado[] = $corte;
        }
    }

    return $resultado;
}

function validarAgendamento($nome, $data, $horario)
{
    if (empty($nome))
    {
        return "Nome obrigatório";
    }
    elseif (empty($data))
    {
        return "Data obrigatória";
    }
    elseif (empty($horario))
    {
        return "Horário obrigatório";
    }

    return "OK";
}