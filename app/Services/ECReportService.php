<?php

namespace App\Services;

use App\Models\ExchangeOrderRequest;
use App\Models\Order;
use App\Models\RefundOrderItem;
use App\Models\RefundOrderRequest;
use Carbon\Carbon;

class ECReportService
{
    //-------------------------------------- get interval -------------------------------
    public static function determineInterval($startDate, $endDate)
    {
        $start = Carbon::createFromFormat('Y-m-d', $startDate);
        $end = Carbon::createFromFormat('Y-m-d', $endDate);

        $diffInDays = $start->diffInDays($end);

        if ($diffInDays <= 1) {
            return 'hourly';
        } elseif ($diffInDays <= 31) {
            return 'daily';
        } elseif ($diffInDays <= 365) {
            return 'monthly';
        } else {
            return 'yearly';
        }
    }

    // -------------------------------- get the dates based on the start date, end date, and interval ------------------------------------
    public static function generateIntervalDates(Carbon $startDate, Carbon $endDate, $interval)
    {
        $dates = [];
        $currentDate = $startDate->copy();

        // Map the interval to Carbon's expected units
        $intervalMapping = [
            'hourly' => 'hour',
            'daily' => 'day',
            'monthly' => 'month',
            'yearly' => 'year'
        ];

        // Get the unit for the Carbon method
        $unit = $intervalMapping[$interval];

        // If the interval is hourly, extend the end date by one day to include the last day completely
        if ($interval == 'hourly') {
            $endDate->addDays(1);
        }

        // Generate the dates
        while ($currentDate->lte($endDate)) {
            $dates[] = $currentDate->format('Y-m-d H:i');
            $currentDate->add(1, $unit);
        }

        // If the interval is hourly, remove the last date since it's from the next day
        if ($interval == 'hourly') {
            array_pop($dates);
        }
        return $dates;
    }

    //------------------------------------------ get order status statistics---------------------------------
    public static function getOrdersStatusStatistics(Carbon $startDate, Carbon $endDate)
    {
        // Define the order statuses
        $statuses = ['pending', 'processing', 'shipped', 'completed', 'canceled', 'declined'];

        // Initialize an array to store counts and percentages
        $statusCounts = [];
        $statusPercentages = [];

        // Get total count of orders for all statuses
        $totalOrdersCount = Order::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])->count();
        // Loop through each status to get count and percentage
        foreach ($statuses as $status) {
            $statusCount = Order::where('order_status', $status)
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count();
            $statusCounts[$status] = $statusCount;

            // Calculate percentage
            $statusPercentage = $totalOrdersCount ? ($statusCount / $totalOrdersCount) * 100 : 0;
            $statusPercentages[$status] = round($statusPercentage, 2);
        }

        // get orders ids
        $ordersIds = Order::whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])->pluck('order_id')->toArray();

        // get refunded orders count
        $refundedOrdersCount = RefundOrderRequest::whereIn('order_id', $ordersIds)->count();
        $exchangeOrderCount = ExchangeOrderRequest::whereIn('order_id', $ordersIds)->count();

        // Return results
        return [
            'total_orders' => $totalOrdersCount,
            'status_counts' => $statusCounts,
            'status_percentages' => $statusPercentages,
            "refund_requests_count" => $refundedOrdersCount,
            "exchange_requests_count" => $exchangeOrderCount
        ];
    }

    //------------------------------------------ get Orders Sales Statistics By Dates ---------------------------------------
    public static function getOrdersSalesStatisticsByDates(array $dates, string $interval)
    {
        // Get the orders in each date ------------------------------
        $orders = [];
        $ordersCount = [];

        foreach ($dates as $dateString) {
            // Parse the date string to a Carbon instance
            $date = Carbon::parse($dateString);
            $query = Order::with("order_items");
            switch ($interval) {
                case 'hourly':
                    $query->whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->whereDay('created_at', $date->day)
                        ->whereRaw('HOUR(created_at) = ?', [$date->hour]);
                    $dateString = $date->format("h:i a"); // 08:00 am
                    break;
                case 'daily':
                    $query->whereDate('created_at', $dateString);
                    $dateString = $date->format("Y-m-d"); // 2024-11-29
                    break;
                case 'monthly':
                    $query->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
                    $dateString = $date->format("F, Y"); // "August, 2024"
                    break;
                case 'yearly':
                    $query->whereYear('created_at', $date->year);
                    $dateString = $date->format("Y"); // 2024
                    break;
            }

            // execute query
            $orders[$dateString] = $query->get();
            $ordersCount[$dateString] = $query->count();
        }

        // Get the statistics in each date orders ------------------------------
        $ordersSalesStatisticsByDate = [];
        foreach ($orders as $dateString => $orderList) {
            $totalItems = 0;
            $totalEarning = 0;
            $totalRefunded = 0;
            $totalTax = 0;

            foreach ($orderList as $order) {
                foreach ($order->order_items as $orderItem) {
                    $totalItems += $orderItem->local_quantity + $orderItem->online_quantity;
                    $totalEarning += $orderItem->subtotal;

                    // Check if the item is refunded
                    // $refundedItem = RefundOrderItem::where('order_item_id', $orderItem->order_item_id)
                    //     ->join("refund_order_request", "refund_order_request.refund_order_request_id", "refund_order_items.refund_order_request_id")
                    //     ->whereNotNull("money_back_at")
                    //     ->first();

                    $refundedItem = RefundOrderItem::where('order_item_id', $orderItem->order_item_id)->first();

                    if ($refundedItem) {
                        // the item is refunded so add it's value to the refunded
                        $totalRefunded += $orderItem->subtotal;
                    } else {
                        // the item is not refunded so add it's tax
                        $totalTax += $orderItem->tax_value;
                    }
                }
            }

            $revenue = $totalEarning - $totalRefunded;
            $netProfit = $revenue - $totalTax;

            $ordersSalesStatisticsByDate[$dateString] = [
                'total_items' => $totalItems,
                'total_earning' => $totalEarning,
                'total_refunded' => $totalRefunded,
                'total_tax' => $totalTax,
                'revenue' => $revenue,
                'net_profit' => $netProfit,
            ];
        }

        // Get the statistics sum over all dates ------------------------------
        // Initialize the sums array
        $ordersSalesStatistics = [
            'total_items' => 0,
            'total_earning' => 0,
            'total_refunded' => 0,
            'revenue' => 0,
            'total_tax' => 0,
            'net_profit' => 0,
        ];

        // Loop through each date's statistics and accumulate the sums
        foreach ($ordersSalesStatisticsByDate as $date => $statistics) {
            $ordersSalesStatistics['total_items'] += $statistics['total_items'];
            $ordersSalesStatistics['total_earning'] += $statistics['total_earning'];
            $ordersSalesStatistics['total_refunded'] += $statistics['total_refunded'];
            $ordersSalesStatistics['revenue'] += $statistics['revenue'];
            $ordersSalesStatistics['total_tax'] += $statistics['total_tax'];
            $ordersSalesStatistics['net_profit'] += $statistics['net_profit'];
        }

        return [
            "orders_count_by_date" => $ordersCount,
            "orders_sales_statistics" => $ordersSalesStatistics,
            "orders_sales_statistics_by_date" => $ordersSalesStatisticsByDate,
        ];
    }
}
