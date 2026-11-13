<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountingFlowTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'issue_date' => '2026-11-12', 'supplier' => 'Fornecedor Teste',
            'number' => '1001', 'city' => 'Guarapuava', 'state' => 'PR',
            'icms_base' => '30.00', 'icms_total' => '3.60',
            'ipi_total' => '1.50', 'gross_total' => '30.00', 'net_total' => '31.50',
            'items' => [[
                'sku' => 'SKU-001', 'description' => 'Produto', 'cfop' => '1102',
                'quantity' => '3', 'unit_price' => '10.00',
                'icms_base' => '30.00', 'icms_rate' => '12', 'ipi_rate' => '5',
            ]],
        ];
    }

    public function test_unauthenticated_cannot_list_invoices(): void
    {
        $this->getJson('/api/invoices')->assertUnauthorized();
    }

    public function test_invoice_can_be_created_with_consistent_totals(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $response = $this->postJson('/api/invoices', $this->payload())->assertCreated();
        $response->assertJsonPath('items.0.icms_amount', '3.60');
        $response->assertJsonPath('items.0.ipi_amount', '1.50');
        $response->assertJsonPath('items.0.net_total', '31.50');
        $response->assertJsonPath('deduct_icms', false);
        $response->assertJsonPath('add_ipi_to_total', true);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('invoice_items', 1);
    }

    public function test_invoice_can_keep_icms_undiscounted_and_ipi_included_in_gross(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $payload = $this->payload();
        $payload['add_ipi_to_total'] = false;
        $payload['deduct_icms'] = false;
        $payload['net_total'] = '30.00';
        $this->postJson('/api/invoices', $payload)->assertCreated()->assertJsonPath('net_total','30.00');
    }

    public function test_rejects_an_invalid_state(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $payload = $this->payload();
        $payload['state'] = 'XX';
        $this->postJson('/api/invoices', $payload)->assertUnprocessable()->assertJsonValidationErrors('state');
    }

    public function test_invoice_is_rejected_if_header_does_not_match_items(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $payload = $this->payload();
        $payload['icms_total'] = '3.59';
        $this->postJson('/api/invoices', $payload)->assertUnprocessable()->assertJsonValidationErrors('icms_total');
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_eligible_invoices_are_assessed_only_once(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $id = $this->postJson('/api/invoices', $this->payload())->assertCreated()->json('id');
        $data = ['name' => 'Entradas novembro', 'invoice_ids' => [$id]];
        $this->postJson('/api/tax-assessments', $data)->assertCreated()
            ->assertJsonPath('total_icms', '3.60')->assertJsonPath('total_ipi', '1.50');
        $this->getJson('/api/invoices?pending_only=1')->assertOk()->assertExactJson([]);
        $this->postJson('/api/tax-assessments', $data)->assertUnprocessable();
        $this->assertDatabaseCount('tax_assessments', 1);
    }

    public function test_users_cannot_access_each_others_invoices(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);
        $id = $this->postJson('/api/invoices', $this->payload())->assertCreated()->json('id');
        Sanctum::actingAs(User::factory()->create());
        $this->getJson("/api/invoices/{$id}")->assertNotFound();
        $this->postJson('/api/tax-assessments', ['name' => 'Indevida', 'invoice_ids' => [$id]])->assertUnprocessable();
    }
    public function test_saves_real_xml_ipi_after_discounts(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $payload = $this->payload();
        $payload['number'] = '384981';
        $payload['gross_total'] = '759.03';
        $payload['discount_total'] = '9.95';
        $payload['net_total'] = '753.49';
        $payload['ipi_total'] = '4.41';
        $payload['icms_base'] = '753.49';
        $payload['icms_total'] = '83.10';
        $payload['items'] = [
            ['sku'=>'607169','description'=>'Perfurador','cfop'=>'6106','quantity'=>'1','unit_price'=>'670.78',
                'discount_mode'=>'icms_includes_ipi','icms_base'=>'661.99','icms_rate'=>'12','ipi_rate'=>'0'],
            ['sku'=>'606964','description'=>'Broca 15','cfop'=>'6106','quantity'=>'1','unit_price'=>'34.31',
                'discount_mode'=>'icms_includes_ipi','icms_base'=>'35.62','icms_rate'=>'4','ipi_rate'=>'5.2'],
            ['sku'=>'606926','description'=>'Broca 20','cfop'=>'6106','quantity'=>'1','unit_price'=>'47.91',
                'discount_mode'=>'icms_includes_ipi','icms_base'=>'49.74','icms_rate'=>'4','ipi_rate'=>'5.2'],
            ['sku'=>'606674','description'=>'Extensão','cfop'=>'6106','quantity'=>'1','unit_price'=>'6.03',
                'discount_mode'=>'icms_includes_ipi','icms_base'=>'6.14','icms_rate'=>'4','ipi_rate'=>'3.25'],
        ];
        $this->postJson('/api/invoices',$payload)->assertCreated()
            ->assertJsonPath('items.1.ipi_base','33.86')
            ->assertJsonPath('items.1.ipi_amount','1.76')
            ->assertJsonPath('discount_total','9.95')
            ->assertJsonPath('ipi_total','4.41')
            ->assertJsonPath('net_total','753.49');
    }
}
