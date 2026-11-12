# Checklist de disciplinas e entregas

## Paradigmas de Linguagem de Programação

A organização da API segue `Controller -> Service -> Repository -> Model` com funções reutilizáveis, separação de regras de negócio no `MoneyCalculator` e `TaxAssessmentService`, validação em `FormRequest` e padronização de acesso por Repository.

- [x] Arquitetura e separação de responsabilidades
- [x] Reutilização de funções e serviços
- [x] Services e Repositories
- [x] Organização e nomenclatura de arquivos
- [ ] Publicar link do repositório final (atividade de entrega do aluno)

## Programação Orientada a Objetos para Web

Recursos da API:

1. **Notas fiscais**: listagem, detalhe, inclusão, edição e exclusão de notas pendentes.
2. **Fornecedores**: CRUD completo.
3. **Produtos do catálogo**: CRUD completo (SKU, descrição, CFOP e alíquotas).
4. **Apurações**: criar, listar, consultar detalhes e excluir (operações de gerenciamento do agrupamento).
5. **Itens de notas**: listar e consultar; criação, edição e exclusão são feitas por meio do recurso de Nota para preservar consistência de totais.

- [x] Migrations de MySQL
- [x] Models Eloquent
- [x] Factories (User, Supplier, CatalogProduct, Invoice, InvoiceItem e TaxAssessment)
- [x] Seeders (administração, fornecedores e catálogo de exemplo)
- [x] Controllers REST
- [x] FormRequests de entrada e validações do domínio
- [x] Login com Laravel Sanctum
- [x] Documentação REST por Scramble (`/docs/api`) e exemplo `.http`
- [x] Acesso protegido por token e segregação por usuário
- [ ] Executar todas as demonstrações em ambiente com Docker, Composer e MySQL
- [ ] Publicar link do repositório final

## Programação para Dispositivos Móveis (Flutter)

Backend escolhido: **Opção A, API Laravel via HTTP**.

- [x] Estrutura `lib/main.dart`, `app.dart`, `core`, `models`, `services`, `screens`, `widgets`
- [x] Models Dart com serialização JSON
- [x] Services consumindo API HTTP, sem acesso direto da tela ao servidor
- [x] CRUD completo de Nota Fiscal no app Flutter
- [x] Mínimo de quatro telas distintas
- [x] Navegação go_router com rotas centralizadas
- [x] Formulários validados e componentes de tema
- [x] Estados de carregamento, erro e lista vazia
- [x] Tema em `core/theme`
- [x] Teste unitário básico de modelo
- [ ] Executar `flutter analyze` e `dart format` com Flutter SDK
- [ ] Capturar prints de telas e gravar vídeo para apresentação
- [ ] Colocar identificação do estudante e link do repositório no README de entrega

O projeto final deve ser explicado pelo aluno e os códigos devem estar nos repositórios indicados pelas disciplinas.
