<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Granja;
use App\Models\MovimentoEstoque;
use App\Models\Produto;
use App\Models\RetornoCaminhao;
use App\Models\RetornoItem;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EstoqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_producao_entra_e_carregamento_debita_o_estoque(): void
    {
        $produto = Produto::factory()->create();

        MovimentoEstoque::create(['produto_id' => $produto->id, 'tipo' => 'producao', 'quantidade' => 200]);
        $this->assertSame(200, $produto->fresh()->estoque_atual);

        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create(['carga_caminhao_id' => $carga->id, 'produto_id' => $produto->id, 'quantidade' => 120]);
        $carga->fechar();

        $this->assertSame(80, $produto->fresh()->estoque_atual);
    }

    public function test_retorno_devolve_sobra_e_devolucao_mas_nao_quebra(): void
    {
        $produto = Produto::factory()->create();
        MovimentoEstoque::create(['produto_id' => $produto->id, 'tipo' => 'producao', 'quantidade' => 100]);

        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create(['carga_caminhao_id' => $carga->id, 'produto_id' => $produto->id, 'quantidade' => 100]);
        $carga->fechar();
        $this->assertSame(0, $produto->fresh()->estoque_atual);

        $retorno = RetornoCaminhao::factory()->create(['carga_caminhao_id' => $carga->id]);
        RetornoItem::create(['retorno_caminhao_id' => $retorno->id, 'produto_id' => $produto->id, 'quantidade' => 30, 'motivo' => 'sobra']);
        RetornoItem::create(['retorno_caminhao_id' => $retorno->id, 'produto_id' => $produto->id, 'quantidade' => 10, 'motivo' => 'devolucao']);
        RetornoItem::create(['retorno_caminhao_id' => $retorno->id, 'produto_id' => $produto->id, 'quantidade' => 5, 'motivo' => 'quebra']);
        $retorno->fechar();

        // Voltam 30 + 10; as 5 quebradas são perda
        $this->assertSame(40, $produto->fresh()->estoque_atual);
    }

    public function test_movimentos_automaticos_nao_podem_ser_excluidos_nem_editados(): void
    {
        $produto = Produto::factory()->create();
        $carga = CargaCaminhao::factory()->create();
        CargaItem::factory()->create(['carga_caminhao_id' => $carga->id, 'produto_id' => $produto->id, 'quantidade' => 50]);
        $carga->fechar();

        $movimento = MovimentoEstoque::where('tipo', 'carga')->firstOrFail();

        try {
            $movimento->delete();
            $this->fail('Exclusão de movimento automático deveria ser bloqueada');
        } catch (ValidationException) {
        }

        $this->expectException(ValidationException::class);

        $movimento->update(['quantidade' => 999]);
    }

    public function test_perfil_producao_lanca_producao_mas_nao_ve_vendas_nem_produtos(): void
    {
        Role::findOrCreate('producao');
        $granja = Granja::create(['nome' => 'Granja Teste']);
        $user = User::factory()->create(['granja_id' => $granja->id]);
        $user->assignRole('producao');

        $gate = Gate::forUser($user);
        $this->assertTrue($gate->allows('create', MovimentoEstoque::class));
        $this->assertTrue($gate->allows('viewAny', MovimentoEstoque::class));
        $this->assertFalse($gate->allows('viewAny', Venda::class));
        $this->assertFalse($gate->allows('viewAny', Produto::class));
        $this->assertFalse($gate->allows('delete', new MovimentoEstoque));
    }
}
