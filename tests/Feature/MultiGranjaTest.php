<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\Granja;
use App\Models\Produto;
use App\Models\Rota;
use App\Models\User;
use App\Models\Veiculo;
use App\Services\NumeracaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiGranjaTest extends TestCase
{
    use RefreshDatabase;

    private Granja $granjaA;

    private Granja $granjaB;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'dono', 'admin'] as $role) {
            Role::findOrCreate($role);
        }

        $this->granjaA = Granja::create(['nome' => 'Granja A']);
        $this->granjaB = Granja::create(['nome' => 'Granja B']);
    }

    private function usuarioDaGranja(Granja $granja, string $role = 'admin'): User
    {
        $user = User::factory()->create(['granja_id' => $granja->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_usuario_so_enxerga_dados_da_propria_granja(): void
    {
        Produto::create(['nome' => 'Ovo A', 'tipo_bandeja' => '30', 'preco_venda' => 10, 'custo_unitario' => 5, 'granja_id' => $this->granjaA->id]);
        Produto::create(['nome' => 'Ovo B', 'tipo_bandeja' => '30', 'preco_venda' => 10, 'custo_unitario' => 5, 'granja_id' => $this->granjaB->id]);

        $this->actingAs($this->usuarioDaGranja($this->granjaA));

        $this->assertSame(['Ovo A'], Produto::pluck('nome')->all());
    }

    public function test_registro_criado_recebe_a_granja_do_usuario_logado(): void
    {
        $this->actingAs($this->usuarioDaGranja($this->granjaB));

        $veiculo = Veiculo::create(['placa' => 'XYZ9Z99', 'modelo' => 'VUC', 'capacidade_carga' => 100]);

        $this->assertSame($this->granjaB->id, $veiculo->granja_id);
    }

    public function test_numeracao_de_notas_e_independente_por_granja(): void
    {
        $this->assertSame(1, NumeracaoService::proximo('carga', $this->granjaA->id));
        $this->assertSame(2, NumeracaoService::proximo('carga', $this->granjaA->id));

        // Outra granja começa a sua própria sequência do zero
        $this->assertSame(1, NumeracaoService::proximo('carga', $this->granjaB->id));
    }

    public function test_duas_granjas_podem_ter_cargas_com_o_mesmo_numero(): void
    {
        $veiculoA = Veiculo::create(['placa' => 'AAA1A11', 'modelo' => 'HR', 'capacidade_carga' => 100, 'granja_id' => $this->granjaA->id]);
        $veiculoB = Veiculo::create(['placa' => 'AAA1A11', 'modelo' => 'HR', 'capacidade_carga' => 100, 'granja_id' => $this->granjaB->id]);

        $rotaA = Rota::create(['nome' => 'Rota A', 'veiculo_id' => $veiculoA->id, 'data' => today(), 'responsavel_id' => $this->usuarioDaGranja($this->granjaA)->id, 'status' => 'planejada', 'granja_id' => $this->granjaA->id]);
        $rotaB = Rota::create(['nome' => 'Rota B', 'veiculo_id' => $veiculoB->id, 'data' => today(), 'responsavel_id' => $this->usuarioDaGranja($this->granjaB)->id, 'status' => 'planejada', 'granja_id' => $this->granjaB->id]);

        $cargaA = CargaCaminhao::create(['rota_id' => $rotaA->id, 'data_hora_saida' => now()]);
        $cargaB = CargaCaminhao::create(['rota_id' => $rotaB->id, 'data_hora_saida' => now()]);

        // Ambas recebem o nº 1 da sua própria granja — e a mesma placa coexiste
        $this->assertSame(1, $cargaA->numero);
        $this->assertSame(1, $cargaB->numero);
    }

    public function test_dono_gerencia_usuarios_apenas_da_propria_granja(): void
    {
        $dono = $this->usuarioDaGranja($this->granjaA, 'dono');
        $usuarioDaMinhaGranja = $this->usuarioDaGranja($this->granjaA);
        $usuarioDeOutraGranja = $this->usuarioDaGranja($this->granjaB);

        $this->assertTrue(Gate::forUser($dono)->allows('update', $usuarioDaMinhaGranja));
        $this->assertFalse(Gate::forUser($dono)->allows('update', $usuarioDeOutraGranja));
        $this->assertFalse(Gate::forUser($dono)->allows('create', Granja::class));
    }

    public function test_super_admin_gerencia_granjas(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');

        $this->assertTrue(Gate::forUser($superAdmin)->allows('create', Granja::class));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('update', $this->usuarioDaGranja($this->granjaA)));
    }
}
