# SimplyFood — Fase 1: contratos definitivos e plano técnico de evolução

**Data:** 22/10/2026  
**Natureza:** planejamento. Não implementa funcionalidades nem altera arquivos existentes, banco ou configuração.

## 1. Resumo executivo

1. **Catálogo comum:** preservar products/categories. Manter campos comuns existentes; adicionar somente atributos simples aprovados. Códigos de barras múltiplos, saldo e ledger ficam em estruturas relacionadas.
2. **Vendas separadas:** preservar orders/order_items/payments para alimentação. Criar sales, sale_items e sale_payments para o ciclo varejista.
3. **Estoque separado:** inventory_items/product_ingredients/stock_movements permanecem exclusivos dos ingredientes. Estoque comercial e eventos próprios referenciam products.
4. **Atomicidade:** conclusão da venda e baixa controlada no mesmo commit; bloquear saldo em MySQL antes de validar/subtrair; chave idempotente impede reprocessamento.
5. **Caixa compartilhável:** manter turnos, ligar pagamento varejista ao turno/venda e calcular dinheiro esperado por pagamentos cash líquidos e movimentos manuais, sem dupla contagem.
6. **Perfil não autoriza:** business_profile habilita módulos, mas tenant, perfil e capacidade são verificados no backend.
7. **Schema real desconhecido:** backup restaurável, comparação do schema/triggers e aprovação de regras operacionais são pré-condições à implementação.

## 2. Evidência e método

- **Fato verificado:** observado diretamente no repositório atual, com arquivo e linhas.
- **Inferência:** consequência a confirmar durante implementação, especialmente quanto ao comportamento concorrente em MySQL.
- **Recomendação:** contrato técnico proposto; não é funcionalidade existente.
- **Pendente de aprovação:** regra operacional que não pode ser deduzida do código.

O relatório da Fase 0 está em [auditoria-fase-0.md](auditoria-fase-0.md), mas esta revisão também confirmou as principais evidências diretamente nas fontes atuais. O banco ativo não foi consultado e testes não foram executados. O checkout já tinha alterações locais e artefatos não rastreados; nenhum foi alterado.

## 3. Auditoria direcionada

### 3.1 Catálogo, categorias e ingredientes

