<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\CustomerInvoice;
use App\Models\SalesOrder;
use Exception;

class CreditLimitService
{
    /**
     * Compute current credit exposure for customer (DL-05 / SR-08)
     * Credit Exposure = Opening AR + Unpaid Posted Invoices + Confirmed Unbilled Sales Orders
     */
    public static function calculateExposure(Contact $customer): float
    {
        // 1. Opening AR
        $openingAR = (float) $customer->opening_receivable;

        // 2. Unpaid customer invoices
        $unpaidInvoices = (float) CustomerInvoice::where('customer_id', $customer->id)
            ->whereIn('status', ['Posted'])
            ->sum('open_amount');

        // 3. Unbilled Confirmed Sales Orders (prevent double counting: only those without posted invoices)
        $unbilledSOs = (float) SalesOrder::where('customer_id', $customer->id)
            ->where('status', 'Confirmed')
            ->whereDoesntHave('invoices', function ($q) {
                $q->whereIn('status', ['Posted', 'Paid']);
            })
            ->sum('total_amount');

        return $openingAR + $unpaidInvoices + $unbilledSOs;
    }

    /**
     * Validate credit limit before confirming a new Sales Order
     * Throws Exception if total exposure exceeds credit limit
     */
    public static function validateOrderExposure(Contact $customer, float $newOrderGrossTotal, string $orderNumber = ''): void
    {
        $currentExposure = self::calculateExposure($customer);
        $totalExposure = $currentExposure + $newOrderGrossTotal;

        if ($totalExposure > (float) $customer->credit_limit) {
            $msg = sprintf(
                'Credit limit exceeded for customer %s. Credit Limit: Rp%s, Current Exposure: Rp%s, Order Value: Rp%s, Total Exposure: Rp%s',
                $customer->name,
                number_format($customer->credit_limit, 0, ',', '.'),
                number_format($currentExposure, 0, ',', '.'),
                number_format($newOrderGrossTotal, 0, ',', '.'),
                number_format($totalExposure, 0, ',', '.')
            );

            AuditService::log('Credit Limit Blocked', 'Sales Order', $orderNumber, $msg);
            throw new Exception($msg);
        }
    }
}
