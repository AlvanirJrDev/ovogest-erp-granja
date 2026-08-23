<?php

namespace Tests\Feature;

use App\Enums\StatusCarga;
use App\Enums\StatusConciliacao;
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

class ConciliacaoTest extends TestCase
{
    use RefreshDatabase;

    private function montarCenario(int $saiu, int $vendido, int $retornou): CargaCaminhao
    {
        $produto = Produto::factory()->create();
        $carga = CargaCaminhao::factory()->create();

        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'produto_id' => $produto->id,
            'quantidade' => $saiu,
        ]);

        $carga->fechar();
        $carga = $carga->fresh();

        if ($vendido > 0) {
            $venda = Venda::factory()->create([
                'carga_caminhao_id' => $carga->id,
                'cliente_id' => Cliente::factory()->create()->id,
            ]);

            VendaItem::create([
                'venda_id' => $venda->id,
                'produto_id' => $produto->id,
                'quantidade' => $vendido,
                'valor_unitario' => 18.00,
            ]);
        }

        $retorno = RetornoCaminhao::factory()->create([
            'carga_caminhao_id' => $carga->id,
        ]);

        if ($retornou > 0) {
            RetornoItem::create([
                'retorno_caminhao_id' => $retorno->id,
                'produto_id' => $produto->id,
                'quantidade' => $retornou,
                'motivo' => 'sobra',
            ]);
        }

        $retorno->fechar();

        return $carga->fresh();
    }

    public function test_conciliacao_ok_quando_saida_igual_venda_mais_retorno(): void
    {
        $carga = $this->montarCenario(saiu: 100, vendido: 70, retornou: 30);

        $conciliacao = $carga->conciliacao;

        $this->assertSame(100, $conciliacao->total_saiu);
        $this->assertSame(70, $conciliacao->total_vendido);
        $this->assertSame(30, $conciliacao->total_retornou);
        $this->assertSame(0, $conciliacao->diferenca);
        $this->assertSame(StatusConciliacao::Ok, $conciliacao->status);
        $this->assertSame(StatusCarga::Conciliada, $carga->status);
    }

    public function test_conciliacao_divergente_quando_ha_diferenca(): void
    {
        $carga = $this->montarCenario(saiu: 100, vendido: 70, retornou: 20);

        $conciliacao = $carga->conciliacao;

        $this->assertSame(10, $conciliacao->diferenca);
        $this->assertSame(StatusConciliacao::Divergente, $conciliacao->status);
    }

    public function test_retorno_fechado_bloqueia_edicao_de_itens(): void
    {
        $carga = $this->montarCenario(saiu: 100, vendido: 70, retornou: 30);

        $item = $carga->retorno->itens()->first();

        $this->expectException(ValidationException::class);

        $item->update(['quantidade' => 5]);
    }
}
