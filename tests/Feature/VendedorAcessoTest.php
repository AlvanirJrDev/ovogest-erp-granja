<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendedorAcessoTest extends TestCase
{
    use RefreshDatabase;

    private User $vendedor;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('vendedor');

        $this->vendedor = User::factory()->create();
        $this->vendedor->assignRole('vendedor');
    }

    public function test_vendedor_pode_criar_vendas_e_clientes(): void
    {
        $this->assertTrue(Gate::forUser($this->vendedor)->allows('create', Venda::class));
        $this->assertTrue(Gate::forUser($this->vendedor)->allows('create', Cliente::class));
    }

    public function test_vendedor_nao_gerencia_produtos_nem_cargas(): void
    {
        $this->assertFalse(Gate::forUser($this->vendedor)->allows('viewAny', Produto::class));
        $this->assertFalse(Gate::forUser($this->vendedor)->allows('create', CargaCaminhao::class));
    }

    public function test_vendedor_so_ve_a_propria_venda(): void
    {
        $minhaVenda = Venda::factory()->make(['vendedor_id' => $this->vendedor->id]);
        $vendaDeOutro = Venda::factory()->make(['vendedor_id' => User::factory()->create()->id]);

        $this->assertTrue(Gate::forUser($this->vendedor)->allows('view', $minhaVenda));
        $this->assertFalse(Gate::forUser($this->vendedor)->allows('view', $vendaDeOutro));
        $this->assertFalse(Gate::forUser($this->vendedor)->allows('update', $minhaVenda));
    }
}
