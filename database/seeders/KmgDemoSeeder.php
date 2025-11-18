<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

use App\Models\Order;
use App\Models\Invoice;
use App\Models\OrderConfirmation;
use App\Models\OrderMatch;
use App\Models\OrderMismatch;
use App\Models\User;

class KmgDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo user if none exists
        if (!User::exists()) {
            User::create([
                'name' => 'Demo User',
                'email' => 'demo@kmg.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        $suppliers = [
            'BAX', 'HAVECO', 'HE DESIGN', 'KMG ZUMBROCK GMBH', 
            'FURNITURE SUPPLIERS B.V.', 'INTERIOR SOLUTIONS', 'IKEA SUPPLIER',
            'JYSK', 'Leen Bakker', 'EURO FURNITURE', 'MODERN LIVING B.V.',
            'DESIGN PARTNERS', 'FURNITURE EXPRESS', 'HOME DECOR B.V.'
        ];
        $customerNames = [
            'KMG Zumbrock', 'KMG ZUMBROCK GMBH', 'IMEA SUPPLIER',
            'DREAM RETAIL', 'Leen Bakker Stores', 'EURO FURNITURE CHAIN',
            'MODERN LIVING CUSTOMERS', 'DESIGN PARTNERS CLIENT',
            'FURNITURE EXPRESS BUYER', 'HOME DECOR RETAIL'
        ];
        $mismatchCodes = ['missing_lines', 'price_delta', 'unknown_supplier', 'doc_quality', 'quantity_mismatch', 'date_mismatch'];
        $matchStrategies = ['exact', 'fuzzy', 'heuristic', 'fallback'];
        $matchResults = ['matched', 'mismatched', 'partial', 'unmatched', 'needs_review'];

        // Create 30 orders with confirmations
        for ($i = 1; $i <= 30; $i++) {
            $orderNumber = 'ORD-' . str_pad((string) $i, 5, '0', STR_PAD_LEFT);
            $supplier = $suppliers[array_rand($suppliers)];
            $customerName = $customerNames[array_rand($customerNames)];
            
            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'supplier_code' => 'SUP-' . str_pad((string) rand(1, 100), 3, '0', STR_PAD_LEFT),
                'supplier' => $supplier,
                'customer_name' => $customerName,
                'customer_email' => strtolower(str_replace(' ', '.', $customerName)) . '@example.com',
                'order_date' => Carbon::now()->subDays(rand(1, 30)),
                'required_by' => Carbon::now()->addDays(rand(3, 15)),
                'expected_delivery_date' => Carbon::now()->addDays(rand(5, 20)),
                'currency' => 'EUR',
                'subtotal_amount' => fake()->randomFloat(2, 500, 5000),
                'tax_amount' => fake()->randomFloat(2, 50, 500),
                'total_amount' => fake()->randomFloat(2, 550, 5500),
                'payment_terms' => '30 days',
                'incoterm' => 'FOB',
                'shipping_method' => 'Standard',
                'status' => ['open', 'in_progress', 'closed'][array_rand(['open', 'in_progress', 'closed'])],
                'source' => 'web',
                'channel' => 'direct',
                'po_number' => 'PO-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'notes' => fake()->sentence(),
            ]);

            // Create order confirmation
            $confirmation = OrderConfirmation::create([
                'order_id' => $order->id,
                'supplier' => $supplier,
                'received_at' => Carbon::now()->subDays(rand(0, 10)),
                'confidence' => fake()->randomFloat(2, 0.60, 1.00),
                'status' => ['pending', 'processed', 'failed'][array_rand(['pending', 'processed', 'failed'])],
                'payload' => [
                    'raw_data' => "Confirmation data for order {$orderNumber}",
                    'extracted_fields' => [
                        'supplier' => $supplier,
                        'order_number' => $orderNumber,
                        'total_amount' => $order->total_amount,
                    ]
                ],
                'notes' => fake()->sentence(),
                'created_by' => User::first()->id,
            ]);

            // Always create an OrderMatch record (which holds the full comparison payload)
            $result = $matchResults[array_rand($matchResults)];
            
            $orderMatch = OrderMatch::create([
                'order_confirmation_id' => $confirmation->id,
                'order_id' => $order->id,
                'po_number' => $order->po_number,
                'customer' => $customerName,
                'status' => ['pending', 'processed', 'failed'][array_rand(['pending', 'processed', 'failed'])],
                'payload' => $this->generateOrderMatchPayload($order, $result, $supplier, $customerName),
                'strategy' => $matchStrategies[array_rand($matchStrategies)],
                'score' => fake()->randomFloat(2, 0.70, 1.00),
                'result' => $result,
                'matched_at' => Carbon::now()->subHours(rand(1, 72)),
                'notes' => fake()->sentence(),
                'author_id' => User::first()->id,
            ]);

            // If the result indicates mismatches, create OrderMismatch records linked to the OrderMatch
            if (in_array($result, ['mismatched', 'partial', 'unmatched', 'needs_review'])) {
                $mismatchCode = $mismatchCodes[array_rand($mismatchCodes)];
                $severity = ['low', 'medium', 'high', 'critical'][array_rand(['low', 'medium', 'high', 'critical'])];
                
                OrderMismatch::create([
                    'order_match_id' => $orderMatch->id,
                    'order_confirmation_id' => $confirmation->id,
                    'order_id' => $order->id,
                    'code' => $mismatchCode,
                    'severity' => $severity,
                    'status' => ['open', 'in_progress', 'resolved'][array_rand(['open', 'in_progress', 'resolved'])],
                    'message' => $this->getMismatchMessage($mismatchCode, $orderNumber),
                    'details' => [
                        'mismatch_type' => $mismatchCode,
                        'severity_level' => $severity,
                        'order_reference' => $orderNumber,
                        'order_match_id' => $orderMatch->id,
                    ],
                    'created_by' => User::first()->id,
                ]);
            }
        }

        // Create some invoices with realistic invoice numbers
        for ($i = 1; $i <= 25; $i++) {
        $order = Order::inRandomOrder()->first();
            $supplier = $suppliers[array_rand($suppliers)];
            
            // Generate realistic invoice numbers based on supplier
            $invoiceNumber = $this->generateInvoiceNumber($supplier, $i);
            
            $subtotal = fake()->randomFloat(2, 400, 4000);
            $tax = $subtotal * 0.21; // 21% VAT
            $total = $subtotal + $tax;

            Invoice::create([
                'invoice_number' => $invoiceNumber,
                'order_id' => $order->id,
                'supplier' => $supplier,
                'issued_at' => Carbon::now()->subDays(rand(1, 20)),
                'due_at' => Carbon::now()->addDays(rand(15, 30)),
            'subtotal_amount' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'currency' => 'EUR',
                'status' => ['pending', 'approved', 'paid'][array_rand(['pending', 'approved', 'paid'])],
            ]);
        }

        // Create invoice demo data
        $this->createInvoiceDemoData();

        Model::reguard();
    }

    private function createInvoiceDemoData(): void
    {
        $suppliers = [
            'BAX', 'HAVECO', 'HE DESIGN', 'KMG ZUMBROCK GMBH', 
            'FURNITURE SUPPLIERS B.V.', 'INTERIOR SOLUTIONS',
             'Leen Bakker', 'EURO FURNITURE', 'MODERN LIVING B.V.',
            'DESIGN PARTNERS', 'FURNITURE EXPRESS', 'HOME DECOR B.V.'
        ];
        
        // Create some invoices with realistic data
        $invoices = [];
        for ($i = 1; $i <= 15; $i++) {
            $supplier = $suppliers[array_rand($suppliers)];
            $invoiceNumber = $this->generateInvoiceNumber($supplier, $i + 25); // Offset to avoid duplicates
            
            $invoices[] = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'order_id' => Order::inRandomOrder()->first()?->id,
                'supplier' => $supplier,
                'issued_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'due_at' => fake()->dateTimeBetween('now', '+30 days'),
                'subtotal_amount' => fake()->randomFloat(2, 1000, 5000),
                'tax_amount' => fake()->randomFloat(2, 100, 500),
                'total_amount' => fake()->randomFloat(2, 1100, 5500),
                'currency' => 'EUR',
                'status' => fake()->randomElement(['draft', 'pending', 'approved', 'paid']),
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }

        // Create invoice matches and mismatches
        foreach ($invoices as $invoice) {
            $result = fake()->randomElement(['matched', 'mismatched', 'partial']);
            $confidenceScore = fake()->randomFloat(4, 0.5, 1.0);
            
            // Always create an InvoiceMatch record (this contains the comparison data)
            $invoiceMatch = \App\Models\InvoiceMatch::create([
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'supplier' => $invoice->supplier,
                'status' => fake()->randomElement(['pending', 'processing', 'matched', 'completed']),
                'result' => $result,
                'matched_at' => fake()->dateTimeBetween('-20 days', 'now'),
                'confidence_score' => $confidenceScore,
                'payload' => $this->generateInvoiceMatchPayload($invoice, $result, $confidenceScore),
                'author_id' => 1,
                'updated_by' => 1,
            ]);

            // If the result is mismatched, also create InvoiceMismatch records
            if ($invoiceMatch->result === 'mismatched') {
                \App\Models\InvoiceMismatch::create([
                    'invoice_id' => $invoice->id,
                    'invoice_match_id' => $invoiceMatch->id, // Link to the comparison data
                    'mismatch_type' => fake()->randomElement(['price', 'quantity', 'item', 'date', 'other']),
                    'description' => $this->getInvoiceMismatchMessage(fake()->randomElement(['price_delta', 'quantity_mismatch', 'item_missing', 'date_mismatch']), $invoice->invoice_number),
                    'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
                    'status' => fake()->randomElement(['open', 'investigating', 'resolved']),
                    'details' => [
                        'expected_amount' => $invoice->total_amount,
                        'actual_amount' => $invoice->total_amount + fake()->randomFloat(2, -100, 100),
                        'discrepancy_reason' => fake()->sentence(),
                    ],
                    'reported_by' => 1,
                    'assigned_to' => fake()->boolean() ? 1 : null,
                    'resolved_at' => fake()->boolean(20) ? fake()->dateTimeBetween('-10 days', 'now') : null,
                    'resolution_notes' => fake()->boolean(20) ? fake()->sentence() : null,
                ]);
            }
        }
    }

    private function getMismatchMessage(string $code, string $orderNumber): string
    {
        return match ($code) {
            'missing_lines' => "Order {$orderNumber} is missing line items in the confirmation",
            'price_delta' => "Price discrepancy detected in order {$orderNumber}",
            'unknown_supplier' => "Supplier not recognized for order {$orderNumber}",
            'doc_quality' => "Document quality issues found for order {$orderNumber}",
            'quantity_mismatch' => "Quantity mismatch in order {$orderNumber}",
            'date_mismatch' => "Date discrepancy in order {$orderNumber}",
            default => "Mismatch detected in order {$orderNumber}",
        };
    }

    private function getInvoiceMismatchMessage(string $code, string $invoiceNumber): string
    {
        return match ($code) {
            'price_delta' => "Price discrepancy detected in invoice {$invoiceNumber}",
            'quantity_mismatch' => "Quantity mismatch in invoice {$invoiceNumber}",
            'item_missing' => "Missing items in invoice {$invoiceNumber}",
            'date_mismatch' => "Date discrepancy in invoice {$invoiceNumber}",
            default => "Mismatch detected in invoice {$invoiceNumber}",
        };
    }

    private function generateInvoiceNumber(string $supplier, int $index): string
    {
        $supplierPrefixes = [
            'BAX' => 'BAX',
            'HAVECO' => 'HAV',
            'HE DESIGN' => 'HED',
            'KMG ZUMBROCK GMBH' => 'KMG',
            'FURNITURE SUPPLIERS B.V.' => 'FSB',
            'INTERIOR SOLUTIONS' => 'IS',
            'IKEA SUPPLIER' => 'IKEA',
            'JYSK' => 'JYSK',
            'Leen Bakker' => 'LB',
            'EURO FURNITURE' => 'EF',
            'MODERN LIVING B.V.' => 'ML',
            'DESIGN PARTNERS' => 'DP',
            'FURNITURE EXPRESS' => 'FE',
            'HOME DECOR B.V.' => 'HD'
        ];

        $prefix = $supplierPrefixes[$supplier] ?? 'INV';
        $year = date('Y');
        $month = str_pad((string) rand(1, 12), 2, '0', STR_PAD_LEFT);
        $number = str_pad((string) ($index + rand(1000, 9999)), 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}{$month}-{$number}";
    }

    private function generateInvoiceMatchPayload($invoice, string $result, float $confidenceScore): array
    {
        $furnitureItems = [
            ['name' => 'CHAIR MODEL A', 'type' => 'CHAIR', 'material' => 'LEATHER'],
            ['name' => 'TABLE MODEL B', 'type' => 'TABLE', 'material' => 'WOOD'],
            ['name' => 'SOFA MODEL C', 'type' => 'SOFA', 'material' => 'FABRIC'],
            ['name' => 'LAMP MODEL D', 'type' => 'LAMP', 'material' => 'METAL'],
            ['name' => 'CABINET MODEL E', 'type' => 'CABINET', 'material' => 'WOOD'],
            ['name' => 'DESK MODEL F', 'type' => 'DESK', 'material' => 'GLASS'],
        ];

        $item = $furnitureItems[array_rand($furnitureItems)];
        $qty = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 100, 800);
        $amount = $qty * $unitPrice;

        $pdfItems = [
            [
                'reference' => 'ITEM-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
                'model' => $item['name'],
                'color' => fake()->randomElement(['BLACK', 'WHITE', 'BROWN', 'GRAY', 'BLUE']),
                'material' => $item['material'],
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'amount' => $amount,
                'delivery_week' => fake()->numberBetween(40, 52) . '-2025'
            ]
        ];

        $excelItems = [
            [
                'line_id' => '1',
                'order_id' => 'PO-' . str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'reference' => $pdfItems[0]['reference'],
                'description' => $item['name'] . ' ' . $pdfItems[0]['color'] . ' ' . $item['material'],
                'qty' => $result === 'mismatched' ? $qty + rand(1, 2) : $qty, // Create mismatch
                'unit_price' => $unitPrice,
                'delivery_week' => $pdfItems[0]['delivery_week']
            ]
        ];

        $pairs = [
            [
                'excel_line_id' => '1',
                'pdf_ref' => $pdfItems[0]['reference'],
                'final_decision' => $result === 'matched' ? 'MATCH' : 'MISMATCH',
                'status' => $result === 'matched' ? 'MATCH' : 'MISMATCH',
                'score' => $confidenceScore
            ]
        ];

        return [
            'results' => [
                'pdf_header' => [
                    'po_number' => 'PO-' . str_pad((string) rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'customer' => fake()->randomElement(['KMG Zumbrock', 'KMG ZUMBROCK GMBH', 'IKEA SUPPLIER']),
                    'confirm_date' => fake()->date('Y-m-d', '-10 days'),
                    'order_date' => fake()->date('Y-m-d', '-15 days'),
                    'delivery_week' => fake()->numberBetween(40, 52) . '-2025',
                    'commission' => 'REF-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'email' => strtolower(str_replace(' ', '.', $invoice->supplier)) . '@supplier.com'
                ],
                'pdf_groups' => [
                    [
                        'main_reference' => 'REF-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
                        'total_amount' => $amount,
                        'total_qty' => $qty,
                        'items' => $pdfItems
                    ]
                ],
                'match_results' => [
                    [
                        'match_type' => '1-to-1',
                        'pdf_reference' => 'REF-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT),
                        'excel_items' => $excelItems,
                        'pairs' => $pairs,
                        'unmatched_excel_ids' => $result === 'mismatched' ? ['2', '3'] : [],
                        'unmatched_pdf' => $result === 'mismatched' ? [$pdfItems[0]['reference']] : []
                    ]
                ],
                'structural_mismatches' => $result === 'mismatched' ? [
                    [
                        'message' => 'Quantity mismatch detected for ' . $pdfItems[0]['reference'],
                        'type' => 'quantity_mismatch',
                        'pdf_qty' => $qty,
                        'excel_qty' => $excelItems[0]['qty']
                    ]
                ] : []
            ]
        ];
    }

    private function generateOrderMatchPayload($order, string $result, string $supplier, string $customerName): array
    {
        $furnitureItems = [
            ['name' => 'CHAIR MODEL A', 'type' => 'CHAIR', 'material' => 'LEATHER'],
            ['name' => 'TABLE MODEL B', 'type' => 'TABLE', 'material' => 'WOOD'],
            ['name' => 'SOFA MODEL C', 'type' => 'SOFA', 'material' => 'FABRIC'],
            ['name' => 'LAMP MODEL D', 'type' => 'LAMP', 'material' => 'METAL'],
            ['name' => 'CABINET MODEL E', 'type' => 'CABINET', 'material' => 'WOOD'],
            ['name' => 'DESK MODEL F', 'type' => 'DESK', 'material' => 'GLASS'],
        ];

        $item = $furnitureItems[array_rand($furnitureItems)];
        $qty = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 100, 800);
        $amount = $qty * $unitPrice;
        $reference = 'ITEM-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);
        $commission = 'REF-' . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT);

        $pdfItems = [
            [
                'reference' => $reference,
                'model' => $item['name'],
                'color' => fake()->randomElement(['BLACK', 'WHITE', 'BROWN', 'GRAY', 'BLUE']),
                'material' => $item['material'],
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'amount' => $amount,
                'delivery_week' => fake()->numberBetween(40, 52) . '-2025'
            ]
        ];

        $excelItems = [
            [
                'line_id' => '1',
                'order_id' => $order->po_number,
                'reference' => $reference,
                'description' => $item['name'] . ' ' . $pdfItems[0]['color'] . ' ' . $item['material'],
                'qty' => $result === 'matched' ? $qty : $qty + rand(1, 2), // Create mismatch for non-matched
                'unit_price' => $unitPrice,
                'delivery_week' => $pdfItems[0]['delivery_week']
            ]
        ];

        $pairs = [
            [
                'excel_line_id' => '1',
                'pdf_ref' => $reference,
                'final_decision' => $result === 'matched' ? 'MATCH' : 'MISMATCH',
                'status' => $result === 'matched' ? 'MATCH' : 'MISMATCH',
                'score' => fake()->randomFloat(2, 0.70, 1.00)
            ]
        ];

        return [
            'results' => [
                'pdf_header' => [
                    'po_number' => $order->po_number,
                    'customer' => $customerName,
                    'confirm_date' => fake()->date('Y-m-d', '-5 days'),
                    'order_date' => $order->order_date->format('Y-m-d'),
                    'delivery_week' => fake()->numberBetween(40, 52) . '-2025',
                    'commission' => $commission,
                    'email' => strtolower(str_replace(' ', '.', $supplier)) . '@supplier.com'
                ],
                'pdf_groups' => [
                    [
                        'main_reference' => $commission,
                        'total_amount' => $amount,
                        'total_qty' => $qty,
                        'items' => $pdfItems
                    ]
                ],
                'match_results' => [
                    [
                        'match_type' => '1-to-1',
                        'pdf_reference' => $commission,
                        'excel_items' => $excelItems,
                        'pairs' => $pairs,
                        'unmatched_excel_ids' => in_array($result, ['mismatched', 'partial', 'unmatched', 'needs_review']) ? ['2', '3'] : [],
                        'unmatched_pdf' => in_array($result, ['mismatched', 'partial', 'unmatched', 'needs_review']) ? [$reference] : []
                    ]
                ],
                'structural_mismatches' => in_array($result, ['mismatched', 'partial', 'unmatched', 'needs_review']) ? [
                    [
                        'message' => 'Quantity mismatch detected for ' . $reference,
                        'type' => 'quantity_mismatch',
                        'pdf_qty' => $qty,
                        'excel_qty' => $excelItems[0]['qty']
                    ]
                ] : []
            ]
        ];
    }
}
