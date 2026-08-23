<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VendaSaldoTest extends TestCase
{
    use RefreshDatabase;

    private function cargaFechadaComSaldo(Produto $produto, int $quantidade): CargaCaminhao
    {
        $carga = CargaCaminhao::factory()->create();

        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'produto_id' => $produto->id,
            'quantidade' => $quantidade,
        ]);

        $carga->fechar();

        return $carga->fresh();
    }

    public function test_venda_dentro_do_saldo_e_permitida(): void
    {
        $produto = Produto::factory()->create();
        $carga = $this->cargaFechadaComSaldo($produto, 100);

        $venda = Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
        ]);

        VendaItem::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'quantidade' => 60,
            'valor_unitario' => 18.00,
        ]);

        $this->assertSame(40, $carga->saldoDisponivel($produto->id));
    }

    public function test_venda_nao_pode_ultrapassar_saldo_disponivel(): void
    {
        $produto = Produto::factory()->create();
        $carga = $this->cargaFechadaComSaldo($produto, 100);

        $venda = Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
        ]);

        VendaItem::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'quantidade' => 60,
            'valor_unitario' => 18.00,
        ]);

        $this->expectException(ValidationException::class);

        VendaItem::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'quantidade' => 50,
            'valor_unitario' => 18.00,
        ]);
    }

    public function test_venda_nao_pode_ser_registrada_em_carga_aberta(): void
    {
        $produto = Produto::factory()->create();
        $carga = CargaCaminhao::factory()->create();

        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'produto_id' => $produto->id,
            'quantidade' => 100,
        ]);

        $this->expectException(ValidationException::class);

        Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
        ]);
    }

    public function test_venda_duplicada_em_janela_curta_e_bloqueada(): void
    {
        $produto = Produto::factory()->create();
        $carga = $this->cargaFechadaComSaldo($produto, 100);
        $cliente = Cliente::factory()->create();
        $vendedor = User::factory()->create();

        Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
        ]);

        $this->expectException(ValidationException::class);

        // mesmo cliente, mesma carga, mesmo vendedor, segundos depois
        Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
        ]);
    }
}
