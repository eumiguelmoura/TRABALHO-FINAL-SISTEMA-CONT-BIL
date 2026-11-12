# Arquitetura do Sistema Contábil

## Backend Laravel

| Camada | Caminho | Responsabilidade |
|---|---|---|
| Rotas | `apps/api/routes/api.php` | Publicar endpoints HTTP e aplicar `auth:sanctum` |
| Controllers | `app/Http/Controllers/Api/` | Receber requisições, chamar serviços, devolver JSON/status |
| Requests | `app/Http/Requests/` | Verificar campos, tipos e formatos |
| Services | `app/Services/` | Cálculos, comparação de totais e regras de lançamentos/apuração |
| Repositories | `app/Repositories/` | Consultar dados com restrição ao usuário autenticado |
| Models | `app/Models/` | Persistência Eloquent e relacionamentos |
| Migrations | `database/migrations/` | Definir tabelas, índices, FKs |
| Factories/Seeders | `database/` | Dados de demonstração e conta inicial |

`MoneyCalculator` calcula valores por item em centavos; `InvoiceService` compara todos os campos do cabeçalho com os resultados e usa transação de banco para criar/alterar nota e itens. Alteração/exclusão de nota apurada é bloqueada.

`TaxAssessmentService` verifica ownership, impede notas reapuradas e consolida os valores selecionados em uma transação. A pivot `assessment_invoice` mantém os vínculos e possui unicidade por invoice.

## Modelo de dados

```text
users 1---N invoices 1---N invoice_items
   |              |
   |              N---N tax_assessments (pivot assessment_invoice)
   +---N suppliers
   +---N catalog_products
   +---N tax_assessments
```

As notas guardam fornecedor textual para preservar o nome originalmente lançado. O recurso auxiliar `suppliers` é independente para cadastro de fornecedores reutilizável. Os produtos do catálogo são um recurso separado dos itens efetivamente lançados na nota para manter o histórico fiscal intacto.

## Frontend web

`apps/web/src/pages` usa roteamento baseado em arquivos do Quasar. A instância Axios centralizada em `src/boot/axios.js` inclui o Bearer token nas requisições. `src/utils/money.js` calcula prévias; a conferência definitiva ocorre no backend.

## Aplicativo Flutter

`core` guarda rotas, temas, textos e validadores. `models` representa as entidades; `services` executa HTTP e grava o token seguro; `screens` monta interfaces sem acessar banco diretamente; `widgets` disponibiliza componentes para carregamento, erro e listas vazias. O aplicativo utiliza a API Laravel, sem Firestore, mantendo uma única fonte de verdade MySQL.

## Limitações fiscais

O imposto de entrada informado é demonstrativo. O cálculo completo de impostos **a pagar** exige notas de saída e apuração das contas fiscais segundo o regime adotado. O valor de IPI costuma fazer parte do valor da nota fiscal; a subtração do bruto foi aplicada exclusivamente como critério didático fornecido pelo projeto.
