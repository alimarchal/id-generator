<?php

use Illuminate\Support\Facades\DB;

describe('Real World Usage Scenarios', function () {
    it('simulates e-commerce order flow', function () {
        // Create order
        $orderNumber = generateUniqueIdWithPrefix('ORD', 'test_orders', 'order_no');
        createTestOrder($orderNumber);

        // Create invoice for the order
        $invoiceNumber = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($invoiceNumber);

        // Create tracking number
        $trackingNumber = generateUniqueIdWithPrefix('TRK', 'test_orders', 'order_no');

        expect($orderNumber)->toStartWith('ORD-');
        expect($invoiceNumber)->toStartWith('INV-');
        expect($trackingNumber)->toStartWith('TRK-');

        expect([$orderNumber, $invoiceNumber, $trackingNumber])
            ->toHaveCount(3)
            ->and(array_unique([$orderNumber, $invoiceNumber, $trackingNumber]))
            ->toHaveCount(3);
    });

    it('simulates customer support ticket system', function () {
        // Create multiple tickets
        $tickets = [];
        for ($i = 0; $i < 3; $i++) {
            $ticketNumber = generateUniqueId('complaint', 'test_invoices', 'invoice_no');
            $tickets[] = $ticketNumber;
            createTestInvoice($ticketNumber);
        }

        expect($tickets)->toHaveCount(3);
        expect($tickets[0])->toEndWith('-0001');
        expect($tickets[1])->toEndWith('-0002');
        expect($tickets[2])->toEndWith('-0003');

        foreach ($tickets as $ticket) {
            expect($ticket)->toStartWith('CMP-');
        }
    });

    it('simulates high volume document generation', function () {
        $documents = [];

        // Generate various document types rapidly
        for ($i = 0; $i < 20; $i++) {
            if ($i % 3 === 0) {
                $documents[] = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
            } elseif ($i % 3 === 1) {
                $documents[] = generateUniqueId('quotation', 'test_invoices', 'invoice_no');
            } else {
                $documents[] = generateUniqueIdWithPrefix('CUSTOM', 'test_orders', 'order_no');
            }
        }

        expect($documents)->toHaveCount(20);
        expect(array_unique($documents))->toHaveCount(20);
    });

    it('handles mixed usage patterns', function () {
        // Mix helper functions and class usage
        $helperInvoice = generateUniqueId('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($helperInvoice);

        $generator = app(\Alimarchal\IdGenerator\IdGenerator::class);
        $classInvoice = $generator->generate('invoice', 'test_invoices', 'invoice_no');
        createTestInvoice($classInvoice);

        $helperOrder = generateUniqueIdWithPrefix('ORD', 'test_orders', 'order_no');
        $classOrder = $generator->generateWithPrefix('ORD', 'test_orders', 'order_no');

        expect($helperInvoice)->toEndWith('-0001');
        expect($classInvoice)->toEndWith('-0002');
        expect($helperOrder)->toEndWith('-0001');
        expect($classOrder)->toEndWith('-0002');
    });
});