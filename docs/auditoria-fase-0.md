# SimplyFood — Fase 0: auditoria técnica e contratos preliminares

**Data da auditoria:** 22/10/2026  
**Escopo:** somente inspeção e planejamento. Nenhum código, schema, configuração ou trigger foi alterado nesta fase; não foram executados migrations, seeders ou testes.

## Como ler este documento

- **Fato verificado**: observado no estado atual do repositório; a evidência aponta para o arquivo e linhas.
- **Inferência**: consequência técnica plausível dos fatos, a validar durante implementação.
- **Proposta**: contrato recomendado para as fases seguintes; não descreve comportamento já implementado.

O repositório já contém documentação, mas foram verificadas diferenças entre documentos e SQL: os dois arquivos SQL em `backend/database/sql/` e `docs/databases/` são idênticos; já [`docs/backend/integrity.md`](backend/integrity.md) contém trechos de recomendações antigas que não refletem o DDL atual (por exemplo, recomenda como futura a restrição SKU por estabelecimento que já existe no DDL). As conclusões abaixo priorizam o código e o SQL executável.

## 1. Diagnóstico do estado atual

### 1.1 Estrutura

**Fato verificado — backend.** Projeto Laravel 12 / PHP 8.2+ pelo [`backend/composer.json`](../backend/composer.json); a árvore contém `Models`, `Http/Controllers/Api`, `Http/Requests`, `Http/Resources`, `Http/Middleware`, `Application/*`, `OpenApi` e `Providers`. Há serviços separados para catálogo, clientes, pedidos, pagamentos, caixa, métricas, mesas e comandas. As rotas de API estão em [`backend/routes/api.php`](../backend/routes/api.php#L16).

**Fato verificado — frontend.** Vue 3 + TypeScript + Vite + Pinia + Vue Router, conforme [`frontend/package.json`](../frontend/package.json#L12) e [`frontend/src/main.ts`](../frontend/src/main.ts#L1). A organização divide módulos de auth, caixa, catálogo, clientes, dashboard, pedidos e mesas; os serviços chamam a API compartilhada em `frontend/src/shared/api/`. A navegação e os filtros visuais de papel ficam em [`frontend/src/app/router.ts`](../frontend/src/app/router.ts#L7).

**Fato verificado — dependências de execução.** `docker-compose.yml` liga Nginx, PHP-FPM, MySQL 8.4, Redis 7 e Node 20; MySQL usa volume nomeado `mysql_data`, backend e frontend usam bind mounts, e Vite fica exposto na porta 5173 ([`docker-compose.yml`](../docker-compose.yml#L1)). O serviço web monta `frontend/dist`, mas o Nginx versionado declara `backend/public` como `root` e encaminha rotas para `index.php` ([`default.conf`](../infrastructure/nginx/default.conf#L6)); não há alias visível para servir `frontend/dist`. Confirmar o modo de publicação pretendido (dev via Vite ou build estático) antes de alteração de infraestrutura. O overlay WSL ajusta HOME/UID e cache npm ([`docker-compose.wsl.yml`](../docker-compose.wsl.yml#L1)). O Dockerfile versionado em HEAD usa PHP 8.2; no checkout auditado há alteração local para PHP 8.4. A extensão PDO MySQL e redis, além de extensões PHP, são instaladas pelo Dockerfile ([`Dockerfile`](../infrastructure/php/Dockerfile#L1)).

**Fato verificado — estado local relevante.** No momento da auditoria, `git status --short` já mostrava alteração local em `infrastructure/php/Dockerfile` (PHP 8.2 → 8.4) e vários arquivos `.js` não rastreados sob `frontend/src`, além de `frontend/tsconfig.tsbuildinfo`. Estes arquivos foram preservados e não são tratados como parte confirmada do código versionado. Este relatório foi salvo como único novo arquivo intencional da Fase 0.

### 1.2 Usuários, autenticação e estabelecimento

**Fato verificado.** `users` tem UUID, `establishment_id` obrigatório, email globalmente único, senha, papel enum (`ADMIN`, `MANAGER`, `CASHIER`, `WAITER`, `KITCHEN`), status ativo/inativo e exclusão lógica. O estabelecimento tem status ativo/inativo. Veja [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L5) e [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L19).

Sanctum emite token bearer; login exige usuário ativo e senha válida e retorna `establishment_id` e papel ([`AuthController.php`](../backend/app/Http/Controllers/Api/AuthController.php#L13), [`User.php`](../backend/app/Models/User.php#L9)). Login não verifica `establishments.status`; não há administração de usuários nas rotas presentes. A autorização existente é middleware `role:` com lista fixa de papéis, registrada nas rotas; não foram encontrados `Policies` ou `Gates` próprios. O frontend também filtra páginas por papel, o que melhora a navegação, mas não substitui autorização backend.

### 1.3 Entidades, relacionamentos e fluxos críticos

| Domínio | Estruturas atuais e vínculos | Evidência |
|---|---|---|
| Núcleo por estabelecimento | `establishments` → users, customers, categories, products, mesas, comandas, caixas, turnos, movimentos, pedidos, pagamentos e ingredientes | [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L5) |
| Catálogo | `products` tem categoria opcional, preço/custo, SKU e disponibilidade; SKU é único por estabelecimento. Produto não tem barcode nem unidade de venda | [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L76) |
| Atendimento | `orders` se vincula a turno, atendente, cliente, mesa/comanda; `order_items` guarda quantidade inteira e preço unitário fotografado; tabela de histórico registra transições | [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L198) |
| Pagamento | `payments` pertence ao pedido, com método, valor, estado e código; aceita vários pagamentos para um pedido. Campo shift é nullable no DDL, apesar de o serviço atual sempre o preencher | [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L266), [`PaymentService.php`](../backend/app/Application/Payments/PaymentService.php#L14) |
| Caixa | `cash_registers` → `cash_register_shifts` → `cash_movements`; constraint de chave gerada permite no máximo um turno aberto por caixa. Tipos atuais incluem SALE/REFUND, mas o endpoint manual só aceita BLEED/SUPPLEMENT | [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L136), [`CashMovementRequest.php`](../backend/app/Http/Requests/Cash/CashMovementRequest.php#L10) |
| Ingredientes | `inventory_items` → `product_ingredients` (ficha técnica produto/ingrediente) → `stock_movements`; quantidade fracionária com 3 casas. Não é inventário de mercadorias para venda | [`simplyfood-db.sql`](../backend/database/sql/simplyfood-db.sql#L284) |

**Pedidos.** Criação exige turno aberto e deriva estabelecimento do usuário; itens só são inseridos/removidos enquanto `OPEN`; transições permitidas são `OPEN → IN_PREPARATION → READY → DELIVERED → CLOSED`, com cancelamentos antes do fechamento. `CLOSED` exige pagamentos confirmados suficientes e estoque suficiente de ingredientes. Isso está duplicado com salvaguardas no serviço e nos triggers. Evidência: [`OrderService.php`](../backend/app/Application/Orders/OrderService.php#L20), [`OrderService.php`](../backend/app/Application/Orders/OrderService.php#L39), [`OrderService.php`](../backend/app/Application/Orders/OrderService.php#L120).

**Caixa.** Abertura bloqueia o caixa e verifica turno existente; há garantia única adicional no MySQL. Fechamento rejeita pedidos ativos ou não cancelados/fechados, registra saldo informado e encerra o turno ([`CashService.php`](../backend/app/Application/Cash/CashService.php#L15), [`CashService.php`](../backend/app/Application/Cash/CashService.php#L86)). Pagamentos ficam associados ao turno do pedido e o turno não fecha enquanto o pedido está ativo. Não há hoje contrato de venda independente do pedido.

### 1.4 Isolamento e autorização existentes

**Fato verificado.** A maioria das consultas de aplicação busca recursos usando `user->establishment_id`, e as criações atribuem esse valor no backend (exemplo: [`ProductService.php`](../backend/app/Application/Catalog/ProductService.php#L11), [`CustomerService.php`](../backend/app/Application/Customers/CustomerService.php#L27), [`OrderService.php`](../backend/app/Application/Orders/OrderService.php#L39)). Requests validam referências de catálogo dentro desse estabelecimento. Vários triggers também validam vínculos cruzados em inserção/atualização (por exemplo, pedido e pagamentos: [`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L150)).

**Limite importante.** As FKs relacionais comuns não garantem por si que os `establishment_id` do filho e pai sejam iguais; algumas regras cruzadas estão em triggers e outras dependem da aplicação. O `establishment_id` jamais deve ser confiado ao payload frontend. Há oportunidade de fechar lacunas com escopos centrais/policies e, onde necessário, FKs compostas, sem assumir que a camada atual cobre todos os fluxos.

## 2. Migrations, SQL e triggers

### 2.1 Inventário

| Migration | Efeito observado | Risco / observação |
|---|---|---|
| `2026_01_01_000000_create_simplyfood_schema.php` | Executa, em ordem, `database/sql/simplyfood-db.sql` e `simplyfood-triggers.sql`; o `down()` remove a lista de tabelas em ordem explícita | É migration inicial não incremental: rollback apaga os dados dessas tabelas. O runner interpreta `DELIMITER` e executa SQL não preparado; não foi feita validação contra banco real nesta auditoria. [`migration`](../backend/database/migrations/2026_01_01_000000_create_simplyfood_schema.php#L8) |
| `2026_01_01_000100_create_personal_access_tokens_table.php` | Cria tokens Sanctum com morph UUID, hash único, abilities e expiração | Reversível por drop isolado, desde que a tabela não carregue dados necessários. [`migration`](../backend/database/migrations/2026_01_01_000100_create_personal_access_tokens_table.php#L9) |

Não há migration inicial incremental Laravel `Schema::create` para o domínio: o DDL está em SQL MySQL específico (ENUM, CHECK, coluna gerada, triggers). A migration inicial não deve ser editada para acomodar varejo; adicionar migration posterior preserva o histórico e permite plano de rollback mais delimitado. Rollback de migration aditiva também deve considerar dependências e dados criados após a subida.

### 2.2 Trigger de estoque de ingredientes

**Condições verificadas.** `trg_orders_bu` executa a verificação apenas quando o novo estado é `CLOSED` e o anterior não era. Exige pagamento confirmado agregado ≥ total; compara quantidade de ingrediente agregada por item com `inventory_items.stock_quantity`; transição válida para fechar só ocorre a partir de `DELIVERED`. `trg_orders_au_stock` tem a mesma condição de transição e, nessa transição, insere uma linha `OUT` por ingrediente (quantidade agregada) em `stock_movements`, depois subtrai do saldo. Evidências: [`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L201), [`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L226), [`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L252).

**Duplicidade.** No fluxo normal, a condição `OLD.status <> 'CLOSED'` mais a proibição de alterar pedidos terminais e o bloqueio transacional do pedido no serviço impedem uma segunda baixa daquele pedido. O trigger não declara chave idempotente/unique para `order_id + inventory_item_id + motivo`; a tabela permite múltiplas movimentações indistinguíveis com o mesmo pedido. Portanto reexecução manual, recriação de triggers/rotina de recuperação inadequada ou divergência de estado por intervenção fora do fluxo são risco de duplicidade, não algo impedido pela estrutura de dados. A criação de movimentação e atualização de saldo ocorrem na mesma instrução que atualiza pedido, portanto, em tabelas transacionais InnoDB, a transação do update inclui os efeitos do trigger.

**Operações dependentes.** Fechamento de pedidos de alimentação depende da baixa como consequência implícita do update de status. A manutenção de saldo e histórico de ingredientes depende da correspondência ficha técnica `product_ingredients`, da tabela `inventory_items` e do trigger. Operações administrativas diretas no banco podem contornar verificações da aplicação, mas não triggers ativos.

**Risco de varejo.** `inventory_items` representa insumos de receita: unidade livre, quantidade fracionária, ficha técnica e baixa quando pedido alimentar fecha. Associar mercadorias vendidas a essa estrutura ou a `product_ingredients` pode produzir baixa indevida de ingredientes ou acoplar estoque comercial ao ciclo de preparo/entrega. Estoque comercial deve ter entidades e eventos próprios; nenhuma conversão automática de ingrediente é aceitável.

**Outros triggers relevantes.** O SQL apaga e recria triggers no `up` da migration inicial; valida mesmo estabelecimento em relações específicas; garante pedidos em turno aberto, impede mudanças em pedidos/turnos fechados e impede turno fechar com pedido ativo ([`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L5), [`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L89)). Esta auditoria não alterou nem executou esses arquivos.

### 2.3 Fonte de schema e compatibilidade

**Fato verificado.** `backend/database/sql/*` e `docs/databases/*` estão idênticos, mas o conteúdo não é uma representação gerada do schema do banco em execução. Não foi possível confirmar estado de migrations, objetos, versão SQL mode, índices ou dados do ambiente Docker, pois nenhum acesso ao banco ativo foi usado.

**Preparação antes de mudança.** Capturar backup consistente e testar restauração; registrar versão de MySQL, `SHOW CREATE TABLE`, triggers e tabela `migrations`; comparar com arquivos do repositório e investigar drift; testar migração e rollback em cópia restaurada. Não usar `migrate:fresh`, remover volumes ou reconstruir o ambiente com perda de dados.

## 3. Contratos preliminares de negócio — propostas

| Tema | Alternativa considerada | Proposta | Justificativa / contrato preliminar |
|---|---|---|---|
| Perfil | Inferir perfil pelas mesas/funcionalidades; ou coluna explícita | Coluna `business_profile` em `establishments`, valores iniciais `FOOD_SERVICE` e `RETAIL`; existentes são candidatos a `FOOD_SERVICE`, sem atualização nesta fase | Perfil dirige módulos e experiência, não autorização. Código backend decide acesso às operações a partir do usuário/estabelecimento, perfil e permissão; frontend só espelha. Antes da migration, confirmar se haverá outros perfis ou estabelecimentos mistos. |
| Vendas | Reutilizar `orders` com mais tipos; ou venda independente | Preservar `orders/order_items` para alimentação. Introduzir `sales`, `sale_items`, `sale_payments` para varejo, com snapshots de SKU/nome/unidade/preço/desconto e vínculos a estabelecimento, operador, caixa/turno e cliente opcionais | Os estados e relações de pedido atendem mesa, comanda, preparo e entrega. Venda de balcão tem ciclo, cancelamento e devolução diferentes. Separação reduz regressão e preserva histórico. Não migrar histórico sem necessidade demonstrada. |
| Estoque | Reusar `inventory_items`; ou separar mercadoria | Estruturas comerciais independentes, p.ex. `retail_products` ou extensão explícita do catálogo com `track_stock`, mais saldo e `inventory_movements` próprios. Manter `inventory_items/product_ingredients/stock_movements` só para ingredientes | Catálogo compartilhado pode ser útil, mas uma entidade de produto de venda precisa SKU/barcode/unidade e regras diferentes de ficha técnica. Escolher extensão do `products` atual vs catálogo separado na Fase 1 com estudo de compatibilidade do frontend/API. Movimentação append-only com tipo, quantidade positiva, motivo/referência, usuário, instante e origem; ajuste exige motivo e auditoria. |
| Concorrência de estoque | Atualizar saldo sem trava; ou consistência transacional | Baixa/entrada/ajuste em transação InnoDB, lock `FOR UPDATE` do saldo ou update condicional atômico; validar não-negativo no mesmo commit. Índice idempotente por origem/tipo para impedir reprocessamento | Vendas concorrentes não podem oversell silenciosamente quando estoque for controlado. Definir política de produto sem estoque e venda offline antes de implementação. Usar decimal/inteiro em menor unidade conforme unidade vendida, sem float em cálculo monetário. |
| Pagamentos | Acoplar payment a `orders`; ou manter ledger de vendas | Preservar `payments` para pedidos. Varejo usa `sale_payments` associados à venda e turno, um registro por parcela/meio; estados e estorno rastreados sem apagar o original | O fluxo atual já aceita pagamentos parciais/múltiplos por pedido, mas valida apenas total confirmado no fechamento. Não alterar sem plano compatível. Vendas devem calcular total no backend, guardar valores fotografados e validar soma, troco e limites definidos. |
| Cancelamento/devolução | Editar/remover venda ou pagamento original; ou eventos reversores | Venda concluída é imutável para auditoria: cancelamento/devolução cria evento/documento referenciando venda/itens e pagamentos originais; reentrada de estoque e saída de caixa são movimentos explícitos, com operador/motivo | Evita apagar histórico e permite devolução parcial. Definir regras fiscais/financeiras e se estorno após fechamento é permitido antes da Fase 5. |
| Caixa | Reusar apenas `cash_movements`; ou origem comercial explícita | Reaproveitar o conceito de turnos, após desenhar associação de venda e movimento de caixa; um caixa pode atender ambos os módulos sem misturar origem. Cada recebimento/reembolso liga ao pagamento e turno; movimento agregado não substitui ledger | Atual estrutura já guarda SALE/REFUND como tipos possíveis, mas serviço de pedido não cria automaticamente cash_movement de venda e fechamento guarda saldo contado sem cálculo explícito esperado. Fazer reconciliação e compatibilidade antes de ampliar. |
| Tenant/autorizações | Identificador enviado pelo cliente; ou contexto autenticado | Backend deriva tenant do usuário autenticado; consultas de leitura/escrita sempre escopadas; valida referências e permissão no servidor; respostas não revelam existência de outro tenant | Identificador livre no frontend é entrada não confiável. Cobertura deve ser testada para IDOR e relações cruzadas, além de middleware de papel. Usar policy/gate ou serviço de autorização central e constraints no banco como defesa adicional. |
| Usuários | Papéis fixos apenas; ou permissões arbitrárias | MVP mantém papéis fixos com matriz mínima de capacidades, administrador da loja pode convidar/criar, desativar e redefinir acesso de usuários do próprio estabelecimento. ADMIN/MANAGER/CASHIER/SELLER (nome final pendente); ações sensíveis: desconto, ajuste de estoque, cancelamento/devolução, fechar caixa | Separar autenticação (identidade/token), autorização (capacidade e tenant) e administração (ciclo de vida dos usuários). Não precisa de RBAC dinâmico/pluggable no MVP. Administração mínima e permissão de operações críticas precisam ser antecipadas antes do piloto varejista, mesmo que relatórios completos permaneçam depois. |

**Contrato de preços.** Valores e descontos são calculados/validados no servidor. O cliente envia identificadores, quantidades e eventualmente desconto solicitado; servidor confirma preço atual e capacidade do usuário, persiste preço/descrição na linha da venda, calcula subtotal, desconto, total e pagamentos com decimal. Evitar aceitar totais do browser como autoridade.

**Código de barras.** Campo opcional no produto; unicidade por estabelecimento recomendada inicialmente, considerando que lojas diferentes podem usar o mesmo EAN. Normalização e possibilidade de múltiplos códigos por item ficam como dúvida; não presumir barcode globalmente único sem validar cenário de embalagem/variante.

## 4. Limites candidatos do MVP de varejo

| Item | Classificação | Justificativa / critério de contorno |
|---|---|---|
| Perfil `RETAIL` | Obrigatório | Habilita navegação e operações do perfil no backend e frontend; `FOOD_SERVICE` continua coberto. |
| Cadastro de produto, categoria, SKU, preço e unidade | Obrigatório | Base do catálogo varejista; validar preço e unidade no servidor, SKU único por estabelecimento. |
| Barcode opcional | Obrigatório (campo opcional) | Cadastro e busca manual por barcode são valiosos no caixa; unicidade no tenant. Leitor de hardware é posterior. |
| Controle de estoque habilitável por produto | Obrigatório | Permite itens não controlados e itens controlados, com política explícita para falta de saldo. |
| Entradas, saídas, ajustes e histórico | Obrigatório | Operações auditáveis por usuário, motivo e timestamp; saldo reconciliável com ledger. |
| Venda balcão com múltiplos itens | Obrigatório | Fluxo principal varejista independente de pedido alimentar. |
| Preços, descontos e totais calculados backend | Obrigatório | Integridade comercial e prevenção de manipulação pelo cliente; política de teto de desconto definida antes do piloto. |
| Pagamento manual em dinheiro, PIX, crédito/débito e divisão | Obrigatório | Pagamentos manuais sem gateway; soma vinculada à venda; fechamento só sob regra definida de valor pago/troco. |
| Cancelamentos e devoluções rastreáveis | Obrigatório | Sem apagar venda/pagamento original; devolução parcial e movimento de estoque/caixa referenciados. |
| Abertura e fechamento de caixa | Obrigatório | Necessário para operação, com relatório de esperado vs contado, diferenças e usuário responsável. |
| Clientes e histórico de compras | Cadastro/histórico básico obrigatório; campos avançados opcionais | Venda sem cliente deve ser possível; cadastro e consulta por estabelecimento, histórico construído de vendas. Consentimento/retencão precisam de definição. |
| Relatórios operacionais básicos | Opcional no primeiro piloto, obrigatório antes de escala | Resumo de vendas por período, pagamentos, cancelamentos e estoque baixo podem iniciar como telas simples; decisão de prontidão do piloto depende do processo operacional local. |
| Permissões mínimas | Obrigatório e antecipado | Matriz server-side para operações críticas e administração mínima de usuários antes de expor retail a usuários reais. |
| Fornecedores e compras simplificadas | Posterior | Entrada inicial de estoque pode ser manual e auditada; compra/recebimento/fiscalidade aumentam escopo e integram fase 7. |
| Fiscal, gateway, e-commerce, filiais, fidelidade avançada, hardware e contabilidade avançada | Excluído do MVP | Conforme limite expresso do plano. |

## 5. Riscos

| Risco | Impacto | Probabilidade | Mitigação / evidência |
|---|---|---|---|
| Baixa duplicada, oversell concorrente ou perda de saldo | Alto | Média | Origem idempotente única por operação e ledger; lock/atualização condicional atômica; reconciliação. O trigger atual evita segunda transição normal para `CLOSED`, mas não tem unique por pedido/ingrediente; o pré-teste de saldo e o decremento são comandos distintos, portanto a corrida entre fechamentos de pedidos diferentes deve ser validada contra MySQL. [`simplyfood-triggers.sql`](../backend/database/sql/simplyfood-triggers.sql#L226). |
| Mistura de mercadoria e ingrediente | Alto | Média | Domínios/tabelas e serviços distintos; nenhuma migração automática; testes de isolamento de regra antes de ativar perfil retail. |
| Acesso cruzado entre estabelecimentos | Crítico | Média | Derivar tenant do usuário; centralizar escopos; testar IDs de outro tenant para cada endpoint; complementar triggers/FKs compostas. A integridade atual lista dependências da camada app em [`integrity.md`](backend/integrity.md#L38). |
| Rollback apaga dados por causa da migration inicial | Crítico | Média | Preservar migration; adições reversíveis e ensaiadas em cópia; rollback planejado só para estruturas novas vazias; backups e restauração ensaiada. [`migration`](../backend/database/migrations/2026_01_01_000000_create_simplyfood_schema.php#L14). |
| Drift entre schema documentado e banco real | Alto | Média | Inspecionar banco ativo, triggers e tabela migrations; comparar SHOW CREATE com DDL; não inferir estado do volume a partir do repositório. |
| Regressão do fluxo alimentar de pedido/caixa | Alto | Média | Novas rotas e tabelas sem alteração de contrato atual; testes de regressão de abertura, pagamento, fechamento, baixa de ingrediente e turno. |
| Desconto, total ou devolução inconsistente | Alto | Média | Cálculo monetário no backend, preço histórico por linha, movimentos de reversão e autorização sensível; definir política de arredondamento e troco. |
| Papéis de UI tratados como segurança | Alto | Média | Todas as regras no backend; proteger cada endpoint e validar capacidade além do papel visível no router Vue. |
| Migração de produto/usuários sem compatibilidade | Alto | Baixa/Média | Atribuição explícita de perfil, migração expand/contract e consulta de registros existentes antes de ativar retail. Nenhuma atualização de dados nesta fase. |
| Baixa cobertura das regras críticas | Alto | Alta | Adicionar testes de feature e concorrência por fase; há 9 cenários em três arquivos de teste (auth, remoção de categoria e health), sem cobertura feature visível para pedidos, pagamentos, caixa ou triggers. Nenhum arquivo de teste frontend está versionado na árvore inspecionada. |
| Ambiente/rebuild destrói dados | Crítico | Baixa/Média | Nunca remover volumes ou executar `migrate:fresh`; exigir backup restaurável, e distinguir build de imagem de reset do volume. `mysql_data` é volume nomeado em [`docker-compose.yml`](../docker-compose.yml#L72). |

## 6. Roadmap e critérios de conclusão

| Fase | Dependências | Critérios de aceitação verificáveis |
|---|---|---|
| 1. Contratos e preparação técnica | Esta auditoria; resposta às dúvidas; snapshot/restore do banco | Contratos de perfil, catálogo, transação, preço, devolução e permissões aprovados; schema real e triggers inventariados; backup restaurado em ambiente isolado; matriz de testes e rollback revisada. |
| 2. Perfil do estabelecimento e autorização | Fase 1 | Migration aditiva e rollback ensaiado; estabelecimentos atuais mapeados como `FOOD_SERVICE` por decisão explícita; perfil derivado no backend; acesso cruzado negado em endpoints; front-end condiciona módulos sem substituir autorização; login de estabelecimento inativo bloqueado conforme decisão. |
| 3. Catálogo comercial e estoque | Fases 1–2 | Produto/categoria/SKU/unidade/barcode; barcode único no tenant; stock tracking opcional; entradas/saídas/ajustes com ator e motivo; saldo consistente sob chamadas concorrentes; prova de que venda retail não altera `inventory_items`. |
| 4. Vendas de balcão | Fases 1–3 | Criar/listar/consultar venda multi-item; preço, desconto e total validados no backend; preço histórico persistido; tenant e status verificados; duplicar requisição não duplica venda/baixa; `orders` alimentares continuam com contratos existentes. |
| 5. Pagamentos e caixa | Fase 4 (caixa compartilhado depende de estudo fase 1) | Vários meios por venda, pagamentos parciais conforme regra, reconciliação de numerário, PIX/cartão manual; cancelamento/estorno ligado ao original; movimentos de caixa e vendas conciliáveis; fechamento não ocorre com operação aberta conforme regra aprovada. |
| 6. Usuários, permissões e relatórios | Matriz começa na fase 1; núcleo mínimo antecipado até fase 4/5 | Administração de usuário do mesmo tenant, desativação/revogação de token, capacidade por operação crítica; trilha de ator; relatórios fecham com dados de caixa e vendas. O conjunto mínimo de usuários/permissões não deve aguardar a fase 6 para o primeiro piloto retail. |
| 7. Fornecedores e compras simplificadas | Fases 3 e 5 | Fornecedor e recebimento geram entrada de estoque rastreável; custo e referência persistidos; compra cancelada/revertida sem apagar movimento. |
| 8. Identidade visual e evolução da marca | Aprendizado das fases anteriores | Nomes, navegação e componentes acomodam os dois perfis; marca e conteúdo validados sem alterar contratos de dados; fluxos alimentares existentes permanecem identificáveis. |

### Sequência sugerida para dependências antecipadas

Embora “usuários, permissões e relatórios” esteja na fase 6 do roadmap, autenticação é existente e autorização server-side é requisito de segurança do MVP. Antecipar na fase 2 a matriz de permissões, proteção por perfil/tenant, administração mínima de operadores e revogação de acesso; finalizar relatórios na fase 6. Fazer vendas antes dessa autorização mínima deixa operações críticas sem controle adequado.

## 7. Dúvidas sem evidência suficiente no repositório

1. Qual perfil inicial se aplica a cada estabelecimento real, e existe algum que vende alimentos e mercadorias no mesmo caixa?
2. Um SKU/barcode pode ser repetido em variantes, embalagens ou marcas? Barcode deve aceitar mais de um por produto?
3. Deve bloquear venda sem saldo, permitir saldo negativo com autorização, ou permitir produto sem controle de estoque?
4. Qual regra de fracionamento/unidade varejista (peso, volume, unidade) e arredondamento monetário é necessária?
5. Qual teto de desconto exige gerente? Quem pode cancelar após pagamento/fechamento? Devoluções serão parciais e em dinheiro, crédito ou meio original?
6. O que significa “PIX/cartão manual” operacionalmente e como conciliar taxas, comprovantes, troco e pagamentos parcialmente recebidos?
7. O caixa é um só por loja, pode haver múltiplos operadores simultâneos ou turnos independentes? O saldo esperado deve incluir pagamentos não em espécie?
8. E-mail global único é requisito de login ou há compartilhamento de pessoa entre estabelecimentos? O schema atual impõe unicidade global.
9. Quais ações críticas exigem justificativa/auditoria e quais papéis existentes devem continuar válidos no módulo alimentar?
10. O ambiente Docker ativo contém dados que precisam preservação? Qual banco é fonte de verdade: volume local, staging ou outro ambiente?
11. Confirmar comportamento de sessão no frontend versionado: `main.ts` limpa a chave `token` ao inicializar, e `token.ts` mantém token somente em memória; a experiência após reload precisa decisão explícita antes do rollout.

## 8. Checklist para autorizar a Fase 1

- [ ] Aprovar entidade separada `sales/sale_items/sale_payments` para varejo ou registrar justificativa para outra forma.
- [ ] Aprovar separação rígida entre estoque comercial e ingredientes.
- [ ] Definir estratégia de catálogo (extensão controlada de `products` ou catálogo comercial separado).
- [ ] Confirmar defaults do perfil e processo de classificação dos estabelecimentos existentes.
- [ ] Aprovar o mínimo de papéis/capacidades e antecipação da administração de usuários.
- [ ] Definir regras de desconto, estoque negativo, cancelamento, devolução, troco e fechamento de caixa.
- [ ] Definir barcode/sku e unidade de medida do varejo.
- [ ] Designar ambiente e responsável pelo backup/restauração e auditoria do schema real.
- [ ] Fazer inventário de dados e triggers em execução, verificando drift com os SQL versionados.
- [ ] Aprovar plano de migrations aditivas, critérios de rollback e proibição de `migrate:fresh`/remoção de volumes.
- [ ] Definir testes de regressão obrigatórios para pedido alimentar, pagamentos, caixa e trigger de baixa de ingrediente.
- [ ] Revisar o estado local já modificado/não rastreado antes de qualquer fase que publique ou faça deploy.

## 9. Limite desta entrega

Esta fase entrega auditoria e propostas. Não aprova por si só alterações de schema nem implementa o MVP. A Fase 1 deve produzir contratos fechados e preparação técnica verificável; só depois da aprovação explícita do usuário poderá começar implementação.
