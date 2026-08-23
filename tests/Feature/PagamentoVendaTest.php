<?php

namespace Tests\Feature;

use App\Enums\StatusPagamento;
use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagamentoVendaTest extends TestCase
{
    use RefreshDatabase;

    private function vendaDe100Reais(float $valorPago): Venda
    {
        $produto = Produto::factory()->create();
        $carga = CargaCaminhao::factory()->create();

        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'produto_id' => $produto->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $venda = Venda::factory()->create([
            'carga_caminhao_id' => $carga->fresh()->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'valor_pago' => $valorPago,
        ]);

        VendaItem::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'quantidade' => 10,
            'valor_unitario' => 10.00,
        ]);

        return $venda->fresh();
    }

    public function test_venda_totalmente_paga(): void
    {
        $venda = $this->vendaDe100Reais(valorPago: 100.00);

        $this->assertSame(100.0, $venda->valor_total);
        $this->assertSame(0.0, $venda->valor_em_aberto);
        $this->assertSame(StatusPagamento::Pago, $venda->status_pagamento);
    }

    public function test_venda_parcialmente_paga(): void
    {
        $venda = $this->vendaDe100Reais(valorPago: 40.00);

        $this->assertSame(60.0, $venda->valor_em_aberto);
        $this->assertSame(StatusPagamento::Parcial, $venda->status_pagamento);
    }

    public function test_venda_totalmente_em_aberto(): void
    {
        $venda = $this->vendaDe100Reais(valorPago: 0);

        $this->assertSame(100.0, $venda->valor_em_aberto);
        $this->assertSame(StatusPagamento::EmAberto, $venda->status_pagamento);
    }
}
