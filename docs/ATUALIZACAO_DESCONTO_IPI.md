# Atualização: desconto proporcional e cálculo de IPI por item

## O que mudou

- No lançamento de notas, cada produto tem **Desconto do item (R$)** e **BC IPI diferente (opcional)**.
- O padrão de **BC IPI = total bruto do item − desconto do item**. É possível usar outra base quando a NF-e assim indicar.
- O IPI de cada produto é calculado separadamente: **BC IPI × alíquota de IPI**, arredondado a centavos.
- O líquido do item considera bruto − desconto + IPI (quando somado por fora), sem deduzir ICMS por padrão.
- O botão **Calcular e distribuir desconto por item** usa o total bruto dos itens, o IPI total *informado conforme a nota* e o total líquido *informado conforme a nota*, para inferir um desconto total: `bruto + IPI − líquido` (na composição padrão). Distribui proporcionalmente pelos valores brutos. Os centavos restantes são atribuídos pelos maiores resíduos, garantindo soma exata.
- O valor unitário aceita até quatro casas decimais, como no XML da NF-e. A API valida os mesmos totais calculados pela interface.

## Aplicar em uma instalação já existente (Windows + Docker)

1. Faça backup da sua pasta do projeto antes de substituir arquivos. Se houver lançamentos importantes, faça também backup do MySQL.
2. Extraia o ZIP de correção **dentro da pasta `sistema_contabil_unificado`**, preservando a estrutura `apps/` e substituindo apenas os arquivos com o mesmo nome.
3. Na pasta do projeto, abra o terminal PowerShell e execute:

   ```powershell
   docker compose up -d
   docker compose exec php php artisan migrate --force
   docker compose exec php php artisan optimize:clear
   docker compose restart php nginx web
   ```

4. Acesse `http://localhost:9010`, pressione `Ctrl + Shift + R`.
5. Na tela Lançamento de Notas, preencha os produtos, os totais da nota **conforme o documento** (Bruto, IPI e Líquido) e clique em **Calcular e distribuir desconto por item**.
6. Verifique o desconto e o IPI de cada item. Se houver frete, seguro, outras despesas, ICMS-ST ou outro tratamento tributário, não aceite o rateio como verdade sem conferir o XML. Use a BC IPI diferente quando aplicável.
7. Confira todos os valores em verde e só então clique em **Gravar nota**.

**Atenção:** o botão **Copiar totais calculados** substitui os valores do cabeçalho pela soma do que está digitado nos itens. Não o use para preencher os totais originais da nota **antes** do rateio; neste caso, informe os totais reais da NF-e.

**Nota:** a migration acrescenta campos e aumenta a precisão de `unit_price` para quatro casas decimais. Não apaga as notas existentes. Os registros antigos passam a ter desconto zero e preservam a base de IPI correspondente ao bruto anterior.

## Conferência: NF-e 384981 (exemplo real recebido)

| SKU | Bruto | Desconto | BC IPI | % IPI | IPI |
|---|---:|---:|---:|---:|---:|
| 607169 | 670,78 | 8,79 | 661,99 | 0% | 0,00 |
| 606964 | 34,31 | 0,45 | 33,86 | 5,20% | 1,76 |
| 606926 | 47,91 | 0,63 | 47,28 | 5,20% | 2,46 |
| 606674 | 6,03 | 0,08 | 5,95 | 3,25% | 0,19 |
| **Total** | **759,03** | **9,95** | | | **4,41** |

Nota líquida: `759,03 − 9,95 + 4,41 = 753,49`. ICMS próprio não é subtraído nesse exemplo.

## Verificar e publicar

```powershell
docker compose exec php php artisan test
# Com o sistema validado localmente:
git add .
git commit -m "Implementa desconto proporcional e BC IPI por item"
git push origin main
```

Não publique o `.env` nem arquivos com dados reais de clientes.
