<?php

namespace Tests\Feature;

use App\Enums\StatusCarga;
use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CargaFechamentoTest extends TestCase
{
    use RefreshDatabase;

    public function test_carga_com_itens_pode_ser_fechada(): void
    {
        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $this->assertSame(StatusCarga::Fechada, $carga->fresh()->status);
    }

    public function test_carga_sem_itens_nao_pode_ser_fechada(): void
    {
        $carga = CargaCaminhao::factory()->create();

        $this->expectException(ValidationException::class);

        $carga->fechar();
    }

    public function test_fechamento_bloqueia_edicao_de_itens(): void
    {
        $carga = CargaCaminhao::factory()->create();
        $item = CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $this->expectException(ValidationException::class);

        $item->fresh()->update(['quantidade' => 200]);
    }

    public function test_fechamento_bloqueia_inclusao_de_itens(): void
    {
        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $this->expectException(ValidationException::class);

        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'produto_id' => Produto::factory()->create()->id,
        ]);
    }

    public function test_fechamento_bloqueia_exclusao_de_itens(): void
    {
        $carga = CargaCaminhao::factory()->create();
        $item = CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $this->expectException(ValidationException::class);

        $item->fresh()->delete();
    }

    public function test_carga_fechada_nao_pode_ser_excluida(): void
    {
        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $this->expectException(ValidationException::class);

        $carga->fresh()->delete();
    }
}
