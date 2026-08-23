<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>OvoGest — Pitch</title>
<style>
    @page { margin: 0; }
    * { font-family: "DejaVu Sans", sans-serif; margin: 0; padding: 0; }
    body { color: #1e293b; font-size: 13px; }
    /* dompdf usa content-box: A4 paisagem 297x210mm -> 257 x 169 com padding 20/18-22 */
    .slide { width: 257mm; height: 169mm; page-break-after: always; position: relative; padding: 16mm 20mm 24mm; background: #ffffff; }
    .slide:last-child { page-break-after: avoid; }

    .marca { font-size: 19px; font-weight: bold; color: #0f172a; }
    .marca .g { color: #d97706; }

    .topo { border-bottom: 2px solid #f1f5f9; padding-bottom: 9px; margin-bottom: 20px; }
    .topo table { width: 100%; }
    .topo .num { text-align: right; color: #cbd5e1; font-size: 11px; font-weight: bold; }

    .kicker { display: inline-block; color: #d97706; font-size: 11px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }
    h2 { font-size: 27px; color: #0f172a; margin-bottom: 8px; letter-spacing: -0.5px; }
    .sub { font-size: 13.5px; color: #64748b; margin-bottom: 22px; line-height: 1.5; }

    .rodape { position: absolute; bottom: 9mm; left: 20mm; right: 20mm; border-top: 1px solid #e2e8f0; padding-top: 7px; font-size: 9px; color: #94a3b8; }
    .rodape .dir { float: right; }

    .ovo { display: inline-block; width: 42px; height: 54px; background: #f59e0b; border-radius: 21px 21px 19px 19px; margin-bottom: 14px; }
    .ovo-mini { display: inline-block; width: 20px; height: 26px; background: #f59e0b; border-radius: 10px 10px 9px 9px; vertical-align: middle; margin-right: 10px; }
    .capa-kicker { color: #f59e0b; font-size: 12px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 10px; }

    /* capa / fechamento */
    .escuro { background: #0f172a; color: #f8fafc; }
    .faixa { position: absolute; bottom: 0; left: 0; right: 0; height: 7mm; background: #f59e0b; }
    .capa .conteudo { margin-top: 26mm; }
    .capa h1 { color: #f8fafc; font-size: 54px; letter-spacing: -2px; }
    .capa h1 .g { color: #f59e0b; }
    .capa .tagline { font-size: 19px; color: #cbd5e1; margin-top: 12px; line-height: 1.5; }
    .capa .pontos { margin-top: 14mm; }
    .capa .pontos span { display: inline-block; background: #1e293b; border: 1px solid #334155; color: #e2e8f0; border-radius: 16px; padding: 6px 16px; font-size: 12px; margin-right: 8px; }
    .capa .meta { margin-top: 16mm; color: #64748b; font-size: 12px; }

    table.grade { width: 100%; border-spacing: 8px; border-collapse: separate; }
    table.grade td { vertical-align: top; }

    table.grade.compacta { border-spacing: 6px; }
    table.grade.compacta .cartao { padding: 10px 14px; }
    table.grade.compacta .cartao h3 { font-size: 13px; margin-bottom: 4px; }
    table.grade.compacta .cartao p { font-size: 10.5px; line-height: 1.45; }

    .cartao { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px 17px; }
    .cartao h3 { font-size: 14.5px; color: #0f172a; margin-bottom: 6px; }
    .cartao p { color: #475569; font-size: 11.5px; line-height: 1.55; }
    .cartao.destaque { background: #fffbeb; border-color: #fcd34d; }
    .cartao .tag { display: inline-block; background: #0f172a; color: #f8fafc; border-radius: 6px; padding: 2px 9px; font-size: 9.5px; font-weight: bold; letter-spacing: 1px; margin-bottom: 8px; }

    .conta { background: #0f172a; border-radius: 10px; padding: 16px 18px; color: #f8fafc; }
    .conta .rotulo { font-size: 11px; color: #94a3b8; }
    .conta .valor { font-size: 26px; font-weight: bold; color: #f59e0b; margin: 4px 0; }
    .conta .como { font-size: 10px; color: #64748b; }

    .etapa { background: #0f172a; color: #f8fafc; border-radius: 10px; padding: 14px 12px; text-align: center; }
    .etapa .n { display: inline-block; padding: 3px 10px; border-radius: 12px; background: #f59e0b; color: #0f172a; font-weight: bold; margin-bottom: 8px; font-size: 12px; }
    .etapa h4 { font-size: 13px; margin-bottom: 5px; }
    .etapa p { font-size: 10px; color: #cbd5e1; line-height: 1.45; }
    .seta { text-align: center; font-size: 19px; color: #d97706; font-weight: bold; }
    .equacao { background: #fffbeb; border: 2px solid #f59e0b; border-radius: 10px; padding: 12px; text-align: center; font-size: 16px; font-weight: bold; color: #92400e; margin-top: 16px; }

    table.perfis { width: 100%; border-collapse: collapse; }
    table.perfis th { background: #0f172a; color: #ffffff; padding: 8px 12px; text-align: left; font-size: 11.5px; }
    table.perfis td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; font-size: 11px; vertical-align: top; }
    table.perfis td.p { font-weight: bold; color: #0f172a; white-space: nowrap; }
    table.perfis.compacta th { padding: 6px 10px; font-size: 10.5px; }
    table.perfis.compacta td { padding: 6px 10px; font-size: 10px; line-height: 1.4; }
    .pill { display: inline-block; background: #fef3c7; color: #92400e; border-radius: 8px; padding: 1px 8px; font-size: 9.5px; font-weight: bold; }

    ul.lista { margin-left: 15px; }
    ul.lista li { margin-bottom: 8px; color: #334155; line-height: 1.5; font-size: 12px; }
    ul.lista li b { color: #0f172a; }

    .vantagem { border-left: 4px solid #f59e0b; padding: 2px 0 2px 14px; margin-bottom: 14px; }
    .vantagem h3 { font-size: 14px; color: #0f172a; margin-bottom: 3px; }
    .vantagem p { font-size: 11px; color: #475569; line-height: 1.5; }

    .passo { text-align: center; padding: 16px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; }
    .passo .dia { display: inline-block; background: #f59e0b; color: #0f172a; font-weight: bold; border-radius: 14px; padding: 4px 14px; font-size: 11px; margin-bottom: 10px; }
    .passo h4 { font-size: 14px; color: #0f172a; margin-bottom: 6px; }
    .passo p { font-size: 11px; color: #475569; line-height: 1.5; }

    .fechamento h2 { color: #f8fafc; font-size: 33px; margin-top: 36mm; letter-spacing: -1px; }
    .fechamento .p { color: #cbd5e1; font-size: 15px; margin-top: 12px; line-height: 1.6; }
    .fechamento .contato { margin-top: 20mm; font-size: 13px; color: #94a3b8; }
    .selo-ajr { display: inline-block; background: #1e293b; border-radius: 6px; padding: 4px 10px; color: #f8fafc; font-weight: bold; font-size: 12px; }
</style>
</head>
<body>

{{-- 1 · CAPA --}}
<div class="slide escuro capa">
    <div class="conteudo">
        <div class="ovo"></div>
        <div class="capa-kicker">Gestão de granja e distribuição</div>
        <h1>Ovo<span class="g">Gest</span></h1>
        <div class="tagline">A plataforma que transforma a venda de ovos em rota<br>em um negócio controlado, profissional e lucrativo.</div>
        <div class="pontos">
            <span>Cada bandeja rastreada</span>
            <span>Cada real contabilizado</span>
            <span>Zero papel</span>
        </div>
        <div class="meta">
            Pitch de produto · {{ now()->translatedFormat('F \d\e Y') }} &nbsp;·&nbsp; Desenvolvido por <b style="color:#f8fafc;">AJR Software</b>
        </div>
    </div>
    <div class="faixa"></div>
</div>

{{-- 2 · A DOR, QUANTIFICADA --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">02 / 11</td></tr></table></div>
    <span class="kicker">O problema</span>
    <h2>Quanto custa não saber?</h2>
    <div class="sub">Na venda embarcada, o prejuízo não aparece em nenhum caderno — ele simplesmente some no caminho. Faça as contas com a sua realidade:</div>
    <table class="grade">
        <tr>
            <td width="33%"><div class="conta">
                <div class="rotulo">1 bandeja &#8220;sumida&#8221; por dia</div>
                <div class="valor">R$ 6.570 /ano</div>
                <div class="como">1 bandeja × R$ 18 × 365 dias — sem conferência de retorno, ninguém percebe.</div>
            </div></td>
            <td width="33%"><div class="conta">
                <div class="rotulo">1 fiado esquecido por semana</div>
                <div class="valor">R$ 7.800 /ano</div>
                <div class="como">R$ 150 anotados no papel que nunca voltam × 52 semanas.</div>
            </div></td>
            <td width="33%"><div class="conta">
                <div class="rotulo">Fechamento manual do caderno</div>
                <div class="valor">+40 h /mês</div>
                <div class="como">~2h por dia conferindo anotações de vendedores — tempo que não gera venda.</div>
            </div></td>
        </tr>
    </table>
    <br>
    <ul class="lista">
        <li><b>E o pior não é o número — é não saber qual é o número.</b> Sem registro, quebra, extravio e erro de troco viram &#8220;custo do negócio&#8221;.</li>
        <li>O dono só descobre o resultado real no fim do mês, quando não dá mais para agir.</li>
    </ul>
    <p style="margin-top:8px; font-size:9.5px; color:#94a3b8;">* Cenários ilustrativos para dimensionar o impacto — substitua pelos seus volumes.</p>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 3 · A PROPOSTA --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">03 / 11</td></tr></table></div>
    <span class="kicker">A proposta</span>
    <h2>Um sistema que paga o próprio café da manhã</h2>
    <div class="sub">O OvoGest fecha as três torneiras por onde o dinheiro escapa — e ainda profissionaliza a sua marca perante cada cliente.</div>
    <table class="grade">
        <tr>
            <td width="33%"><div class="cartao destaque">
                <div class="tag">CONTROLE</div>
                <h3>Nada some no caminho</h3>
                <p>Tudo que sai no caminhão é conferido na volta, bandeja por bandeja. A conciliação automática acusa qualquer diferença <b>no mesmo dia</b> — quebra, extravio ou erro deixa de ser invisível.</p>
            </div></td>
            <td width="33%"><div class="cartao destaque">
                <div class="tag">CAIXA</div>
                <h3>Fiado com data para voltar</h3>
                <p>Toda venda registra valor pago e valor em aberto, com vencimento. O painel mostra o total a receber consolidado — a cobrança deixa de depender da memória de alguém.</p>
            </div></td>
            <td width="33%"><div class="cartao destaque">
                <div class="tag">DECISÃO</div>
                <h3>O dono enxerga hoje, não no fim do mês</h3>
                <p>Faturamento, margem real (descontando o custo do ovo), quebra por rota e ranking de clientes — atualizados a cada venda, no celular ou no computador.</p>
            </div></td>
        </tr>
    </table>
    <br>
    <div class="cartao">
        <h3 style="color:#d97706;">O efeito colateral mais elogiado: profissionalismo</h3>
        <p>Cada cliente recebe <b>por e-mail uma nota em PDF com a logo da granja</b>, valores e vencimento — no lugar do papelzinho de caderno. A granja que emite nota organizada é a granja em que o mercado confia (e da qual não troca de fornecedor).</p>
    </div>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 4 · COMO FUNCIONA --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">04 / 11</td></tr></table></div>
    <span class="kicker">Como funciona</span>
    <h2>Um ciclo fechado, do galpão à conciliação</h2>
    <div class="sub">Cinco passos que espelham exatamente o dia a dia da granja — sem mudar a rotina de ninguém, só registrando o que já acontece.</div>
    <table class="grade">
        <tr>
            <td width="18%"><div class="etapa"><span class="n">1</span><h4>Produção</h4><p>O galpão lança as bandejas do dia. O estoque vira a base para planejar as cargas.</p></div></td>
            <td width="2%" class="seta">&#8594;</td>
            <td width="18%"><div class="etapa"><span class="n">2</span><h4>Carga</h4><p>Nota de saída imutável em PDF acompanha o caminhão — e debita o estoque na hora.</p></div></td>
            <td width="2%" class="seta">&#8594;</td>
            <td width="18%"><div class="etapa"><span class="n">3</span><h4>Venda em rota</h4><p>O vendedor registra cada venda. Impossível vender o que não está no caminhão.</p></div></td>
            <td width="2%" class="seta">&#8594;</td>
            <td width="18%"><div class="etapa"><span class="n">4</span><h4>Retorno</h4><p>Sobra e devolução voltam ao estoque; quebra vira perda registrada.</p></div></td>
            <td width="2%" class="seta">&#8594;</td>
            <td width="18%"><div class="etapa"><span class="n">5</span><h4>Conciliação</h4><p>Automática. Bateu, ótimo. Não bateu, alerta vermelho no painel — no mesmo dia.</p></div></td>
        </tr>
    </table>
    <div class="equacao">Regra de ouro: SAÍDA = VENDAS + RETORNO &nbsp;·&nbsp; documentos fechados não podem ser alterados nem apagados</div>
    <br>
    <ul class="lista">
        <li><b>Numeração sequencial</b> em toda nota (saída, venda e entrada) — auditoria simples, sem furos.</li>
        <li><b>Preços congelados no momento da venda</b> — mudar a tabela amanhã não reescreve o histórico de ontem.</li>
    </ul>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 5 · PERFIS --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">05 / 11</td></tr></table></div>
    <span class="kicker">Para quem</span>
    <h2>Cada pessoa vê exatamente o que precisa</h2>
    <div class="sub">Permissões sob medida: o vendedor não vê custos, o financeiro não mexe na operação, o dono enxerga tudo — e ninguém acessa outra granja.</div>
    <table class="perfis compacta">
        <tr><th width="15%">Perfil</th><th>O que faz no OvoGest</th></tr>
        <tr><td class="p">Dono</td><td><b>Vê tudo, em detalhe e somente leitura</b> — cargas, vendas, retornos, estoque, conciliações e relatórios · painel executivo com faturamento, margem real, quebra e a receber, com filtro por período · rankings de rotas e clientes · cria e gerencia os acessos da própria equipe.</td></tr>
        <tr><td class="p">Administrativo</td><td>Operação completa: cadastros, montagem e fechamento de cargas, retornos e conciliação · recebe <span class="pill">notificação</span> a cada venda registrada em rota.</td></tr>
        <tr><td class="p">Financeiro</td><td>Todas as vendas com valor pago e em aberto · registra baixas de pagamento · conciliações e indicadores — o contas a receber vira rotina, não caça ao tesouro.</td></tr>
        <tr><td class="p">Vendedor</td><td>Modo campo: saldo do caminhão em tempo real, registra vendas em segundos, busca ou <b>cadastra estabelecimentos na hora</b> · enxerga apenas as próprias vendas, sem custos nem margens.</td></tr>
        <tr><td class="p">Produção</td><td>O encarregado do galpão lança a <b>produção diária</b> e consulta o estoque — sem ver vendas, preços ou financeiro.</td></tr>
        <tr><td class="p">Motorista</td><td><span class="pill">próxima fase</span> confirmação de carregamento e retorno direto do pátio.</td></tr>
        <tr><td class="p">Cliente</td><td><span class="pill">próxima fase</span> portal do estabelecimento: 2ª via de notas e extrato do que deve, sem precisar ligar.</td></tr>
    </table>
    <br>
    <div class="cartao">
        <p><b style="color:#0f172a;">Cada um sabe onde está:</b> o perfil logado aparece identificado no topo de todas as telas — e como saída, venda e retorno ficam registrados com hora e autor, a conversa muda de &#8220;acho que&#8221; para &#8220;está aqui&#8221;. Protege o patrão e protege o funcionário honesto. <b>A gestão das granjas na plataforma fica com a AJR Software, fora da sua operação.</b></p>
    </div>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 6 · O QUE O SISTEMA FAZ --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">06 / 11</td></tr></table></div>
    <span class="kicker">Funcionalidades</span>
    <h2>O que o OvoGest faz por você, todos os dias</h2>
    <table class="grade compacta">
        <tr>
            <td width="50%"><div class="cartao"><h3>Notas em PDF com a sua marca</h3><p>Nota de saída para o caminhão, nota de venda para o cliente — numeradas, com logo, CNPJ e campos de assinatura. Geradas em um clique.</p></div></td>
            <td width="50%"><div class="cartao"><h3>E-mail automático ao estabelecimento</h3><p>Fechou a venda, o cliente recebe a nota no e-mail na hora — com valores, o que foi pago e o que ficou em aberto. Cobrança formalizada sem esforço.</p></div></td>
        </tr>
        <tr>
            <td><div class="cartao"><h3>Validação de estoque embarcado</h3><p>O sistema conhece o caminhão: mostra o saldo por produto e bloqueia venda ou retorno acima do disponível — erro de digitação não vira divergência.</p></div></td>
            <td><div class="cartao"><h3>Conciliação e alertas automáticos</h3><p>Ao fechar o retorno, a conta fecha sozinha. Divergência vira alerta vermelho com badge no menu — impossível passar despercebida.</p></div></td>
        </tr>
        <tr>
            <td><div class="cartao"><h3>Contas a receber sempre à vista</h3><p>Pago, parcial ou em aberto — venda a venda, com vencimento e total consolidado no painel. O fiado deixa de ser fé e vira gestão.</p></div></td>
            <td><div class="cartao"><h3>Rankings que orientam decisão</h3><p>Rota com mais quebra, cliente que mais compra, tendência semanal de faturamento — os dados apontam onde agir primeiro.</p></div></td>
        </tr>
        <tr>
            <td><div class="cartao"><h3>Estoque e produção integrados</h3><p>Produção diária, semanal e mensal no painel. Carga debita, retorno devolve, quebra vira perda — o saldo do galpão sempre em dia, sozinho.</p></div></td>
            <td><div class="cartao"><h3>Relatórios prontos para o contador</h3><p>Fechamento mensal e extrato por cliente em PDF, vendas do mês em Excel — margem, recebimentos e contas a receber sem montar planilha.</p></div></td>
        </tr>
    </table>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 7 · BLINDAGEM --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">07 / 11</td></tr></table></div>
    <span class="kicker">Integridade</span>
    <h2>&#8220;E se alguém tentar burlar?&#8221;</h2>
    <div class="sub">Fizemos essa pergunta antes de você — e testamos cada resposta. O sistema não confia: ele verifica.</div>
    <table class="perfis">
        <tr><th width="55%">Tentativa</th><th>O que o OvoGest faz</th></tr>
        <tr><td>Alterar uma nota de saída depois de fechada</td><td class="p">Bloqueia — documento imutável</td></tr>
        <tr><td>Vender mais do que existe no caminhão</td><td class="p">Bloqueia — mostra o saldo real</td></tr>
        <tr><td>Registrar recebimento maior que a dívida</td><td class="p">Bloqueia — informa o valor em aberto</td></tr>
        <tr><td>Retornar mais bandejas do que saíram</td><td class="p">Bloqueia — aponta a conta exata</td></tr>
        <tr><td>Cancelar ou apagar venda após a conciliação</td><td class="p">Bloqueia — fechamento auditado é definitivo</td></tr>
        <tr><td>Excluir movimentos de estoque gerados pelo sistema</td><td class="p">Bloqueia — o livro-razão é indelével</td></tr>
        <tr><td>Fazer qualquer coisa sem deixar rastro</td><td class="p">Impossível — auditoria registra autor, hora e o antes/depois</td></tr>
    </table>
    <br>
    <div class="cartao destaque">
        <p><b style="color:#0f172a;">Comprovado, não prometido:</b> cada barreira é verificada por uma bateria de testes automáticos — mais um teste de ponta a ponta do ciclo completo. E a <b>trilha de auditoria</b> guarda tudo: quem criou, quem alterou, quem excluiu, campo a campo, com data e hora. Auditoria que nem o administrador consegue apagar.</p>
    </div>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 8 · FACILIDADE --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">08 / 11</td></tr></table></div>
    <span class="kicker">Facilidade</span>
    <h2>Simples como o caderno — só que certo</h2>
    <div class="sub">Sistema bom é o que a equipe usa sem reclamar. O OvoGest foi desenhado para quem passa o dia na estrada, não para quem gosta de computador.</div>
    <table class="grade">
        <tr>
            <td width="50%">
                <div class="vantagem"><h3>Zero instalação</h3><p>100% web: abre no celular, tablet ou computador. Nada para instalar, nada para atualizar — sempre a última versão.</p></div>
                <div class="vantagem"><h3>Telas em português, guiadas</h3><p>Formulários com máscaras (CNPJ, telefone), preços preenchidos sozinhos, avisos claros quando algo não fecha.</p></div>
                <div class="vantagem"><h3>Vendedor produtivo em minutos</h3><p>O menu do vendedor tem 3 itens. Escolhe a carga, escolhe o cliente, digita a quantidade — o resto o sistema faz.</p></div>
            </td>
            <td width="50%">
                <div class="vantagem"><h3>O sistema previne o erro</h3><p>Não deixa vender além do saldo, não deixa retornar mais do que saiu, não deixa apagar nota fechada. Menos correção, mais confiança.</p></div>
                <div class="vantagem"><h3>Notificações no lugar certo</h3><p>Venda nova? O administrativo vê no sino. Conciliação divergente? Badge vermelho no menu. Ninguém precisa ficar caçando.</p></div>
                <div class="vantagem"><h3>Um endereço só da sua granja</h3><p>Sua equipe acessa a granja pelo endereço exclusivo dela — simples de memorizar, impossível de confundir.</p></div>
            </td>
        </tr>
    </table>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 9 · SEGURANÇA --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">09 / 11</td></tr></table></div>
    <span class="kicker">Confiança</span>
    <h2>Seguro por construção</h2>
    <div class="sub">São os números do seu negócio — tratamos como dinheiro.</div>
    <table class="grade">
        <tr>
            <td width="33%"><div class="cartao"><h3>Seus dados são só seus</h3><p>Cada granja opera em ambiente isolado com endereço próprio. Usuários de uma granja jamais enxergam dados de outra — garantido em duas camadas de software.</p></div></td>
            <td width="33%"><div class="cartao"><h3>Tudo auditado, nada se perde</h3><p>Notas fechadas são imutáveis, numeração sem reaproveitamento — e cada ação no sistema fica registrada com autor, hora e o antes/depois de cada campo. Cada login, com IP e horário.</p></div></td>
            <td width="33%"><div class="cartao"><h3>Vigiado 24h pela AJR</h3><p>Monitoramento em tempo real: qualquer erro é capturado e chega à equipe AJR Software — muitas vezes antes de você perceber. Backups automáticos e atualizações contínuas inclusos.</p></div></td>
        </tr>
        <tr>
            <td colspan="3"><div class="cartao destaque">
                <h3>Roadmap — o que vem na sequência</h3>
                <p><b>App do vendedor</b> com funcionamento offline para rotas sem sinal · <b>portal do estabelecimento</b> (2ª via de notas e extrato) · <b>resumo diário via WhatsApp</b> para o dono · <b>perfil do motorista</b> para conferência no pátio. Quem entra agora participa da priorização.</p>
            </div></td>
        </tr>
    </table>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 10 · IMPLANTAÇÃO --}}
<div class="slide">
    <div class="topo"><table><tr><td class="marca">Ovo<span class="g">Gest</span></td><td class="num">10 / 11</td></tr></table></div>
    <span class="kicker">Implantação</span>
    <h2>Do aperto de mão ao primeiro caminhão rastreado</h2>
    <div class="sub">Implantação assistida pela AJR Software — sem parar a operação, sem depender de TI.</div>
    <table class="grade">
        <tr>
            <td width="33%"><div class="passo"><span class="dia">DIA 1</span><h4>Configuração</h4><p>Cadastramos juntos a granja (logo, CNPJ), produtos com preços e custos, veículos, rotas e a equipe com seus perfis.</p></div></td>
            <td width="33%"><div class="passo"><span class="dia">DIA 2</span><h4>Primeira carga acompanhada</h4><p>Montamos a primeira nota de saída com o seu time, o vendedor registra as vendas reais do dia e fechamos o primeiro retorno juntos.</p></div></td>
            <td width="33%"><div class="passo"><span class="dia">DIA 3+</span><h4>Operação normal</h4><p>A rotina roda no sistema. Suporte direto da AJR Software no período de adaptação — e evolução contínua da plataforma.</p></div></td>
        </tr>
    </table>
    <br>
    <ul class="lista">
        <li><b>Sem obra, sem servidor, sem contrato de fidelidade</b> — e os dados são seus, sempre.</li>
        <li><b>Treinamento incluído:</b> quem sabe preencher o caderno, sabe usar o OvoGest.</li>
    </ul>
    <div class="rodape">OvoGest — pitch de produto <span class="dir">Desenvolvido por AJR Software</span></div>
</div>

{{-- 11 · FECHAMENTO --}}
<div class="slide escuro fechamento">
    <div style="margin-top: 26mm;"><span class="ovo-mini"></span><span style="color:#f59e0b; font-size:12px; font-weight:bold; letter-spacing:3px;">OVOGEST</span></div>
    <h2 style="margin-top: 8mm;">A pergunta não é quanto custa o sistema.<br>É quanto custa continuar sem ele.</h2>
    <div class="p">Cada dia sem conferência é bandeja que some, fiado que envelhece e decisão no escuro.<br>Vamos colocar a sua granja no controle — a partir do próximo carregamento.</div>
    <div class="contato">
        <span class="selo-ajr">AJR</span> &nbsp;<b style="color:#f8fafc;">AJR Software</b> · soluções sob medida em gestão e pagamentos<br><br>
        Ovo<span style="color:#f59e0b; font-weight:bold;">Gest</span> · pitch de produto · {{ now()->format('d/m/Y') }}
    </div>
    <div class="faixa"></div>
</div>

</body>
</html>
