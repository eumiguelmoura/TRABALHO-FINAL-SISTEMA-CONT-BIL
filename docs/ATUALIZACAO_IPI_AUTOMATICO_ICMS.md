# Correção: cálculo automático do desconto/BC IPI por produto

## Objetivo

O desconto **não precisa ser digitado** quando a nota segue a identidade:

    BC do ICMS do item = (valor bruto do item - desconto do item) + IPI do item

Isso ocorre no exemplo da NF-e nº 384981 (2026-11-12), mas não é regra para todas as operações.

O formulário web ativa por padrão a opção **"Calcular desconto e IPI automaticamente pela BC ICMS (IPI incluído na base)"** em cada **novo item**. Ao informar quantidade, valor unitário, BC ICMS e alíquota IPI, os valores são atualizados imediatamente. O desconto fica somente leitura.

Fórmulas para esta composição:

    BC IPI = arredondar [ BC ICMS / (1 + aliquota IPI / 100) ] para centavos
    IPI    = arredondar [ BC IPI * aliquota IPI / 100 ] para centavos
    Desconto = valor bruto do item - BC IPI

O Laravel valida/recalcula a mesma fórmula em centavos no momento de gravar: não depende apenas dos números calculados pela tela. O Flutter também oferece cálculo automático. As notas existentes são abertas com o modo manual para evitar alterações retroativas.

## Exemplo real: SKU 606964

- Valor bruto: 34,31
- BC ICMS: 35,62
- Aliquota IPI: 5,20%
- BC IPI: 33,86
- Desconto automático: 0,45
- IPI automático: **1,76**

A conferência com os quatro produtos do XML de exemplo produz descontos por item de 8,79; 0,45; 0,63; 0,08, IPI total de 4,41 e total da nota de 753,49.

## LIMITES IMPORTANTES

Não é matematicamente possível determinar sempre o desconto apenas com bruto, BC ICMS e alíquota IPI. O modo automático pressupõe que a BC ICMS **inclui o IPI e não inclui outras parcelas** além do valor líquido do produto. Por exemplo, quando a BC ICMS exclui IPI (ou inclui frete, seguro, outras despesas), o cálculo pode inferir desconto incorreto mesmo que a aritmética feche.

Se essa composição não se aplica:

1. Desative o modo automático naquele produto;
2. Utilize o botão de **rateio pelo total da nota**, se frete, seguro e demais parcelas permitirem; ou
3. Informe os valores reais do XML (o DANFE/PDF pode omitir bases ou descontos por item).

Para notas com outras composições, o campo de BC IPI manual continua disponível apenas quando o modo automático é desligado. O sistema rejeita o cálculo automático se a identidade `BC IPI + IPI = BC ICMS` não fechar nos centavos ou se gerar desconto negativo.

## Instalação no Windows

1. Faça backup da pasta `sistema_contabil_unificado`.
2. Extraia o ZIP **dentro dessa pasta**, preservando os caminhos `apps/...` e `docs/...`, e aceite a substituição dos arquivos.
3. No terminal do VS Code, a partir da pasta `sistema_contabil_unificado`, execute:

       docker compose up -d
       docker compose restart php web

4. No navegador, acesse `http://localhost:9010` e pressione `Ctrl+Shift+R`.
5. Abra **LANÇAMENTO DE NOTAS** > **Novo lançamento** e preencha a BC ICMS e o IPI% de cada item. O desconto deve aparecer automaticamente, sem clicar em distribuir descontos.

**Esta correção não altera a estrutura do banco nem exige migrations.** Não execute `docker compose down -v`, pois isso apagaria os volumes do MySQL.

## Testes realizados

- Cálculo do Laravel executado isoladamente em PHP, com os 4 itens do XML, aprovando os totais 4,41 e 753,49;
- Cálculo do Quasar executado em Node.js com os 4 itens e com o campo `auto_discount` de formulário, aprovando os mesmos valores;
- Validado tratamento de BC ICMS incompatível (erro explícito em vez de inventar desconto).

**Não foram executados aqui o navegador com Docker, o Laravel com MySQL ou o compilador Flutter.** Faça uma nota de teste antes de usar em lançamentos definitivos.
