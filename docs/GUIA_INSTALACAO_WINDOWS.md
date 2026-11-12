# GUIA DE INSTALAÇÃO NO WINDOWS — SISTEMA CONTÁBIL

Este documento acompanha o projeto efetivo. Para o primeiro uso, **não edite controllers PHP nem instale PHP no Windows**: o contêiner PHP já executa PHP 8.4 e o Composer. Use o terminal **PowerShell** do VS Code na pasta que contém `docker-compose.yaml`.

## 1. Pré-requisitos

- Docker Desktop aberto, funcionando com Linux Containers/WSL 2.
- VS Code; internet na primeira instalação para baixar imagens Docker, pacotes Composer e npm.
- Portas livres: 8182 (API), 9010 (web), 8184 (phpMyAdmin), 33106 (MySQL).
- Flutter SDK + Android Studio **apenas** para iniciar o app Android. O site web funciona sem eles.

Teste: `docker --version`, `docker compose version`, `docker info`.

## 2. Preparar variáveis (.env)

Abra no VS Code a pasta `sistema_contabil_unificado` (onde está `docker-compose.yaml`). No terminal PowerShell:

```powershell
Copy-Item .\apps\api\.env.example .\apps\api\.env
Copy-Item .\apps\web\.env.example .\apps\web\.env
notepad .\apps\api\.env
```

Mantenha estes parâmetros do banco na **API** (não use `localhost` como DB_HOST dentro do Docker):

```dotenv
APP_URL=http://localhost:8182
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=sistema_contabil
DB_USERNAME=contabil
DB_PASSWORD=contabil_local_somente
CORS_ALLOWED_ORIGINS=http://localhost:9010,http://127.0.0.1:9010
SEED_ADMIN_EMAIL=admin@contabil.local
SEED_ADMIN_PASSWORD=COLOQUE_AQUI_UMA_SENHA_FORTE_COM_12_OU_MAIS_CARACTERES
```

**Atenção:** modifique o valor `SEED_ADMIN_PASSWORD` para uma senha real escolhida por você (não utilize o exemplo acima como senha). O seeder rejeita a senha placeholder distribuída no ZIP. O arquivo `.env` é segredo local: não o envie ao GitHub.

No `apps/web/.env`, mantenha:

```dotenv
VITE_API_BASE_URL=http://localhost:8182/api
```

As credenciais MySQL acima devem coincidir com as que estão em `docker-compose.yaml`. Se mudar a senha do MySQL após o primeiro início, os dados persistidos no volume podem exigir reconfiguração; não apague volumes se houver notas cadastradas.

## 3. Primeira inicialização

No mesmo terminal, estando na pasta raiz do projeto:

```powershell
docker compose up -d --build
docker compose ps
docker compose exec -u root php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate --force
docker compose exec php php artisan db:seed --force
```

Pode levar alguns minutos no primeiro início por causa de downloads e compilações. Se `composer install` apresentar erro de permissão em `storage` ou `bootstrap/cache`, rode:

```powershell
docker compose exec -u root php sh -lc 'chown -R www-data:www-data storage bootstrap/cache && chmod -R ug+rwX storage bootstrap/cache'
```

Depois repita o comando que falhou. **Não** execute `php artisan key:generate` toda vez que iniciar: a chave da aplicação deve ser mantida.

## 4. Testar e entrar

- Web: http://localhost:9010
- Health da API: http://localhost:8182/api/health → `{"status":"ok"}`
- Documentação da API (Scramble): http://localhost:8182/docs/api
- phpMyAdmin: http://localhost:8184 — servidor `mysql`, usuário `contabil`, senha `contabil_local_somente`.

O usuário do login do sistema é o **e-mail** configurado em `SEED_ADMIN_EMAIL`, e a senha é a de `SEED_ADMIN_PASSWORD`.

Teste opcional no PowerShell:

```powershell
Invoke-RestMethod http://localhost:8182/api/health
docker compose exec php php artisan route:list --path=api
docker compose exec php php artisan test
```

## 5. Próximos usos

Execute na pasta raiz:

```powershell
docker compose up -d
docker compose ps
```

Para interromper **sem apagar o banco**:

```powershell
docker compose down
```

**Não use `docker compose down -v`: isso removerá o volume MySQL e os dados cadastrados.**

Após editar controllers, services, models ou rotas PHP, normalmente o volume montado atualiza o contêiner automaticamente, sem recompilar. Se houver cache de configuração:

```powershell
docker compose exec php php artisan optimize:clear
```

Se você mudar migrations:

```powershell
docker compose exec php php artisan migrate
```

## 6. Flutter Android

No Windows, instale Flutter SDK e Android Studio. Rode `flutter doctor` e resolva os avisos de Android SDK. Abra o Android Studio > Device Manager e crie/inicie um emulador. O projeto contém `lib/`, mas ainda não contém a pasta Android; gere-a:

```powershell
cd .\apps\mobile
flutter create --platforms=android .
flutter pub get
flutter doctor
flutter devices
```

O Android costuma bloquear tráfego HTTP por padrão. Para teste local, abra `android/app/src/main/AndroidManifest.xml`, localize `<application` e inclua `android:usesCleartextTraffic="true"` como atributo nessa tag. **Somente para desenvolvimento**. Em produção, remova a exceção e publique a API com HTTPS.

Emulador Android (10.0.2.2 aponta para o computador):

```powershell
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8182/api
```

Em celular físico, utilize `http://SEU_IP_LOCAL:8182/api` no argumento, mantenha PC e telefone na mesma rede e permita a comunicação no firewall. Descubra o IP com `ipconfig` no Windows. O comando deve ser executado dentro de `apps/mobile`.

## 7. Diagnóstico de problemas

- **docker não reconhecido:** instale e abra Docker Desktop; reinicie terminal.
- **cannot connect to Docker daemon / erro WSL 2:** abra Docker Desktop, habilite WSL 2, execute `wsl --update`, `wsl --shutdown` em PowerShell administrador, depois reinicie o Docker.
- **porta ocupada:** veja `docker compose ps` e `netstat -ano | findstr :8182` (ou 9010/8184/33106). Libere a porta ou ajuste o mapeamento.
- **500 / Laravel não sobe:** `docker compose logs -f php nginx`.
- **web não abre:** `docker compose logs -f web`.
- **Access denied MySQL / SQLSTATE:** confira `DB_HOST=mysql`, usuário e senha em `.env` e no Compose; confira `docker compose logs mysql`.
- **login inválido:** confira `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`, execute `docker compose exec php php artisan db:seed --force` após ajustar a senha.
- **CORS no navegador:** confirme origem `http://localhost:9010` e `VITE_API_BASE_URL=http://localhost:8182/api`, depois `docker compose exec php php artisan optimize:clear`.
- **Flutter não reconhecido:** configure o `PATH` para o `bin` do Flutter SDK.
- **Flutter não acessa API:** no emulador use `10.0.2.2`, não `localhost`; verifique a permissão temporária HTTP no manifest, e teste antes a API no navegador do PC.

## 8. Observação fiscal

A soma de ICMS e IPI de notas de entrada é um consolidado didático, não um valor fiscal legal de imposto a pagar. Não utilizar em fechamento tributário real sem desenvolver as regras aplicáveis.
