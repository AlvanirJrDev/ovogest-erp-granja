# OvoGest — Gestão de Granja e Distribuição

Plataforma web para gestão de uma granja de ovos: carregamento de caminhão (nota de saída), vendas em rota pelo vendedor, retorno (nota de entrada) e conciliação automática.

**Regra de ouro do sistema:** `Saída = Vendas + Retorno`. Toda carga fechada precisa bater essa equação — se não bater, a conciliação marca divergência.

## Stack

- **Laravel 12** (PHP 8.3) — o prompt original pedia Laravel 11, mas todas as versões 11.x possuem avisos de segurança conhecidos; o 12 é a versão corrigida e mantém a mesma estrutura
- **Filament 3.3** — painel administrativo e dashboard
- **spatie/laravel-permission** — perfis de acesso
- **barryvdh/laravel-dompdf** — geração do PDF da nota de saída
- **PostgreSQL 16**
- **Docker + Docker Compose** — app (PHP-FPM), nginx, postgres, worker de fila e Mailpit

## Subindo o ambiente

Pré-requisito: Docker instalado.

```bash
cp .env.example .env   # se ainda não existir
make setup
```

O `make setup` faz build, sobe os containers, instala dependências, gera a APP_KEY, roda migrations + seeds e publica os assets do Filament.

Acesse:

- **Painel da granja:** http://localhost:8000/app → após o login a URL carrega o slug da granja (ex.: `/app/granja-sao-jose/vendas`)
- **Painel da plataforma (super admin):** http://localhost:8000/plataforma
- **Mailpit (e-mails de dev):** http://localhost:8025

### Usuários de desenvolvimento

| Perfil | E-mail | Senha |
|--------|--------|-------|
| Super admin (plataforma) | `superadmin@ovogest.test` | `password` |
| Dono | `dono@granja.test` | `password` |
| Admin | `admin@granja.test` | `password` |
| Financeiro | `financeiro@granja.test` | `password` |
| Vendedor | `vendedor@granja.test` | `password` |
| Produção (estoque) | `producao@granja.test` | `password` |

> Usuários de demonstração são criados apenas com `APP_ENV=local`. Em produção, defina `SUPERADMIN_EMAIL` e `SUPERADMIN_PASSWORD` no `.env` antes do seed.

## Multi-tenant (SaaS)

O OvoGest atende **várias granjas na mesma instalação**, com dois painéis:

- **`/app` (tenant)** — a operação de cada granja, com o **slug da granja na URL** em todas as telas (`/app/granja-sao-jose/cargas`, `/app/granja-sao-jose/vendas`...), via multi-tenancy nativa do Filament.
- **`/plataforma`** — exclusivo do super admin: cadastro de granjas (nome, CNPJ, contato, logo e slug da URL) e de todos os usuários.

Cada granja é um tenant com nome, CNPJ, contato e **logo próprios — usados nos PDFs das notas**. Todos os dados operacionais (produtos, veículos, clientes, rotas, cargas, vendas, retornos, conciliações e até a numeração das notas) são isolados por granja em duas camadas: o global scope dos models e o escopo de tenant do Filament.

Fluxo de onboarding: o **super admin** cadastra a granja e o usuário **dono**; o dono cria os demais acessos da equipe dele.

## Perfis de acesso

- **super_admin** (plataforma) — gerencia granjas e usuários de todas; não participa da operação.
- **dono** — **visão somente leitura de tudo** (cargas, vendas, retornos, conciliações, estoque, cadastros e relatórios), dashboard executivo com filtro de período e **gestão dos usuários da própria granja** (admin, financeiro, vendedor, produção — nunca outros donos).
- **admin** — operação completa da granja: cadastros, cargas, vendas, retornos, ajustes de estoque e cancelamentos. Recebe notificação a cada venda.
- **financeiro** — dashboard, vendas (registra **baixas de pagamento**), conciliações, estoque (leitura) e relatórios.
- **vendedor** — modo campo (web): vê o saldo das cargas das rotas em que é responsável, registra vendas, busca ou cadastra estabelecimentos na hora. Só enxerga as próprias vendas, sem custos.
- **producao** — **responsável pelo estoque**: lança a produção diária do galpão e consulta os movimentos. Não vê vendas, preços, custos nem financeiro.
- **motorista / cliente** — roles reservados para fases futuras. A versão 2 prevê o app mobile do vendedor.

## Vendas e pagamento

