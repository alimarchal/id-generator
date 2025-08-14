<?php

use Alimarchal\IdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->idGenerator = new IdGenerator();
});

describe('ID Generation with Database Prefix', function () {
    it('generates unique ID with correct format', function () {
        $id = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');

        expect($id)
            ->toStartWith('INV-')
            ->toContain(now()->format('Ymd'))
            ->toEndWith('-0001');
    });

    it('increments serial number correctly', function () {
        // Generate and save first ID
        $firstId = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($firstId);

        // Generate second ID
        $secondId = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');

        expect($firstId)->toEndWith('-0001');
        expect($secondId)->toEndWith('-0002');
        expect($firstId)->not->toBe($secondId);
    });

    it('handles multiple records correctly', function () {
        $ids = [];

        // Generate 5 IDs
        for ($i = 1; $i <= 5; $i++) {
            $id = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');
            $ids[] = $id;
            createTestInvoice($id);
        }

        // Check all IDs are unique
        expect($ids)->toHaveCount(5);
        expect(array_unique($ids))->toHaveCount(5);

        // Check serial numbers increment correctly
        expect($ids[0])->toEndWith('-0001');
        expect($ids[1])->toEndWith('-0002');
        expect($ids[2])->toEndWith('-0003');
        expect($ids[3])->toEndWith('-0004');
        expect($ids[4])->toEndWith('-0005');
    });

    it('throws exception for invalid prefix type', function () {
        expect(fn() => $this->idGenerator->generate('nonexistent', 'test_invoices', 'invoice_no'))
            ->toThrow(Exception::class, "Prefix for type 'nonexistent' not found in id_prefixes table.");
    });
});

describe('ID Generation with Direct Prefix', function () {
    it('generates unique ID with custom prefix', function () {
        $id = $this->idGenerator->generateWithPrefix('TEST', 'test_orders', 'order_no');

        expect($id)
            ->toStartWith('TEST-')
            ->toContain(now()->format('Ymd'))
            ->toEndWith('-0001');
    });

    it('generates fallback ID on database error', function () {
        // Test with non-existent table should trigger fallback
        $id = $this->idGenerator->generateWithPrefix('TEST', 'nonexistent_table', 'column');

        expect($id)
            ->toStartWith('TEST-')
            ->toMatch('/^TEST-\d+-\d+$/');
    });
});

describe('ID Format Validation', function () {
    it('formats ID correctly', function () {
        $id = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');
        $today = now()->format('Ymd');

        expect($id)->toMatch("/^INV-{$today}-\d{4}$/");
    });

    it('has correct parts structure', function () {
        $id = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');
        $parts = explode('-', $id);

        expect($parts)->toHaveCount(3);
        expect($parts[0])->toBe('INV');
        expect($parts[1])->toHaveLength(8); // YYYYMMDD format
        expect($parts[2])->toHaveLength(4); // 4-digit serial
    });
});

describe('Concurrency Handling', function () {
    it('handles concurrent requests without conflicts', function () {
        $ids = [];

        // Simulate concurrent requests by generating multiple IDs rapidly
        for ($i = 0; $i < 10; $i++) {
            $id = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');
            $ids[] = $id;
            createTestInvoice($id);
        }

        // All IDs should be unique
        expect($ids)->toHaveCount(10);
        expect(array_unique($ids))->toHaveCount(10);
    });
});

describe('Different Document Types', function () {
    it('generates different prefixes for different types', function () {
        $invoiceId = $this->idGenerator->generate('invoice', 'test_invoices', 'invoice_no');
        $complaintId = $this->idGenerator->generate('complaint', 'test_invoices', 'invoice_no');
        $quotationId = $this->idGenerator->generate('quotation', 'test_invoices', 'invoice_no');

        expect($invoiceId)->toStartWith('INV-');
        expect($complaintId)->toStartWith('CMP-');
        expect($quotationId)->toStartWith('QTN-');

        expect($invoiceId)->not->toBe($complaintId);
        expect($complaintId)->not->toBe($quotationId);
    });
});