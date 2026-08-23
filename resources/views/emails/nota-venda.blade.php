<!DOCTYPE html>
<html lang="pt-BR">
<body style="font-family: Arial, Helvetica, sans-serif; color: #1f2937; margin: 0; padding: 24px; background: #f9fafb;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 32px; border: 1px solid #e5e7eb;">
        <h1 style="margin: 0 0 4px; font-size: 22px;">Ovo<span style="color:#d97706;">Gest</span></h1>
        <p style="margin: 0 0 24px; color: #6b7280; font-size: 13px;">Nota de venda Nº {{ str_pad($venda->numero, 6, '0', STR_PAD_LEFT) }}</p>

        <p>Olá, <strong>{{ $venda->cliente->nome }}</strong>!</p>
        <p>Segue em anexo a nota da sua compra realizada em <strong>{{ $venda->data_hora->format('d/m/Y \à\s H:i') }}</strong> com o vendedor <strong>{{ $venda->vendedor?->name ?? '—' }}</strong>.</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280;">Total da compra</td>
                <td style="padding: 8px 0; text-align: right; font-weight: bold;">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
            </tr>
            <tr style="border-top: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Valor pago</td>
                <td style="padding: 8px 0; text-align: right;">R$ {{ number_format($venda->valor_pago, 2, ',', '.') }}</td>
            </tr>
            <tr style="border-top: 1px solid #e5e7eb;">
                <td style="padding: 8px 0; color: #6b7280;">Valor em aberto</td>
                <td style="padding: 8px 0; text-align: right; font-weight: bold; color: {{ $venda->valor_em_aberto > 0 ? '#dc2626' : '#16a34a' }};">
                    R$ {{ number_format($venda->valor_em_aberto, 2, ',', '.') }}
                </td>
            </tr>
            @if ($venda->data_vencimento)
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="padding: 8px 0; color: #6b7280;">Vencimento</td>
                    <td style="padding: 8px 0; text-align: right;">{{ $venda->data_vencimento->format('d/m/Y') }}</td>
                </tr>
            @endif
        </table>

        <p style="color: #6b7280; font-size: 12px; margin-top: 28px;">
            Os detalhes completos (produtos, quantidades e valores) estão na nota em PDF anexa.<br>
            Em caso de dúvida, fale com o seu vendedor.
        </p>
    </div>
</body>
</html>
