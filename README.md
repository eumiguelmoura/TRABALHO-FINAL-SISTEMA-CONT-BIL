# Sistema Contábil Integrado — Laravel + Quasar + Flutter

Projeto acadêmico integrado das disciplinas **Paradigmas de Linguagem de Programação**, **Programação Orientada a Objetos para Web** e **Programação para Dispositivos Móveis**.

## O que foi implementado

- **Login:** usuário (e-mail) e senha, tokens Laravel Sanctum, saída com revogação do token.
- **Início:** dois módulos principais, `Entrada de notas` e `Apuração de impostos`.
- **Notas de entrada:** dados gerais, quantidade livre de produtos, SKU, descrição, CFOP, base e alíquotas de ICMS/IPI, total bruto e líquido. Cálculo automático de cada produto; o Laravel recalcula e **recusa** notas com totais do cabeçalho diferentes dos itens. Listagem, detalhe, criação, edição e exclusão de notas ainda não apuradas.
- **Apuração:** listar notas, selecionar individualmente ou todas as pendentes, consolidar ICMS/IPI destacados, salvar apuração, consultar histórico e abrir detalhes. A mesma nota não pode integrar duas apurações ativas.
- **Recursos adicionais da API:** CRUD de fornecedores e CRUD do catálogo de produtos, reutilizáveis nos módulos contábeis.
- **Web e mobile:** consomem os mesmos endpoints e banco MySQL.

> **Aviso fiscal:** o resultado da apuração neste projeto é a soma de **valores de ICMS e IPI destacados em notas de ENTRADA**. Na escrituração real, entradas podem gerar créditos: **não é correto classificar essas somas automaticamente como ICMS ou IPI a pagar**. Uma apuração legal exigiria saídas, débitos, créditos admitidos, regime tributário, CFOP/CST e outros tratamentos fiscais. A fórmula `líquido = bruto - ICMS - IPI` reproduz apenas a regra didática solicitada, não o cálculo padrão de valor financeiro de NF-e (o IPI pode compor o valor da nota). Não use a simulação para declarar ou recolher impostos.

## Estrutura

```text
sistema_contabil_unificado/
├── apps/
│   ├── api/       # Laravel 13: routes, Controllers, Requests, Services, Repositories, Models, migrations, factories, seeders, testes
│   ├── web/       # Vue 3 + Quasar: login, início, entrada, apuração, histórico
│   └── mobile/    # Flutter: core, models, services, screens, widgets, go_router
├── docker/        # PHP, Nginx, Quasar
├── docs/          # documentação de arquitetura e API
└── docker-compose.yaml
```

> **Windows:** consulte também [docs/GUIA_INSTALACAO_WINDOWS.md](docs/GUIA_INSTALACAO_WINDOWS.md) para um roteiro detalhado de instalação e solução de erros.

## Iniciar no computador com Docker Desktop

**Requisitos:** Docker Desktop com Docker Compose, portas `8182`, `9010`, `8184` e `33106` desocupadas, acesso para baixar as imagens e dependências na primeira execução.

Abra um terminal na pasta `sistema_contabil_unificado`.

**1. Prepare o `.env` da API**

No Windows PowerShell:

```powershell
Copy-Item .\apps\api\.env.example .\apps\api\.env
```

No Linux/macOS:

```bash
cp apps/api/.env.example apps/api/.env
```

No arquivo `apps/api/.env`, configure **uma senha forte com ao menos 12 caracteres** em `SEED_ADMIN_PASSWORD` (a senha de exemplo não é aceita pelo seeder). O e-mail do administrador é definido por `SEED_ADMIN_EMAIL`.

**2. Construa os contêineres**

```bash
docker compose up -d --build
```

**3. Instale o Laravel e crie o banco**

