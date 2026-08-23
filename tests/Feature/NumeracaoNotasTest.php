<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NumeracaoNotasTest extends TestCase
{
    use RefreshDatabase;

    public function test_numeracao_de_cargas_e_sequencial(): void
    {
        $primeira = CargaCaminhao::factory()->create();
        $segunda = CargaCaminhao::factory()->create();
        $terceira = CargaCaminhao::factory()->create();

        $this->assertSame(1, $primeira->numero);
        $this->assertSame(2, $segunda->numero);
        $this->assertSame(3, $terceira->numero);
    }

    public function test_numero_nao_e_reutilizado_apos_exclusao(): void
    {
        CargaCaminhao::factory()->create();
        $segunda = CargaCaminhao::factory()->create();

        // Carga aberta pode ser excluída
        $segunda->delete();

        $nova = CargaCaminhao::factory()->create();

        $this->assertSame(3, $nova->numero);
    }

    public function test_sequencias_de_tipos_diferentes_sao_independentes(): void
    {
        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'quantidade' => 100,
        ]);
        $carga->fechar();

        $venda = Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
        ]);

        $this->assertSame(1, $carga->numero);
        $this->assertSame(1, $venda->numero);
    }
}
