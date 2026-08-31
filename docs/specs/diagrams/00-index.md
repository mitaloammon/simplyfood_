# Fluxos de casos de uso

Diagramas de sequência conferidos em `backend/routes/api.php`, controllers e services.

- [Autenticar usuário](auth-login.md)
- [Consultar e cadastrar catálogo](catalog-products.md)
- [Gerenciar clientes](customers.md)
- [Operar turno de caixa](cash-shift.md)
- [Gerenciar mesas e comandas](tables-commands.md)
- [Criar pedido COUNTER](orders-counter.md)
- [Registrar pagamento](payments.md)
- [Consultar métricas do dashboard](dashboard-metrics.md)

Todas as rotas de negócio usam o `establishment_id` do usuário autenticado. Recursos de outro tenant não são retornados.