- Cada venda registra forma de pagamento (dinheiro, pix, prazo), **valor pago no ato**, **valor em aberto** (calculado) e **vencimento** para vendas a prazo — situação `Pago`, `Parcial`, `Em aberto` ou `Cancelada`.
- **Baixas parciais**: o botão *Receber* (admin/financeiro) registra cada pagamento posterior — quem recebeu, quando, quanto e por qual forma. O histórico aparece na tela da venda; o sistema bloqueia receber mais do que o saldo em aberto.
- **Cancelamento rastreável** (admin): exige motivo, mantém a venda no histórico e devolve o saldo ao caminhão — só é possível antes da conciliação da carga. Venda cancelada não emite PDF, não aceita baixas e sai de todos os totais.
- A nota de venda em PDF é **enviada automaticamente por e-mail** ao estabelecimento (se ele tiver e-mail cadastrado), com itens, valores, valor pago e em aberto. Também dá para baixar o PDF ou reenviar o e-mail pela listagem de vendas.
- O dashboard do admin/dono mostra o total **a receber** consolidado.

## Estoque e produção

- Página **Estoque** (menu Operação): livro-razão das bandejas — cada movimento com data, tipo, quantidade e autor.
- **Lançar produção** (perfil produção ou admin): entrada diária por produto. **Ajuste de inventário** (só admin): correção com motivo obrigatório.
- **Automático**: fechar a carga debita o estoque; fechar o retorno devolve sobras e devoluções (quebra é perda).
- Saldo por produto visível no cadastro de Produtos e na montagem da carga. Saldo **negativo não bloqueia** a operação — fica em vermelho indicando produção não lançada.
- Widget no dashboard: produção **hoje / semana / mês** + estoque total — a base para planejar as cargas.

## Relatórios (menu Operação → Relatórios)

- **Fechamento mensal (PDF)** — faturamento, custo, margem, recebimentos, em aberto, quebra, vendas e conciliações do mês.
- **Extrato por cliente (PDF)** — compras do período com pagamentos e saldo devedor total.
- **Vendas do mês (CSV/Excel)** — planilha com valores, situação e vencimentos.

## Fluxo operacional

1. **Cadastros** — produtos (tipo de ovo + tamanho de bandeja **12, 15 ou 30 unidades**, cada combinação com seu preço e custo), veículos, clientes e rotas. A rota tem um **vendedor responsável** e os **clientes atendidos** vinculados a ela.
2. **Carga (nota de saída)** — cria a carga vinculada a uma rota e adiciona os itens. Ao **fechar a carga**, os itens tornam-se imutáveis e a carga fica liberada para vendas (status `fechada` = caminhão em rota). O botão **Gerar PDF** emite a nota de saída detalhada (itens, valores, vendedor responsável e clientes previstos na rota) para acompanhar o caminhão.
3. **Vendas** — registradas contra uma carga fechada, associadas a um **cliente da rota** (o select prioriza os clientes vinculados à rota da carga). O sistema valida o saldo disponível por produto (`saiu − vendido − retornado`) e grava o preço praticado no momento (histórico imutável).
4. **Retorno (nota de entrada)** — registra o que voltou no caminhão por motivo (sobra, quebra, devolução). Ao **fechar o retorno**, a conciliação é calculada automaticamente e a carga passa a `conciliada`.
5. **Conciliação** — somente leitura, nunca editada manualmente. Status `ok` ou `divergente` conforme a tolerância configurada (`GRANJA_TOLERANCIA_CONCILIACAO`, padrão 0).

Todas as notas (carga, venda, retorno) têm **numeração sequencial por tipo**, gerada com lock pessimista — números nunca se repetem, mesmo após exclusão.

## Comandos úteis

```bash
make up        # sobe os containers
make down      # derruba os containers
make migrate   # roda migrations
make fresh     # recria o banco com seeds
make test      # roda a suíte de testes
make bash      # shell no container da aplicação
make logs      # acompanha os logs
```

## Testes

```bash
make test
```

São 41 testes cobrindo as regras críticas:

- Fechamento de carga bloqueia edição/inclusão/exclusão de itens
- Venda não pode ultrapassar o saldo disponível na carga; retorno não pode exceder o que resta
- Conciliação calcula cenário OK e divergente (ignorando vendas canceladas)
- Numeração sequencial não se repete e é independente por tipo de nota **e por granja**
- Isolamento multi-tenant (usuário só enxerga a própria granja) e matriz de permissões por perfil
- Recebimentos parciais, cancelamento rastreável e imutabilidade pós-conciliação
- Estoque: produção entra, carga debita, retorno devolve (quebra não volta), movimentos automáticos indeléveis

Segurança adicional: senhas novas exigem 8+ caracteres com letras e números, e cada login é registrado na tabela `acessos` (usuário, IP, navegador, data).

## Fora de escopo nesta fase

- App mobile / PWA para vendedor e motorista
- Portal do cliente
- Notificações via WhatsApp
- Tabela de preços negociada por cliente (campo `tabela_preco_id` já reservado no cadastro)
- Alerta automático de vendas vencidas (o vencimento é registrado e aparece nos relatórios; a notificação de atraso é evolução futura)
- Cancelamento de carga/retorno fechados (vendas já têm cancelamento rastreável)
