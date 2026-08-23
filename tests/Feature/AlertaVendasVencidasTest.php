<?php

namespace Tests\Feature;

use App\Models\CargaCaminhao;
use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Granja;
use App\Models\Produto;
use App\Models\User;
use App\Models\Venda;
use App\Models\VendaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlertaVendasVencidasTest extends TestCase
{
    use RefreshDatabase;

    private function vendaAPrazo(float $valorPago, ?string $vencimento, ?Granja $granja = null): Venda
    {
        $granja ??= Granja::create(['nome' => 'Granja Teste']);
        $produto = Produto::factory()->create(['granja_id' => $granja->id]);
        $carga = CargaCaminhao::factory()->create(['granja_id' => $granja->id]);

        CargaItem::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'produto_id' => $produto->id,
            'quantidade' => 100,
        ]);

        $carga->fechar();

        $venda = Venda::factory()->create([
            'carga_caminhao_id' => $carga->id,
            'cliente_id' => Cliente::factory()->create()->id,
            'forma_pagamento' => 'prazo',
            'valor_pago' => $valorPago,
            'data_vencimento' => $vencimento,
        ]);

        VendaItem::create([
            'venda_id' => $venda->id,
            'produto_id' => $produto->id,
            'quantidade' => 10,
            'valor_unitario' => 18.00,
        ]);

        return $venda->fresh();
    }

    public function test_venda_vencida_em_aberto_notifica_admin_e_financeiro(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('financeiro');
        Role::findOrCreate('vendedor');

        $venda = $this->vendaAPrazo(50.00, now()->subDays(3)->toDateString());

        $admin = User::factory()->create(['granja_id' => $venda->granja_id]);
        $admin->assignRole('admin');
        $financeiro = User::factory()->create(['granja_id' => $venda->granja_id]);
        $financeiro->assignRole('financeiro');
        $vendedor = User::factory()->create(['granja_id' => $venda->granja_id]);
        $vendedor->assignRole('vendedor');

        $this->artisan('ovogest:alertar-vencidas')
            ->expectsOutputToContain('1 venda(s) vencida(s) em 1 granja(s).')
            ->assertSuccessful();

        // um resumo para cada responsável; vendedor fica de fora
        $this->assertSame(2, DB::table('notifications')->count());
        $this->assertSame(
            2,
            DB::table('notifications')->whereIn('notifiable_id', [$admin->id, $financeiro->id])->count(),
        );
    }

    public function test_venda_quitada_ou_no_prazo_nao_gera_alerta(): void
    {
        Role::findOrCreate('admin');

        // vencida porém quitada (10 × 18 = 180, tudo pago)
        $quitada = $this->vendaAPrazo(180.00, now()->subDays(3)->toDateString());

        // em aberto porém dentro do prazo
        $this->vendaAPrazo(0.00, now()->addDays(5)->toDateString());

        $admin = User::factory()->create(['granja_id' => $quitada->granja_id]);
        $admin->assignRole('admin');

        $this->artisan('ovogest:alertar-vencidas')->assertSuccessful();

        $this->assertSame(0, DB::table('notifications')->count());
    }
}