```bash
docker compose exec -u root php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

Se o PHP não conseguir gravar em `storage/` ou `bootstrap/cache/`, ajuste as permissões desses diretórios no seu ambiente. No Windows, os volumes do Docker geralmente são compartilhados com permissões adequadas.

**4. Acesse**

| Aplicação | Endereço |
|---|---|
| Sistema web (Quasar) | http://localhost:9010 |
| API Laravel / teste básico | http://localhost:8182/api/health |
| Documentação interativa Scramble | http://localhost:8182/docs/api |
| phpMyAdmin (MySQL) | http://localhost:8184 |

Entre na interface web usando `SEED_ADMIN_EMAIL` e `SEED_ADMIN_PASSWORD` configurados por você.

O Quasar tem `VITE_API_BASE_URL=http://localhost:8182/api` configurado no Docker. O navegador chama a API pelo endereço publicado no computador, não pelo hostname do contêiner.

**5. Popular recursos demonstrativos opcionalmente**

```bash
docker compose exec php php artisan db:seed --class=SupplierSeeder
docker compose exec php php artisan db:seed --class=CatalogProductSeeder
```

**6. Rodar testes do Laravel**

```bash
docker compose exec php php artisan test
```

Os testes usam SQLite em memória e cobrem login protegido, cálculo, divergência, apuração única e segregação de usuários.

## Flutter — aplicativo móvel

**Requisitos:** Flutter SDK, Android Studio/emulador ou aparelho, e API Laravel funcionando. O ZIP contém o código Dart; gere os arquivos nativos de plataforma caso ainda não existam:

```bash
cd apps/mobile
flutter create .
flutter pub get
flutter analyze
flutter test
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8182/api
```

`10.0.2.2` é o endereço especial do computador host para o **emulador Android**. Em um celular físico, substitua pela URL HTTPS acessível ao telefone ou pelo IP local do computador (se permitido pela rede e firewall). Para usar HTTP na rede local durante desenvolvimento Android, pode ser necessário permitir tráfego HTTP em `android/app/src/main/AndroidManifest.xml`; em produção, use **HTTPS**.

O token do Flutter é guardado com `flutter_secure_storage`, não em código-fonte. O aplicativo Flutter **não usa Firebase**, porque a opção de backend escolhida é a **API Laravel via HTTP**.

### Telas e rotas mobile

| Rota | Tela |
|---|---|
| `/login` | Login |
| `/inicio` | Início com dois módulos |
| `/notas` | Listagem de notas |
| `/notas/nova` | Cadastro de nota |
| `/notas/:id` | Edição de nota |
| `/apuracoes` | Seleção de notas e histórico |
| `/apuracoes/:id` | Detalhes de apuração |

CRUD completo no Flutter para a entidade **Nota Fiscal**: `POST /invoices`, `GET /invoices`, `GET /invoices/{id}`, `PUT /invoices/{id}` e `DELETE /invoices/{id}`.

## Regras de cálculo

Cada item possui:

- `bruto = quantidade × valor unitário`;
- `ICMS = base ICMS × alíquota ICMS / 100`;
- `IPI = bruto do produto × alíquota IPI / 100`;
- `líquido = bruto - ICMS - IPI`.

O cabeçalho deve conter as somas dos itens para `BC ICMS`, `ICMS`, `IPI`, `bruto` e `líquido`. Os valores são arredondados para centavos **por item** e então somados. O Laravel valida a igualdade exata em centavos; cálculos sensíveis usam inteiros internamente.

**Exemplo:** 3 unidades × R$ 10 = R$ 30 brutos; base ICMS R$ 30 × 12% = R$ 3,60; IPI R$ 30 × 5% = R$ 1,50; líquido didático R$ 24,90.

## O que falta para produção

É uma entrega acadêmica em código-fonte, não um produto fiscal homologado. É necessário avaliar regras fiscais reais, perfis e permissões, auditoria, cadastro de empresas/CNPJ, validação de documentos XML, integração SEFAZ, conciliação de créditos/débitos, contingências, backup, TLS, políticas de senha e deploy seguro. A apuração de entradas não substitui a escrituração e o fechamento fiscal mensal.

## Entrega das disciplinas

Veja `docs/CRITERIOS_DAS_DISCIPLINAS.md` com o mapeamento das exigências, `docs/ARQUITETURA.md` para compreender o código e `docs/API.md` para testar rotas. **Não foram enviados códigos a repositórios GitHub automaticamente.** Publique em repositórios acessíveis ao professor e informe os links segundo as orientações da disciplina. Os screenshots das telas devem ser capturados após iniciar os aplicativos.
