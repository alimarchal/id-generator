<?php

use Illuminate\Support\Facades\DB;

describe('Helper Functions Existence', function () {
    it('has generateUniqueId function', function () {
        expect(function_exists('generateUniqueId'))->toBeTrue();
    });

    it('has generateUniqueIdWithPrefix function', function () {
        expect(function_exists('generateUniqueIdWithPrefix'))->toBeTrue();
    });
});

describe('generateUniqueId Helper Function', function () {
    it('generates unique ID with database prefix', function () {
        $id = generateUniqueId('invoice', 'test_invoices', 'invoice_no');

        expect($id)
            ->toStartWith('INV-')
            ->toContain(now()->format('Ymd'));
    });

    it('increments serial number correctly', function () {
        // Generate and save first ID
        $firstId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($firstId);

        // Generate second ID
        $secondId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');

        expect($firstId)->toEndWith('-0001');
        expect($secondId)->toEndWith('-0002');
    });

    it('works with different document types', function () {
        $invoiceId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        $complaintId = generateUniqueId('complaint', 'test_invoices', 'invoice_no');

        expect($invoiceId)->toStartWith('INV-');
        expect($complaintId)->toStartWith('CMP-');
        expect($invoiceId)->not->toBe($complaintId);
    });
});

describe('generateUniqueIdWithPrefix Helper Function', function () {
    it('generates unique ID with custom prefix', function () {
        $id = generateUniqueIdWithPrefix('ORDER', 'test_orders', 'order_no');

        expect($id)
            ->toStartWith('ORDER-')
            ->toContain(now()->format('Ymd'));
    });

    it('works with different prefixes', function () {
        $orderId = generateUniqueIdWithPrefix('ORDER', 'test_orders', 'order_no');
        $customId = generateUniqueIdWithPrefix('CUSTOM', 'test_orders', 'order_no');

        expect($orderId)->toStartWith('ORDER-');
        expect($customId)->toStartWith('CUSTOM-');
        expect($orderId)->not->toBe($customId);
    });

    it('increments correctly with prefix', function () {
        $firstId = generateUniqueIdWithPrefix('TEST', 'test_orders', 'order_no');
        createTestOrder($firstId);

        $secondId = generateUniqueIdWithPrefix('TEST', 'test_orders', 'order_no');

        expect($firstId)->toEndWith('-0001');
        expect($secondId)->toEndWith('-0002');
    });
});

describe('Helper Functions Integration', function () {
    it('both helper functions generate different IDs', function () {
        $invoiceId = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        $orderId = generateUniqueIdWithPrefix('ORDER', 'test_orders', 'order_no');

        expect($invoiceId)->not->toBe($orderId);
        expect($invoiceId)->toStartWith('INV-');
        expect($orderId)->toStartWith('ORDER-');
    });

    it('can generate multiple IDs rapidly', function () {
        $ids = [];

        for ($i = 0; $i < 5; $i++) {
            $ids[] = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
            $ids[] = generateUniqueIdWithPrefix('ORDER', 'test_orders', 'order_no');
        }

        expect($ids)->toHaveCount(10);
        expect(array_unique($ids))->toHaveCount(10);
    });
});