**Fatos verificados.** products é escopado por establishment_id, aponta opcionalmente a categories e tem name, description, price, cost_price, sku, is_available e preparation_time_minutes. SKU pode ser nulo e é único por estabelecimento. [DDL de products](../backend/database/sql/simplyfood-db.sql#L76), [Product model](../backend/app/Models/Product.php#L10).

As requests validam categoria e SKU no tenant do usuário; o serviço atribui establishment_id da identidade autenticada. [StoreProductRequest](../backend/app/Http/Requests/Product/StoreProductRequest.php#L10), [ProductService](../backend/app/Application/Catalog/ProductService.php#L11). Preço tem duas casas decimais e quantidade de order_items é inteira. Não há barcode nem unidade de venda no DDL/API atual. [order_items](../backend/database/sql/simplyfood-db.sql#L231), [StoreOrderRequest](../backend/app/Http/Requests/Order/StoreOrderRequest.php#L39).

product_ingredients associa produto a ingrediente com quantidade decimal; o trigger valida tenant ao inserir. A baixa de ingrediente decorre do fechamento de pedido alimentar, não de todo uso do produto. [product_ingredients](../backend/database/sql/simplyfood-db.sql#L305), [trg_ingredients_bi](../backend/database/sql/simplyfood-triggers.sql#L359).

OrderService obtém preço atual do produto e grava snapshot de unit_price em order_items. Preservar esses campos e a quantidade inteira para pedidos alimentares. [OrderService](../backend/app/Application/Orders/OrderService.php#L194).

### 3.2 Pedido, pagamento e caixa atuais

Pedido nasce OPEN em transação e exige turno aberto. Transições no serviço: OPEN → IN_PREPARATION → READY → DELIVERED → CLOSED; cancelamentos são permitidos antes do estado terminal segundo transições definidas. [OrderService](../backend/app/Application/Orders/OrderService.php#L20), [criação](../backend/app/Application/Orders/OrderService.php#L39), [mudança de estado](../backend/app/Application/Orders/OrderService.php#L120).

PaymentService bloqueia pedido e turno, insere pagamento CONFIRMED e soma pagamentos existentes; aceita pagamentos múltiplos/parciais, mas não rejeita sobrepagamento nem calcula troco. payment.cash_register_shift_id é nullable no DDL, mas serviço atual preenche com turno do pedido. [PaymentService](../backend/app/Application/Payments/PaymentService.php#L14), [payments DDL](../backend/database/sql/simplyfood-db.sql#L266), [request de pagamento](../backend/app/Http/Requests/Payment/StorePaymentRequest.php#L10).

CashService abre/fecha turno e impede fechar com pedidos ativos. O endpoint manual aceita BLEED/SUPPLEMENT; o DDL também enumera SALE/REFUND, mas o serviço não cria esses movimentos automaticamente para pagamentos atuais. Fechamento grava valor contado pelo operador, sem cálculo visível de saldo esperado. [CashService abertura](../backend/app/Application/Cash/CashService.php#L15), [CashService fechamento](../backend/app/Application/Cash/CashService.php#L86), [CashMovementRequest](../backend/app/Http/Requests/Cash/CashMovementRequest.php#L8), [cash_movements DDL](../backend/database/sql/simplyfood-db.sql#L181).

### 3.3 Trigger de ingredientes e tenant

Ao mudar pedido de estado não fechado para CLOSED, trg_orders_bu valida pagamentos confirmados e saldo de ingredientes; trg_orders_au_stock grava OUT agregado e decrementa inventory_items. O serviço repete validações dentro de transação e bloqueia o pedido. [trg_orders_bu](../backend/database/sql/simplyfood-triggers.sql#L201), [trg_orders_au_stock](../backend/database/sql/simplyfood-triggers.sql#L252), [ensureStock](../backend/app/Application/Orders/OrderService.php#L252).

**Inferência/risco:** a transição e imutabilidade terminal impedem a segunda baixa pelo caminho normal, mas não há chave unique de movimento por origem. Pré-teste de saldo e decremento são comandos separados; concorrência entre pedidos distintos requer teste MySQL para provar que estoque não pode ficar negativo. Não alterar trigger ou comportamento alimentar.

Serviços e requests usam establishment_id do usuário autenticado; login retorna tenant e papel, mas não verifica o status do estabelecimento. [AuthController](../backend/app/Http/Controllers/Api/AuthController.php#L13), [ProductService](../backend/app/Application/Catalog/ProductService.php#L33). Rotas são protegidas por papel fixo e o Vue também filtra navegação. [api.php](../backend/routes/api.php#L20), [EnsureRole](../backend/app/Http/Middleware/EnsureRole.php#L9), [router.ts](../frontend/src/app/router.ts#L65). Não foram encontrados Policies próprios ou administração de usuários nas rotas. UI não substitui validação de tenant/capacidade no backend.

### 3.4 Migrations e frontend/infra

A migration inicial lê DDL/triggers em SQL e seu down remove tabelas de domínio. Não editá-la nem usar rollback como mecanismo de recuperação de dados. [migration inicial](../backend/database/migrations/2026_01_01_000000_create_simplyfood_schema.php#L8). O Compose define PHP-FPM, Nginx, MySQL 8.4, Redis 7 e Node/Vite; o overlay WSL altera usuário/cache. [docker-compose](../docker-compose.yml#L1), [docker-compose.wsl](../docker-compose.wsl.yml#L1).

Os SQLs em backend/database/sql e docs/databases foram comparados e são idênticos. docs/backend/integrity.md ainda descreve como recomendação futura algumas constraints que já constam no SQL atual; em divergência, este relatório usa o DDL/triggers versionados como evidência, sem presumir que correspondam ao banco ativo.

O Vue versionado usa token em memória e remove localStorage.token no bootstrap; política de sessão após reload está pendente. [main.ts](../frontend/src/main.ts#L1), [token.ts](../frontend/src/shared/api/token.ts#L1). Nginx aponta root a backend/public; embora frontend/dist seja montado no Compose, não há alias aparente na configuração. Confirmar publicação da SPA no preparo técnico. [nginx default.conf](../infrastructure/nginx/default.conf#L6).

## 4. Contratos recomendados

### 4.1 Catálogo compartilhado

Preservar products/categories como catálogo comum, sem duplicar entidade. Mudança aditiva: endpoints alimentares continuam aceitando/enviando os campos atuais; order_items permanece inteiro e mantém preço histórico.

| Atributo | Recomendação | Escopo/validação |
|---|---|---|
| Identidade | UUID e tenant existentes | Servidor sempre deriva establishment_id do token. |
| SKU | Campo atual, opcional, único por loja | Preservar unique atual. SKU de produto soft-deleted ainda ocupa valor; política de reuso precisa aprovação. |
| Barcode | Um código opcional, texto normalizado, único por loja, se aprovado | Pendente: se múltiplos códigos. Se necessário, tabela filha product_barcodes com unique (establishment_id, code), não lista JSON. |
| Nome/descrição/categoria | Campos e relação existentes | Categoria do mesmo tenant; manter validação da aplicação e trigger atual. [triggers de produto](../backend/database/sql/simplyfood-triggers.sql#L23). |
| Preço/custo | Campos decimais atuais | Backend resolve preço; sale_items guarda snapshots. Custo não sai pela API sem permissão. |
| Disponibilidade | is_available existente | Não equivale a saldo. Retail exige também produto disponível e, se controlado, saldo suficiente. |
| Unidade/fração | Conceito de unidade no produto; quantidade decimal em sale_items se fração aprovada | Pendente: unitário versus peso/volume. Não mudar order_items.quantity. Se fracionário, escala fixa e preço calculado no servidor; nunca float. |
| Controle de estoque | Flag track_stock no produto; saldo em tabela separada | Produto não controlado vende sem saldo/ledger; controlado exige saldo inicial explícito antes de habilitar. |
| Preparo/ingredientes | Semântica atual permanece alimentar | Vendas retail não baixam product_ingredients. Não criar acoplamento entre track_stock e recipe. |

**Alternativas.** (A) Adicionar colunas escalares barcode, unidade e track_stock em products: simples, se valores únicos. (B) Tabelas complementares para vários barcodes, saldo e ledger: adequadas à cardinalidade/histórico. (C) Catálogo retail separado: duplicaria identidade, categoria e preço, sem limitação concreta que justifique; não recomendado.

**Estrutura recomendada:** colunas controladas somente para track_stock e unidade após aprovação; barcode direto se um código; tabela filha se múltiplos; saldo e ledger sempre relacionados, nunca colunas de saldo no produto ou JSON. Não acrescentar campos sem consumidor comprovado.

### 4.2 Estoque comercial

Modelo conceitual:

    establishments 1—N products
    products 1—0..1 retail_product_stocks
    retail_product_stocks 1—N retail_stock_movements
    sales 1—N sale_items; sale_items N—1 products
    retail_stock_movements N—0..1 sale_items (origem da baixa)

Saldo único por (establishment_id, product_id). Ledger append-only com UUID, produto/tenant, tipo OPENING/IN/OUT/ADJUSTMENT/REVERSAL, quantidade positiva, motivo, operador, instante, origem e chave idempotente. Uma referência de baixa de venda precisa ser única para a mesma finalidade; devoluções parciais têm eventos próprios limitados à quantidade comprada.

**Consistência:** InnoDB, uma transação para evento e saldo. Baixa bloqueia saldo com SELECT FOR UPDATE, valida track_stock e saldo, insere movimento idempotente e decrementa; update condicional available >= quantity é defesa adicional. Falha em qualquer operação reverte ambas. Entradas/ajustes também travam linha; ajuste registra delta, ator e motivo em novo evento, sem editar ledger anterior.

**Idempotência:** Idempotency-Key enviada na criação/conclusão; unique por tenant/operação/chave e resultado guardado. Repetir devolve recurso original, não repete pagamento ou baixa. Unique de origem do movimento impede segundo OUT. Reversão referencia original; ajustes/reversões não apagam histórico.

**Disponível:** no primeiro corte síncrono, saldo disponível = saldo atual; carrinhos não reservam. Checkout valida e baixa na conclusão. Reserva, expiração e reconciliação ficam fora do MVP inicial.

**Reconciliação:** ledger permite reproduzir saldo inicial + IN − OUT + ajustes/reversões. Relatório compara projeção e soma; divergência vira incidente, corrigido por ajuste auditado, nunca por edição silenciosa.

### 4.3 Venda de balcão

| Estrutura conceitual | Dados mínimos |
|---|---|
| sales | UUID, tenant, status, operador, turno, cliente opcional, subtotal/desconto/total, idempotency key e timestamps |
| sale_items | UUID, tenant, sale, product e snapshots de SKU/nome/unidade/preço, quantidade, subtotal/desconto/total |
| sale_payments | UUID, tenant, sale, turno, meio manual, valor aplicado, valor entregue se dinheiro, troco, status, operador e instante |
| sale_returns / sale_return_items | Documento reversor, venda/itens originais, quantidades, pagamentos devolvidos, motivo, operador e aprovador quando exigido |

Vínculos críticos devem garantir tenant igual, por FKs compostas em pares (id, establishment_id) onde viável e validação backend em todas as referências.

**Fluxo recomendado:** DRAFT → COMPLETED; DRAFT pode CANCELLED. Concluída é imutável. Cancelar após pagamento ou devolver gera documento/eventos vinculados; estados de leitura podem ser COMPLETED/PARTIALLY_RETURNED/RETURNED, derivados do ledger de retorno. Taxonomia final depende de aprovação.

Checkout é síncrono. Venda, linhas, pagamentos manuais, validação e baixa de estoque controlado são uma transação; COMPLETED e movimento ficam visíveis juntos após commit. Não há reserva de carrinho. Pagamento insuficiente ou falha de banco/estoque/auditoria aborta toda transação. Idempotency key evita duplicação HTTP.

Backend recupera produto e calcula quantidade × preço, subtotal, descontos e total. Ignora totais do browser. Sale_items guarda preço/dados da época. Usar decimal ou minor units, não float. Política de arredondamento e forma de desconto ainda pendentes.

Cancelamento antes de concluir não movimenta saldo ou pagamento. Após conclusão, não apagar/editar original. Retorno parcial limita quantidade ao comprado; mercadoria revendável gera entrada de estoque, não revendável não retorna ao saldo. Reembolso cria registro reversor ligado ao pagamento original; saída de dinheiro é evento próprio. Motivo e ator sempre auditados.

### 4.4 Pagamento e caixa

Não alterar payments alimentar. Retail usa sale_payments sem gateway; CONFIRMED significa registrado pelo operador, não confirmado por banco/adquirente.

**Recomendação sujeita à aprovação:** divisão de pagamento por meios aceita; sem crédito parcial pendente no MVP; conclusão exige valor aplicado suficiente. Para cash guardar tendered_amount, applied_amount e change=tendered−applied; numerário esperado soma somente aplicado líquido. PIX/cartão não entram no saldo físico. Não aceitar pagamento eletrônico maior que devido; limite de cash/troco requer aprovação.

Saldo esperado recomendado do turno: opening_balance + pagamentos cash aplicados em vendas concluídas + suplementos − sangrias − refunds cash. Comparar com valor contado; salvar diferença e explicação/ator. Não somar sale_payments e um cash_movements SALE agregado pela mesma venda. Sale_payments é fonte financeira; movimentos manuais documentam sangria/suprimento e correções explícitas.

Venda concluída exige turno OPEN do mesmo tenant, operador e turno registrados. Fechamento impede operações pendentes conforme regra aprovada. Não lançar venda retroativamente em turno fechado. Devolução após fechamento referencia pagamento/turno original, mas efeito financeiro usa turno atual aberto; sem turno, bloquear reembolso em dinheiro até autorização/abertura. Política final pendente.

### 4.5 Perfis e autorização

business_profile inicial: FOOD_SERVICE ou RETAIL, sem perfil misto. Perfil dirige módulo/fluxo; endpoint verifica auth + tenant + perfil + capacidade. Autenticação identifica usuário/token; autorização decide operação; administração gere usuários/estado/credenciais do próprio tenant. Recomenda-se impedir login de usuário de estabelecimento inativo, mas produto deve aprovar.

Não introduzir SELLER no enum nesta etapa: aumentaria mudança em schema, middleware e tipos. Reutilizar temporariamente papéis atuais, com capacidades específicas no backend.

| Operação | ADMIN | MANAGER | CASHIER | WAITER | KITCHEN |
|---|---:|---:|---:|---:|---:|
| Ler catálogo | Sim | Sim | Sim | Food-service | Só preparo food |
| Criar/editar produto, preço, categoria | Sim | Sim | Não | Não | Não |
| Venda retail | Sim | Sim | Sim | Não | Não |
| Desconto dentro do limite | Sim | Sim | Sim | Conforme regra atual food | Não |
| Desconto acima do limite | Sim | Sim | Solicita aprovação | Não | Não |
| Entrada/saída operacional de estoque | Sim | Sim | Não | Não | Não |
| Ajuste de estoque com motivo | Sim | Sim | Não | Não | Não |
| Cancelar antes da conclusão | Sim | Sim | Sim, auditado | Não | Não |
| Cancelar após pagamento/devolver | Sim | Sim | Solicita aprovação | Não | Não |
| Abrir/fechar caixa | Sim | Sim | Sim | Não | Não |
| Relatório da loja | Sim | Sim | Resumo do próprio turno | Não | Não |
| Administrar usuários do tenant | Sim | Sim sem alterar ADMIN | Não | Não | Não |

Matriz é proposta; limites de desconto, diferenças de caixa e cancelamento precisam aprovação. Administração mínima e permissões são pré-requisito antes de piloto retail, embora relatórios completos possam vir depois. Middleware Vue é apenas UX.

## 5. Decisões pendentes: alternativas, impacto e recomendação

Responsável por aprovar: usuário/dono do produto, com validação de operação/financeiro quando aplicável. Recomendações abaixo não equivalem a aprovação.

| Questão | Alternativas/impacto | Recomendação |
|---|---|---|
| 1. Múltiplos barcodes? | Coluna única simples; tabela filha atende embalagens, mais UI/índice | Um opcional único por produto e loja inicialmente; aprovar. |
| 2. Peso, volume ou fração? | Inteiro simplifica; decimal requer escala/preço e arredondamento | Se piloto vende a granel, aprovar decimal com escala definida; senão unidade inteira inicial. |
| 3. Produto sem estoque? | Todos controlados; ou flag por produto | Flag track_stock; não controlados não criam baixa; saldo inicial obrigatório ao habilitar. Aprovar default. |
| 4. Arredondamento? | Por linha/total e método alteram centavos | Definir regra monetária de duas casas e ordem desconto/total; validação financeira necessária. |
| 5. Desconto que exige gerente? | Limite por valor/percentual, linha/total ou todos | Matriz backend simples e auditada; teto exato requer decisão. |
| 6. Cancelar depois de pago? | Operador, cashier com motivo, manager/admin | Só manager/admin no MVP; cashier solicita. |
| 7. Devoluções parciais/meio? | Integral/parcial; dinheiro, original, crédito loja | Parcial por item até quantidade comprada; retorno ligado ao original; crédito loja fora do escopo inicial. |
| 8. Meio diferente do original? | Exigir meio original ou aceitar exceção | Exigir original, exceção autorizada e auditada; confirmar operacionalmente. |
| 9. Troco e split? | Bloquear excesso ou aceitar cash com troco; crédito pendente | Split aceito; sem crédito pendente; cash pode exceder e servidor calcula troco; eletrônico sem excesso. |
| 10. Saldo esperado? | Apenas espécie vs métodos; ledger vs agregado | Numerário pela fórmula da seção 4.4; PIX/cartão em relatório separado; aprovar responsabilidade de diferença. |
| 11. Operar após fechamento? | Bloquear, turno seguinte ou registrar no anterior | Venda exige turno aberto; refund em turno atual com vínculo original; sem turno, solicitação pendente, sem pagamento. |
| 12. Papéis FOOD_SERVICE? | Preservar, revisar, remover ou adicionar | Preservar ADMIN/MANAGER/CASHIER/WAITER/KITCHEN e contratos existentes. |
| 13. Sessão/token? | Memória, localStorage, cookie HttpOnly/Sanctum | Não persistir em localStorage sem avaliação; cookie exige CORS/CSRF. Decidir UX. |
| 14. Perfil dos existentes? | Default, inferência por mesas, decisão por loja | Inventário e aprovação humana tenant a tenant; nenhuma inferência ou atualização automática. |
| 15. Motivo/auditoria? | Sempre ou apenas exceções | Motivo em ajuste, desconto excedente, cancelamento pago, devolução e diferença de caixa; ator/tenant/instante sempre. Aprovar lista. |

## 6. Schema, migração e rollback

1. Backup consistente e restauração em cópia isolada; registrar versão MySQL, SQL mode, SHOW CREATE TABLE, índices, triggers ativos e tabela migrations. Comparar com SQL versionado. Fase 0 não verificou banco ativo.
2. Não editar migration inicial. Nova migration aditiva pode começar com business_profile nullable; classificar por lista aprovada. Só após reconciliação impor NOT NULL/default para novas lojas. Nenhum update automático de existentes.
3. Adicionar colunas opcionais sem mudar semântica/tipo usados pela API. Checar duplicatas e soft delete antes de novos UNIQUE. Manter SKU/índice atuais.
4. Criar tabelas de saldo, ledger, venda, itens, pagamentos e retornos em migrations próprias. Não alterar payments/orders/order_items/ingredientes/triggers existentes.
5. APIs novas aditivas; recursos antigos mantêm campos atuais. Frontend antigo continua operando em FOOD_SERVICE.
6. Rollback destrutivo somente se estruturas novas estão vazias e sem dependentes. Com venda/ledger real, forward fix; não apagar histórico. Ensaiar em cópia restaurada.
7. Interromper se schema/triggers divergem, constraints conflitam com dados, tenant não classificado, restore falha, migration excede janela ou smoke/regressão falha. Preservar logs/estado.

Riscos concretos: SKU único inclui soft-deleted; decidir reuso de código. FKs geralmente ligam por ID simples; validar tenant na aplicação, trigger ou FKs compostas. Compose define MySQL 8.4, mas Dockerfile local foi alterado de PHP 8.2 para 8.4; confirmar runtime alvo. Pedido retail não deve transitar orders.status nem acionar trigger de ingredientes.

## 7. Matriz de testes futuros

**Não executar nesta fase.** Rodar em CI/ambiente isolado MySQL compatível; SQLite não representa fielmente triggers, enums, generated columns e locks do DDL. Não usar volumes/dados locais para testes destrutivos.

| Caso | Tipo | Aceitação |
|---|---|---|
| Preço, desconto, total, troco/arredondamento | Unitário | Casos determinísticos; total do browser ignorado. |
| Transições e imutabilidade da venda | Unitário | Só estados permitidos; retorno cria evento separado. |
| Matriz papel/perfil e tenant | Feature/API + MySQL | Cross-tenant, perfil incompatível e papel insuficiente negados sem vazamento. |
| Produto sem controle | Integração MySQL | Vende sem saldo/baixa comercial; ledger alimentar intocado. |
| Saldo controlado insuficiente | Integração MySQL | Nenhum commit de venda/pagamento/baixa. |
| Duas conexões compram último item | Concorrência MySQL | Uma conclui, uma falha; saldo zero e uma baixa. |
| Repetição de Idempotency-Key | Integração MySQL/API | Mesma venda; nenhum pagamento/OUT adicional. |
| Falha entre venda/pagamento/movimento | Integração MySQL com fault point | Rollback total, sem parciais. |
| Ledger vs saldo após movimentos/reversões | Integração MySQL | Soma reproduz saldo; histórico preservado; duplicata impedida. |
| Cancelamento/devolução parcial | Unitário + integração | Limites por quantidade, ator/motivo e vínculos originais. |
| Caixa por meio e refund | Integração MySQL | Fórmula não soma PIX/cartão nem duplica pagamento/movimento. |
| Fechar turno com operação pendente | Integração MySQL | Regra respeitada; evento nunca se liga a turno encerrado. |
| Regressão pedido/pagamento/caixa alimentar | Integração MySQL/API | Contratos atuais permanecem válidos. |
| Trigger de ingrediente | Integração MySQL | Baixa somente ao fechar; corrida investigada; retail não toca ingrediente. |
| Migration/rollback em cópia | Integração MySQL | Aditiva e segura sem remover dados antigos. |

Cobertura atual visível: auth, health e exclusão de categoria; sem feature tests de pedidos/pagamentos/caixa/triggers nem arquivos frontend de teste versionados. Não foram executados testes nesta fase.

## 8. Plano técnico posterior

| Fase | Objetivo/dependências | Migration | Teste e aceite | Interrupção/rollback |
|---|---|---|---|---|
| 1. Preparação/schema real | Backup, restore, inventário, resolver pendências. | Nenhuma. | Restore válido, schema comparado, decisões documentadas. | Parar sem backup restaurável ou divergência inexplicada. |
| 2. Perfil e autorização | Dois perfis, backend tenant/profile/capability, operadores mínimos; preservar food. Depende de 1. | Aditiva; classificação humana antes de NOT NULL/default. | Matriz de acesso, IDOR negativo, login e regressão food. | Não habilitar retail com proteção só frontend; depois de uso, forward fix. |
| 3. Catálogo | Campos aprovados de unidade/barcode/track_stock. | Aditiva; duplicatas antes de unique. | Compatibilidade API/pedido, unicidade tenant. | Parar se colidir ou mudar semântica; rollback antes de uso. |
| 4. Estoque comercial | Saldo, ledger, entrada/saída/ajuste/reversão, reconciliação. | Tabelas próprias; saldo inicial como evento, sem conversão de ingrediente. | Concorrência, idempotência, rollback transacional, reconciliação. | Não habilitar sem saldo válido; corrigir forward sem apagar ledger. |
| 5. Venda balcão | sales/items, cálculo backend, estado, idempotência e baixa atômica. | Tabelas próprias e API/UI novas. | Multi-item, adulteração, tenant, repetição, falha e regressão food. | Desligar retail se duplicar/oversell ou baixar sem conclusão; preservar dados. |
| 6. Pagamento/retorno/caixa | sale_payments, split/troco, devolução, conciliação. | Tabelas próprias; payments food intacta. | Meios, refund em turno seguinte, fechamento/diferença. | Parar se caixa não concilia ou histórico mutável; não dropar dados. |
| 7. Usuários/relatórios | Completar gestão e relatórios básicos. | Aditiva se aprovada mudança de papel. | Revogação, capabilities, totais conciliados. | Núcleo de usuários move para Fase 2; relatório pode ficar. |

**Dependência antecipada:** gestão mínima de usuário/permissões deve acompanhar Fase 2, antes de piloto. Venda, ajuste e devolução precisam governança; relatórios completos ficam na Fase 7.

## 9. Critérios globais

- Alimentação mantém tabelas, rotas/status e baixa atual de ingredientes.
- Apenas FOOD_SERVICE/RETAIL, sem classificação implícita ou perfil misto.
- Backend deriva tenant e verifica perfil/capacidade.
- products continua catálogo comum sem duplicação injustificada.
- Estoque controlado não fica negativo concorrente; repetição não duplica evento.
- Ledger/saldo são atômicos, eventos auditáveis.
- Venda concluída é imutável; reversores preservam originais.
- Caixa reconcilia cash aplicado, contado e diferença sem incluir eletrônico como espécie.
- Migration validada em cópia restaurada sem editar/reverter inicial.

## 10. Checklist para autorizar implementação

- [ ] Schema/triggers ativos comparados ao SQL; versão/SQL mode anotados.
- [ ] Backup e restauração em cópia isolada comprovados.
- [ ] Perfil dos existentes aprovado loja a loja, sem inferência por mesas.
- [ ] Decidir barcode múltiplo e reuso pós-soft-delete.
- [ ] Decidir unidade/fração, escala e arredondamento.
- [ ] Decidir default de estoque, saldo inicial e bloqueio.
- [ ] Decidir desconto, cancelamento, devolução/reembolso e troco.
- [ ] Decidir fórmula de caixa e eventos após fechamento.
- [ ] Aprovar matriz de capacidades, gestão mínima e sessão frontend.
- [ ] Revisar FKs tenant-aware, índices, idempotência e rollback.
- [ ] CI MySQL isolado cobre concorrência, transação, trigger e regressão alimentar.
- [ ] Revisar alterações locais antes de publicar/deploy.

## 11. Limite da Fase 1

Este documento recomenda contratos e destaca decisões que dependem do produto/operação. Não iniciou Fase 2, não executou testes e não alterou arquivos existentes. Implementação posterior aguarda aprovação explícita.
