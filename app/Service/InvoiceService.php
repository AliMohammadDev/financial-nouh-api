<?php

namespace App\Service;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InvoiceService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->where('invoice_number', 'like', "%{$value}%")
          ->orWhereHas('item', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          })
          ->orWhereHas('supplier', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
          });
      }),
      AllowedFilter::exact('item_id'),
      AllowedFilter::exact('supplier_id'),
      AllowedFilter::exact('is_posted'),
    ];

    $query = QueryBuilder::for(Invoice::class)
      ->with(['item', 'supplier', 'expense'])
      ->allowedFilters(...$filters)
      ->defaultSort('-created_at');

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function findOne(Invoice $invoice): Invoice
  {
    return $invoice->load(['item', 'supplier', 'expense']);
  }

  public function create(array $data): Invoice
  {
    return DB::transaction(function () use ($data) {
      $year = date('Y');
      $month = date('m');

      $lastInvoice = Invoice::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->latest('id')
        ->lockForUpdate()
        ->first();

      $sequence = 1;
      if ($lastInvoice) {
        $parts = explode('-', $lastInvoice->invoice_number);
        $lastSequence = end($parts);
        $sequence = (int) $lastSequence + 1;
      }

      $formattedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

      $data['invoice_number'] = "{$year}-{$month}-{$formattedSequence}";

      $invoice = Invoice::create($data);
      return $invoice->load(['item', 'supplier', 'expense']);
    });
  }
  public function update(Invoice $invoice, array $data): Invoice
  {
    DB::transaction(function () use ($invoice, $data) {
      $invoice->update($data);
    });

    return $invoice->load(['item', 'supplier', 'expense']);
  }

  public function delete(Invoice $invoice): bool
  {
    return DB::transaction(function () use ($invoice) {
      return (bool) $invoice->delete();
    });
  }
}
