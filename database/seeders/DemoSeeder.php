<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Granja;
use App\Models\Produto;
use App\Models\Veiculo;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Dados de exemplo: nunca em produção
        if (! app()->environment('local') || Produto::withoutGlobalScopes()->count() > 0) {
            return;
        }

        $granjaId = Granja::first()->id;

        // Cada combinação de tipo de ovo + tamanho de bandeja é um produto
        // com seu próprio preço de venda e custo.
        $produtos = [
            ['nome' => 'Ovo Branco', 'tipo_bandeja' => '30', 'preco_venda' => 18.00, 'custo_unitario' => 12.50],
            ['nome' => 'Ovo Branco', 'tipo_bandeja' => '15', 'preco_venda' => 10.00, 'custo_unitario' => 6.80],
            ['nome' => 'Ovo Branco', 'tipo_bandeja' => '12', 'preco_venda' => 8.50, 'custo_unitario' => 5.40],
            ['nome' => 'Ovo Vermelho', 'tipo_bandeja' => '30', 'preco_venda' => 21.00, 'custo_unitario' => 14.80],
            ['nome' => 'Ovo Vermelho', 'tipo_bandeja' => '15', 'preco_venda' => 11.50, 'custo_unitario' => 7.90],
            ['nome' => 'Ovo Caipira', 'tipo_bandeja' => '12', 'preco_venda' => 15.00, 'custo_unitario' => 9.20],
        ];

        foreach ($produtos as $p) {
            Produto::create($p + ['granja_id' => $granjaId]);
        }

        Veiculo::create(['placa' => 'ABC1D23', 'modelo' => 'HR 2.5 Baú', 'capacidade_carga' => 800, 'granja_id' => $granjaId]);
        Veiculo::create(['placa' => 'DEF4G56', 'modelo' => 'Fiorino Furgão', 'capacidade_carga' => 300, 'granja_id' => $granjaId]);

        Cliente::create(['nome' => 'Mercado Central', 'documento' => '12.345.678/0001-90', 'email' => 'compras@mercadocentral.test', 'telefone' => '(62) 99999-0001', 'endereco' => 'Av. Principal, 100 - Centro', 'granja_id' => $granjaId]);
        Cliente::create(['nome' => 'Padaria do Bairro', 'documento' => '98.765.432/0001-10', 'telefone' => '(62) 99999-0002', 'endereco' => 'Rua das Flores, 45', 'granja_id' => $granjaId]);
        Cliente::create(['nome' => 'Restaurante Sabor Caseiro', 'documento' => '11.222.333/0001-44', 'telefone' => '(62) 99999-0003', 'endereco' => 'Rua do Comércio, 210', 'granja_id' => $granjaId]);
    }
}
