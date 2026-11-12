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
    /** Retorna resultados canônicos de todos os itens e do cabeçalho. */
    public function calculate(array $items, bool $deductIcms = false, bool $addIpiToTotal = true): array {
        $totals=['icms_base'=>0,'icms_total'=>0,'ipi_total'=>0,'gross_total'=>0,'net_total'=>0];
        $mapped=[];
        foreach ($items as $index=>$item) {
            $qty=$this->parse($item['quantity'], 3);
            $unit=$this->parse($item['unit_price']);
            $base=$this->parse($item['icms_base']);
            $icmsRate=$this->parse($item['icms_rate'],4);
            $ipiRate=$this->parse($item['ipi_rate'],4);
            $gross=$this->mulDiv($unit,$qty,1000);
            // Base ICMS pode incluir parcelas como frete/despesas e exceder o valor bruto do item.
            // A validação concentra-se na consistência da soma do cabeçalho e dos itens.
            $icms=$this->mulDiv($base,$icmsRate,1000000);
            $ipi=$this->mulDiv($gross,$ipiRate,1000000);
            $net=$gross + ($addIpiToTotal ? $ipi : 0) - ($deductIcms ? $icms : 0);
            if ($net<0) {
                throw ValidationException::withMessages(["items.$index.icms_rate"=>'O abatimento de ICMS deixaria o total do item negativo.']);
            }
            $mapped[]=[
                'sku'=>$item['sku'],'description'=>$item['description'],'cfop'=>$item['cfop'],
                'quantity'=>$this->decimal($qty,3), 'unit_price'=>$this->money($unit),
                'gross_total'=>$this->money($gross), 'icms_base'=>$this->money($base),
                'icms_rate'=>$this->decimal($icmsRate,4), 'icms_amount'=>$this->money($icms),
                'ipi_rate'=>$this->decimal($ipiRate,4),'ipi_amount'=>$this->money($ipi),'net_total'=>$this->money($net),
            ];
            $totals['icms_base']+=$base;$totals['icms_total']+=$icms;
            $totals['ipi_total']+=$ipi;$totals['gross_total']+=$gross;$totals['net_total']+=$net;
        }
        return ['items'=>$mapped, 'totals'=>array_map($this->money(...),$totals)];
    }
}
