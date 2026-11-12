# API Contábil (Laravel + Sanctum)

Base local: `http://localhost:8182/api` — Documentação Scramble: `http://localhost:8182/docs/api`.

Envie `Accept: application/json` em todas as requisições e `Authorization: Bearer <TOKEN>` nas rotas protegidas.

| Método | Endpoint | O que faz |
|---|---|---|
| POST | `/auth/login` | Login com `username` e `password` |
| GET | `/health` | Saúde (público) |
| GET | `/auth/me` | Usuário atual |
| POST | `/auth/logout` | Revoga o token atual |
| GET, POST | `/invoices` | Lista e cria nota fiscal |
| GET, PUT, DELETE | `/invoices/{id}` | Detalha, altera ou exclui nota não apurada |
| GET | `/invoice-items`, `/invoice-items/{id}` | Consulta itens de notas autorizadas |
| GET, POST | `/tax-assessments` | Histórico e nova apuração |
| GET, DELETE | `/tax-assessments/{id}` | Detalhes e exclusão de apuração |
| GET, POST | `/suppliers` | Consulta/cadastro de fornecedores |
| GET, PUT, DELETE | `/suppliers/{id}` | Consulta, alteração e exclusão |
| GET, POST | `/catalog-products` | Consulta/cadastro de catálogo de produtos |
| GET, PUT, DELETE | `/catalog-products/{id}` | Consulta, alteração e exclusão |

## Cadastrar nota fiscal

```json
{
  "issue_date": "2026-11-12",
  "supplier": "Fornecedor Teste",
  "number": "1001",
  "city": "Guarapuava",
  "state": "PR",
  "icms_base": "30.00",
  "icms_total": "3.60",
  "ipi_total": "1.50",
  "gross_total": "30.00",
  "net_total": "24.90",
  "items": [{
    "sku": "EXEMPLO-01",
    "description": "Produto teste",
    "cfop": "1102",
    "quantity": "3",
    "unit_price": "10.00",
    "icms_base": "30.00",
    "icms_rate": "12.0000",
    "ipi_rate": "5.0000"
  }]
}
```

A API calcula `gross_total`, `icms_amount`, `ipi_amount` e `net_total` de cada item e compara os totais recebidos. Se houver divergência, retorna HTTP 422 com mensagens por campo. As quantidades admitem até três casas decimais, preços até duas e alíquotas até quatro.

## Apuração

`POST /tax-assessments`:

```json
{
  "name": "Entradas de novembro/2026",
  "invoice_ids": [1, 2, 3]
}
```

Os IDs devem existir, pertencer ao usuário autenticado e não ter sido apurados anteriormente. Para listar apenas notas disponíveis, utilize `GET /invoices?pending_only=1`. A resposta detalhada da apuração contém `total_icms`, `total_ipi` e `invoices`.

**Nota:** `total_icms` e `total_ipi` representam impostos **destacados nas notas selecionadas**, não valores fiscais efetivamente a pagar.
