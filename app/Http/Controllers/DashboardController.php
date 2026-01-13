<?php

namespace App\Http\Controllers;

use App\Analytics\Chart;
use App\Analytics\ChartValue;
use App\Analytics\Link;
use App\Analytics\MoneyMetric;
use App\Facades\Accounts;
use App\Models\Invoice;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController
{
    public function __invoke(Request $request)
    {
        $account = Accounts::current();
        $currency = $account->getCurrency();

        $defaultPeriod = now()->startOfYear();

        if (($requestPeriod = $request->input('period')) && is_numeric($requestPeriod) && $requestPeriod >= 2015 && $requestPeriod <= now()->year + 1) {
            $periodFrom = now()->startOfYear()->setYear((int) $requestPeriod);
            $periodUntil = $periodFrom->copy()->endOfYear();
        } else {
            $periodFrom = $defaultPeriod->copy();
            $periodUntil = $defaultPeriod->copy()->endOfYear();
        }

        $allYears = Invoice::query()
            ->toBase()
            ->where('account_id', $account->id)
            ->selectRaw("date_format(supplied_at, '%Y') as year")
            ->distinct()
            ->get()
            ->map(fn (object $result) => (int) $result->year)
            ->sortDesc()
            ->values();

        $metrics = [
            MoneyMetric::make(
                title: 'Fakturovaný obrat',
                value: Money::ofMinor(
                    minorAmount: $account
                        ->invoices()
                        ->where('draft', false)
                        ->whereBetween('supplied_at', [$periodFrom, $periodUntil])
                        ->sum('total_vat_exclusive'),
                    currency: $currency,
                ),
                link: Link::toRoute('Zobraziť', 'invoices', query: [
                    'issued_from' => $periodFrom->format('Y-m-d'),
                    'issued_until' => $periodUntil->format('Y-m-d'),
                    'limit' => 200,
                ]),
            ),

            MoneyMetric::make(
                title: 'Neuhradené',
                value: Money::ofMinor(
                    minorAmount: $account
                        ->invoices()
                        ->where('draft', false)
                        ->where('paid', false)
                        ->whereBetween('supplied_at', [$periodFrom, $periodUntil])
                        ->sum('total_vat_exclusive'),
                    currency: $currency,
                ),
                link: Link::toRoute('Zobraziť', 'invoices', query: [
                    'payment' => ['unpaid'],
                    'issued_from' => $periodFrom->format('Y-m-d'),
                    'issued_until' => $periodUntil->format('Y-m-d'),
                ]),
            ),

            MoneyMetric::make(
                title: 'Po splatnosti',
                value: Money::ofMinor(
                    minorAmount: $account
                        ->invoices()
                        ->where('draft', false)
                        ->where('paid', false)
                        ->whereBetween('supplied_at', [$periodFrom, $periodUntil])
                        ->where('payment_due_to', '<', now()->startOfDay())
                        ->sum('total_vat_exclusive'),
                    currency: $currency,
                ),
                link: Link::toRoute('Zobraziť', 'invoices', query: [
                    'due' => 'true',
                    'issued_from' => $periodFrom->format('Y-m-d'),
                    'issued_until' => $periodUntil->format('Y-m-d'),
                ]),
            ),
        ];

        $totalPerMonth = Invoice::query()
            ->toBase()
            ->select(
                DB::raw("date_format(supplied_at, '%Y-%m') as date"),
                DB::raw('sum(total_vat_exclusive) as total')
            )
            ->where('account_id', $account->id)
            ->where('draft', false)
            ->whereBetween('supplied_at', [$periodFrom, $periodUntil])
            ->groupBy('date')
            ->get()
            ->keyBy('date')
            ->map
            ->total;

        $yearChart = Chart::make()->formatAsMoney()->showLegend(false);

        Collection::range(1, 12)
            ->map(fn (int $month) => $periodFrom->copy()->startOfMonth()->setMonth($month))
            ->map(function (Carbon $period) use ($totalPerMonth, $currency) {
                $perMonth = $totalPerMonth->get($period->format('Y-m'));

                if (is_numeric($perMonth)) {
                    return [$period, Money::ofMinor($perMonth, $currency)];
                }

                return [$period, Money::zero($currency)];
            })
            ->each(fn (array $perMonth) => $yearChart->valueForMonth(
                date: $perMonth[0],
                value: new ChartValue('Fakturovaný obrat', $perMonth[1]),
            ));

        return Inertia::render('Dashboard', [
            'topMetrics' => $metrics,
            'year' => $periodFrom->format('Y'),
            'allYears' => $allYears,
            'defaultYear' => $periodFrom->year,
            'yearChart' => $yearChart,
        ]);
    }
}
