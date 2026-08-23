<?php

namespace Tests\Feature;

use App\Enums\StatusPagamento;
use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Recebimento;
use App\Models\RetornoCaminhao;
use App\Models\RetornoItem;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RecebimentoCancelamentoTest extends TestCase
{
    use RefreshDatabase;

    private Produto $produto;

    private CargaCaminhao $carga;

    private Venda $venda;

    protected function setUp(): void
    {
        parent::setUp();

        $this->produto = Produto::factory()->create();
        $this->carga = CargaCaminhao::factory()->create();

        CargaItem::factory()->create([
            'carga_caminhao_id' => $this->carga->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 100,
        ]);

        $this->carga->fechar();
        $this->carga = $this->carga->fresh();

        // Venda de R$ 180 (10 × 18) sem nada pago no ato
        $this->venda = Venda::factory()->create([
            'carga_caminhao_id' => $this->carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'valor_pago' => 0,
        ]);

        VendaItem::create([
            'venda_id' => $this->venda->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 10,
            'valor_unitario' => 18.00,
        ]);
    }

    public function test_recebimentos_parciais_abatem_o_saldo_e_mudam_a_situacao(): void
    {
        Recebimento::create(['venda_id' => $this->venda->id, 'valor' => 80, 'forma' => 'pix']);

        $venda = $this->venda->fresh();
        $this->assertSame(100.0, $venda->valor_em_aberto);
        $this->assertSame(StatusPagamento::Parcial, $venda->status_pagamento);

        Recebimento::create(['venda_id' => $this->venda->id, 'valor' => 100, 'forma' => 'dinheiro']);

        $venda = $this->venda->fresh();
        $this->assertSame(0.0, $venda->valor_em_aberto);
        $this->assertSame(StatusPagamento::Pago, $venda->status_pagamento);
        $this->assertSame(2, $venda->recebimentos()->count());
    }

    public function test_recebimento_nao_pode_exceder_o_saldo_em_aberto(): void
    {
        $this->expectException(ValidationException::class);

        Recebimento::create(['venda_id' => $this->venda->id, 'valor' => 200, 'forma' => 'pix']);
    }

    public function test_cancelamento_devolve_o_saldo_ao_caminhao_e_sai_da_conciliacao(): void
    {
        $this->assertSame(90, $this->carga->saldoDisponivel($this->produto->id));

        $this->venda->cancelar('Cliente desistiu do pedido');

        $venda = $this->venda->fresh();
        $this->assertSame(StatusPagamento::Cancelada, $venda->status_pagamento);
        $this->assertSame(100, $this->carga->saldoDisponivel($this->produto->id));

        // Retorno de tudo fecha a conciliação em OK — a venda cancelada não conta
        $retorno = RetornoCaminhao::factory()->create(['carga_caminhao_id' => $this->carga->id]);
        RetornoItem::create(['retorno_caminhao_id' => $retorno->id, 'produto_id' => $this->produto->id, 'quantidade' => 100, 'motivo' => 'sobra']);
        $retorno->fechar();

        $conciliacao = $this->carga->fresh()->conciliacao;
        $this->assertSame(0, $conciliacao->total_vendido);
        $this->assertSame(0, $conciliacao->diferenca);
    }

    public function test_cancelamento_bloqueado_apos_conciliacao(): void
    {
        $retorno = RetornoCaminhao::factory()->create(['carga_caminhao_id' => $this->carga->id]);
        RetornoItem::create(['retorno_caminhao_id' => $retorno->id, 'produto_id' => $this->produto->id, 'quantidade' => 90, 'motivo' => 'sobra']);
        $retorno->fechar();

        $this->expectException(ValidationException::class);

        $this->venda->fresh()->cancelar('Tarde demais');
    }

    public function test_venda_cancelada_nao_pode_ser_excluida_nem_receber_baixas(): void
    {
        $this->venda->cancelar('Erro de lançamento');

        try {
            $this->venda->fresh()->delete();
            $this->fail('Exclusão deveria ter sido bloqueada');
        } catch (ValidationException) {
        }

        $this->expectException(ValidationException::class);

        Recebimento::create(['venda_id' => $this->venda->id, 'valor' => 10, 'forma' => 'pix']);
    }
}
