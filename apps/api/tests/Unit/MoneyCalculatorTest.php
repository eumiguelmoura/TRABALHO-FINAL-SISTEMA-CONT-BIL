<?php
namespace Tests\Unit;
use App\Services\MoneyCalculator;
use PHPUnit\Framework\TestCase;
class MoneyCalculatorTest extends TestCase {
    public function test_three_units_and_taxes(): void {
        $calc=new MoneyCalculator();
        $result=$calc->calculate([['sku'=>'A','description'=>'Teste','cfop'=>'1102','quantity'=>'3','unit_price'=>'10.00','icms_base'=>'30.00','icms_rate'=>'12','ipi_rate'=>'5']]);
        $this->assertSame('30.00',$result['totals']['gross_total']);
        $this->assertSame('3.60',$result['totals']['icms_total']);
        $this->assertSame('1.50',$result['totals']['ipi_total']);
        $this->assertSame('31.50',$result['totals']['net_total']);
    }
    public function test_rounding_of_repeating_percent(): void {
        $calc=new MoneyCalculator();
        $r=$calc->calculate([['sku'=>'A','description'=>'Teste','cfop'=>'1102','quantity'=>'3','unit_price'=>'0.33','icms_base'=>'0.99','icms_rate'=>'17','ipi_rate'=>'7.25']]);
        $this->assertSame('0.17',$r['totals']['icms_total']);
        $this->assertSame('0.07',$r['totals']['ipi_total']);
        $this->assertSame('1.06',$r['totals']['net_total']);
    }
    public function test_icms_can_be_deducted_when_explicitly_selected(): void {
        $calc=new MoneyCalculator();
        $items=[['sku'=>'A','description'=>'Teste','cfop'=>'1102','quantity'=>'3','unit_price'=>'10.00','icms_base'=>'30.00','icms_rate'=>'12','ipi_rate'=>'5']];
        $this->assertSame('27.90',$calc->calculate($items,true,true)['totals']['net_total']);
        $this->assertSame('30.00',$calc->calculate($items,false,false)['totals']['net_total']);
    }
    public function test_proportional_discount_and_ipi_from_real_xml(): void {
        $math = new MoneyCalculator();
        $gross = [67078,3431,4791,603];
        $discounts = $math->distributeDiscount($gross,995);
        $this->assertSame([879,45,63,8], $discounts);
        $items = [
            ['sku'=>'607169','description'=>'Perfurador','cfop'=>'6106','quantity'=>'1','unit_price'=>'670.78','icms_base'=>'661.99','icms_rate'=>'12','ipi_rate'=>'0'],
            ['sku'=>'606964','description'=>'Broca 15','cfop'=>'6106','quantity'=>'1','unit_price'=>'34.31','icms_base'=>'35.62','icms_rate'=>'4','ipi_rate'=>'5.2'],
            ['sku'=>'606926','description'=>'Broca 20','cfop'=>'6106','quantity'=>'1','unit_price'=>'47.91','icms_base'=>'49.74','icms_rate'=>'4','ipi_rate'=>'5.2'],
            ['sku'=>'606674','description'=>'Extensão','cfop'=>'6106','quantity'=>'1','unit_price'=>'6.03','icms_base'=>'6.14','icms_rate'=>'4','ipi_rate'=>'3.25'],
        ];
        foreach ($items as $index => &$item) {
            $item['discount_amount'] = $math->money($discounts[$index]);
        }
        unset($item);
        $result = $math->calculate($items);
        $this->assertSame('9.95', $result['totals']['discount_total']);
        $this->assertSame('4.41', $result['totals']['ipi_total']);
        $this->assertSame('753.49', $result['totals']['net_total']);
        $this->assertSame('33.86', $result['items'][1]['ipi_base']);
        $this->assertSame('1.76', $result['items'][1]['ipi_amount']);
    }
    public function test_auto_discount_from_icms_base_for_each_xml_item(): void {
        $calc = new MoneyCalculator();
        $items = [
            ['sku'=>'607169','description'=>'Perfurador','cfop'=>'6106','quantity'=>'1','unit_price'=>'670.78','icms_base'=>'661.99','icms_rate'=>'12','ipi_rate'=>'0','discount_mode'=>'icms_includes_ipi'],
            ['sku'=>'606964','description'=>'Broca 15','cfop'=>'6106','quantity'=>'1','unit_price'=>'34.31','icms_base'=>'35.62','icms_rate'=>'4','ipi_rate'=>'5.2','discount_mode'=>'icms_includes_ipi'],
            ['sku'=>'606926','description'=>'Broca 20','cfop'=>'6106','quantity'=>'1','unit_price'=>'47.91','icms_base'=>'49.74','icms_rate'=>'4','ipi_rate'=>'5.2','discount_mode'=>'icms_includes_ipi'],
            ['sku'=>'606674','description'=>'Extensao','cfop'=>'6106','quantity'=>'1','unit_price'=>'6.03','icms_base'=>'6.14','icms_rate'=>'4','ipi_rate'=>'3.25','discount_mode'=>'icms_includes_ipi'],
        ];
        $result = $calc->calculate($items);
        $this->assertSame(['8.79','0.45','0.63','0.08'], array_column($result['items'], 'discount_amount'));
        $this->assertSame(['0.00','1.76','2.46','0.19'], array_column($result['items'], 'ipi_amount'));
        $this->assertSame('33.86', $result['items'][1]['ipi_base']);
        $this->assertSame('9.95', $result['totals']['discount_total']);
        $this->assertSame('4.41', $result['totals']['ipi_total']);
        $this->assertSame('753.49', $result['totals']['net_total']);
    }

    public function test_base_can_be_adjusted_without_changing_discount(): void {
        $calc = new MoneyCalculator();
        $items = [['sku'=>'A','description'=>'Teste','cfop'=>'6106','quantity'=>'1','unit_price'=>'100.00',
            'discount_amount'=>'10.00', 'ipi_base'=>'95.00', 'icms_base'=>'90.00','icms_rate'=>'0','ipi_rate'=>'10']];
        $result = $calc->calculate($items);
        $this->assertSame('9.50', $result['totals']['ipi_total']);
        $this->assertSame('99.50', $result['totals']['net_total']);
    }
}
