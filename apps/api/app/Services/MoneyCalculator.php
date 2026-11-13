<?php
namespace App\Services;

use Illuminate\Validation\ValidationException;

/** Aritmética monetária com inteiros: evita discrepâncias de ponto flutuante. */
class MoneyCalculator {
    /** Recebe número decimal com até $scale casas e retorna inteiro escalonado. */
    public function parse($value, int $scale = 2): int {
        $s = trim((string) $value);
        if (!preg_match('/^\d+(?:\.\d{1,'.$scale.'})?$/', $s)) {
            throw ValidationException::withMessages(['values'=>'Use números positivos com no máximo '.$scale.' casas decimais, separados por ponto.']);
        }
        [$whole,$fraction] = array_pad(explode('.', $s, 2), 2, '');
        $factor = 10 ** $scale;
        if (strlen($whole) > 12) {
            throw ValidationException::withMessages(['values'=>'Valor informado muito grande.']);
        }
        return ((int)$whole) * $factor + (int)str_pad($fraction, $scale, '0');
    }
    /** Multiplicação com arredondamento half-up sem produto intermediário grande. */
    public function mulDiv(int $amount, int $multiplier, int $divisor): int {
        return intdiv($amount, $divisor)*$multiplier + intdiv(($amount % $divisor)*$multiplier + intdiv($divisor, 2), $divisor);
    }
    public function money(int $cents): string {
        return sprintf('%d.%02d', intdiv($cents,100), $cents % 100);
    }
    public function decimal(int $scaled, int $scale): string {
        $factor=10**$scale;
        return sprintf('%d.%0'.$scale.'d', intdiv($scaled, $factor), $scaled%$factor);
    }
    /** Distribui desconto em centavos pelo maior resto, sem perder centavos. */
    public function distributeDiscount(array $grossCents, int $discountCents): array {
        $grossTotal = array_sum($grossCents);
        if ($grossTotal <= 0 || $discountCents < 0 || $discountCents > $grossTotal) {
            throw ValidationException::withMessages(['discount_total'=>'Desconto deve estar entre zero e a soma bruta dos itens.']);
        }
        $portions = [];
        $used = 0;
        foreach ($grossCents as $index => $gross) {
            $product = $gross * $discountCents;
            $whole = intdiv($product, $grossTotal);
            $portions[$index] = ['discount'=>$whole, 'remainder'=>$product % $grossTotal, 'index'=>$index];
            $used += $whole;
        }
        $ranked = array_keys($portions);
        usort($ranked, fn ($a,$b) => ($portions[$b]['remainder'] <=> $portions[$a]['remainder']) ?: ($a <=> $b));
        for ($i = 0; $i < $discountCents - $used; $i++) {
            $portions[$ranked[$i]]['discount']++;
        }
        return array_map(fn ($part) => $part['discount'], $portions);
    }

    /**
     * Só é possível inferir desconto pela BC ICMS se ela corresponde a
     * (produto menos desconto) + IPI, sem outros componentes.
     * Recebe/devolve valores inteiros de centavos; alíquota escalonada em 4 casas.
     */
    public function discountFromIcmsBase(int $gross, int $icmsBase, int $ipiRate, int $index = 0): array {
        if ($gross <= 0 || $icmsBase <= 0) {
            throw ValidationException::withMessages(["items.$index.icms_base" => 'Informe uma base ICMS válida para calcular o desconto automaticamente.']);
        }
        $denominator = 1000000 + $ipiRate;
        $ipiBase = $this->mulDiv($icmsBase, 1000000, $denominator);
        $ipi = $this->mulDiv($ipiBase, $ipiRate, 1000000);
        $discount = $gross - $ipiBase;
        if ($discount < 0 || $discount > $gross || $ipiBase + $ipi !== $icmsBase) {
            throw ValidationException::withMessages(["items.$index.icms_base" => 'A BC ICMS não equivale a líquido + IPI. Desative o cálculo automático neste produto ou confira o XML.']);
        }
        return ['discount' => $discount, 'ipi_base' => $ipiBase, 'ipi' => $ipi];
    }

    /** Retorna resultados canônicos de todos os itens e do cabeçalho. */
    public function calculate(array $items, bool $deductIcms = false, bool $addIpiToTotal = true): array {
        $totals=['icms_base'=>0,'icms_total'=>0,'ipi_total'=>0,'gross_total'=>0,'discount_total'=>0,'net_total'=>0];
        $mapped=[];
        foreach ($items as $index=>$item) {
            $qty=$this->parse($item['quantity'], 3);
            $unit=$this->parse($item['unit_price'],4);
            $base=$this->parse($item['icms_base']);
            $icmsRate=$this->parse($item['icms_rate'],4);
            $ipiRate=$this->parse($item['ipi_rate'],4);
            $gross=$this->mulDiv($unit,$qty,100000);
            $mode = $item['discount_mode'] ?? 'manual';
            if ($mode === 'icms_includes_ipi') {
                if (isset($item['ipi_base']) && $item['ipi_base'] !== '') {
                    throw ValidationException::withMessages(["items.$index.ipi_base" => 'Não informe BC IPI manual quando o desconto é automático.']);
                }
                $inferred = $this->discountFromIcmsBase($gross, $base, $ipiRate, $index);
                $discount = $inferred['discount'];
                $ipiBase = $inferred['ipi_base'];
            } elseif ($mode === 'manual') {
                $discount = $this->parse($item['discount_amount'] ?? '0');
                if ($discount > $gross) {
                    throw ValidationException::withMessages(["items.$index.discount_amount"=>'O desconto do item não pode superar seu valor bruto.']);
                }
                $ipiBase = array_key_exists('ipi_base', $item) && $item['ipi_base'] !== null && $item['ipi_base'] !== ''
                    ? $this->parse($item['ipi_base'])
                    : $gross - $discount;
            } else {
                throw ValidationException::withMessages(["items.$index.discount_mode" => 'Modo de desconto inválido.']);
            }
            // Base ICMS pode incluir parcelas como frete/despesas e exceder o valor bruto do item.
            // A validação concentra-se na consistência da soma do cabeçalho e dos itens.
            $icms=$this->mulDiv($base,$icmsRate,1000000);
            $ipi=$this->mulDiv($ipiBase,$ipiRate,1000000);
            $net=$gross - $discount + ($addIpiToTotal ? $ipi : 0) - ($deductIcms ? $icms : 0);
            if ($net<0) {
                throw ValidationException::withMessages(["items.$index.icms_rate"=>'O abatimento de ICMS deixaria o total do item negativo.']);
            }
            $mapped[]=[
                'sku'=>$item['sku'],'description'=>$item['description'],'cfop'=>$item['cfop'],
                'quantity'=>$this->decimal($qty,3), 'unit_price'=>$this->decimal($unit,4),
                'gross_total'=>$this->money($gross), 'discount_amount'=>$this->money($discount), 'ipi_base'=>$this->money($ipiBase), 'icms_base'=>$this->money($base),
                'icms_rate'=>$this->decimal($icmsRate,4), 'icms_amount'=>$this->money($icms),
                'ipi_rate'=>$this->decimal($ipiRate,4),'ipi_amount'=>$this->money($ipi),'net_total'=>$this->money($net),
            ];
            $totals['icms_base']+=$base;$totals['icms_total']+=$icms;
            $totals['ipi_total']+=$ipi;$totals['gross_total']+=$gross;$totals['discount_total']+=$discount;$totals['net_total']+=$net;
        }
        return ['items'=>$mapped, 'totals'=>array_map($this->money(...),$totals)];
    }
}
