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
}
