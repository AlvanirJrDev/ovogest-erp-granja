<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">Fechamento mensal</x-slot>
            Resumo completo do mês em PDF: faturamento, custo, margem, recebimentos,
            valores em aberto, quebra, todas as vendas e conciliações — pronto para o contador.
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Extrato por cliente</x-slot>
            Todas as compras de um estabelecimento no período, com itens, pagamentos
            registrados e o saldo devedor total — ideal para acerto de contas.
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Vendas em CSV/Excel</x-slot>
            Planilha com as vendas do mês (valores, situação de pagamento e vencimentos)
            para abrir no Excel ou importar em outro sistema.
        </x-filament::section>
    </div>
</x-filament-panels::page>
