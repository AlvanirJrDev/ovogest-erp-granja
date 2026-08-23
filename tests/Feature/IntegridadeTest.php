<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\RetornoCaminhao;
use App\Models\RetornoItem;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class IntegridadeTest extends TestCase
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

        $this->venda = Venda::factory()->create([
            'carga_caminhao_id' => $this->carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
        ]);

        VendaItem::create([
            'venda_id' => $this->venda->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 70,
            'valor_unitario' => 18.00,
        ]);
    }

    private function conciliar(): void
    {
        $retorno = RetornoCaminhao::factory()->create(['carga_caminhao_id' => $this->carga->id]);

        RetornoItem::create([
            'retorno_caminhao_id' => $retorno->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 30,
            'motivo' => 'sobra',
        ]);

        $retorno->fechar();
    }

    public function test_item_de_venda_nao_pode_ser_alterado_apos_conciliacao(): void
    {
        $this->conciliar();

        $this->expectException(ValidationException::class);

        $this->venda->itens()->first()->update(['quantidade' => 10]);
    }

    public function test_venda_nao_pode_ser_excluida_apos_conciliacao(): void
    {
        $this->conciliar();

        $this->expectException(ValidationException::class);

        $this->venda->fresh()->delete();
    }

    public function test_retorno_nao_pode_exceder_o_disponivel_na_carga(): void
    {
        // Saíram 100, vendidos 70 — só 30 podem voltar
        $retorno = RetornoCaminhao::factory()->create(['carga_caminhao_id' => $this->carga->id]);

        $this->expectException(ValidationException::class);

        RetornoItem::create([
            'retorno_caminhao_id' => $retorno->id,
            'produto_id' => $this->produto->id,
            'quantidade' => 31,
            'motivo' => 'sobra',
        ]);
    }
}
