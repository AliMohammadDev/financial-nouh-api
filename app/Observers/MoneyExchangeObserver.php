<?php

namespace App\Observers;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\MoneyExchange;
use App\Models\ProjectFundCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class MoneyExchangeObserver
{
  /**
   * Handle the MoneyExchange "creating" event.
   */
  public function creating(MoneyExchange $moneyExchange): void
  {
    $amount = (float) $moneyExchange->amount;
    $rate = (float) $moneyExchange->exchange_rate;
    $operation = $moneyExchange->operation;

    $moneyExchange->converted_amount = ($operation === 'multiply')
      ? ($amount * $rate)
      : ($rate > 0 ? $amount / $rate : 0);
  }

  /**
   * Handle the MoneyExchange "created" event.
   */
  public function created(MoneyExchange $moneyExchange): void
  {
    $this->applyExchange($moneyExchange);
  }

  /**
   * Handle the MoneyExchange "updating" event.
   */
  public function updating(MoneyExchange $moneyExchange): void
  {
    // 1. إلغاء تأثير العملية القديمة تماماً من الصندوق القديم والعملات القديمة
    $this->revertExchange($moneyExchange);

    // 2. تحديث حساب القيمة المحولة بناءً على التعديلات الجديدة
    $amount = (float) $moneyExchange->amount;
    $rate = (float) $moneyExchange->exchange_rate;
    $operation = $moneyExchange->operation;

    $moneyExchange->converted_amount = ($operation === 'multiply')
      ? ($amount * $rate)
      : ($rate > 0 ? $amount / $rate : 0);
  }

  /**
   * Handle the MoneyExchange "updated" event.
   */
  public function updated(MoneyExchange $moneyExchange): void
  {
    // 3. تطبيق العملية الجديدة بالبيانات المحدثة والصندوق الجديد (إن وجد)
    $this->applyExchange($moneyExchange);
  }

  /**
   * Handle the MoneyExchange "deleted" event.
   */
  public function deleted(MoneyExchange $moneyExchange): void
  {
    $this->revertExchange($moneyExchange);
  }

  /**
   * Handle the MoneyExchange "restored" event.
   */
  public function restored(MoneyExchange $moneyExchange): void
  {
    $this->applyExchange($moneyExchange);
  }

  /**
   * Handle the MoneyExchange "force deleted" event.
   */
  public function forceDeleted(MoneyExchange $moneyExchange): void
  {
    $this->revertExchange($moneyExchange);
  }

  // ==========================================
  // Helper Methods
  // ==========================================

  protected function applyExchange(MoneyExchange $moneyExchange): void
  {
    $exchangeable = $moneyExchange->exchangeable;
    if (!$exchangeable) return;

    $amount = (float) $moneyExchange->amount;
    $rate = (float) $moneyExchange->exchange_rate;
    $operation = $moneyExchange->operation;
    $fromCurrency = (int) $moneyExchange->from_currency;
    $toCurrency = (int) $moneyExchange->to_currency;

    // --- التحقق من توفر الرصيد في عملة المصدر ---
    $sourceRecord = $this->getFundRecord($exchangeable, $fromCurrency);

    if (!$sourceRecord || (float) $sourceRecord->balance < $amount) {
      throw ValidationException::withMessages([
        'from_currency' => ['رصيد الصندوق غير كافٍ أو غير موجود لهذه العملة لإتمام عملية التحويل.'],
      ]);
    }

    $convertedAmount = (float) $moneyExchange->converted_amount;
    if ($convertedAmount <= 0) {
      $convertedAmount = ($operation === 'multiply')
        ? ($amount * $rate)
        : ($rate > 0 ? $amount / $rate : 0);
    }

    // خصم من عملة المصدر وإضافة إلى عملة الوجهة (مع الإنشاء التلقائي إذا لم تكن موجودة)
    $this->updateFundBalance($exchangeable, $fromCurrency, $amount, 'subtract');
    $this->updateFundBalance($exchangeable, $toCurrency, $convertedAmount, 'add');
  }

  protected function revertExchange(MoneyExchange $moneyExchange): void
  {
    // استخدام العلاقة أو الـ getOriginal في حال تم تغيير الصندوق أثناء التعديل
    $exchangeableType = $moneyExchange->getOriginal('exchangeable_type') ?: $moneyExchange->exchangeable_type;
    $exchangeableId = $moneyExchange->getOriginal('exchangeable_id') ?: $moneyExchange->exchangeable_id;
    $exchangeable = $exchangeableType::find($exchangeableId);

    if (!$exchangeable) return;

    $amount = (float) $moneyExchange->getOriginal('amount');
    $rate = (float) $moneyExchange->getOriginal('exchange_rate');
    $operation = $moneyExchange->getOriginal('operation');

    $convertedAmount = (float) $moneyExchange->getOriginal('converted_amount');
    if ($convertedAmount <= 0) {
      $convertedAmount = ($operation === 'multiply')
        ? ($amount * $rate)
        : ($rate > 0 ? $amount / $rate : 0);
    }

    $fromCurrency = (int) $moneyExchange->getOriginal('from_currency');
    $toCurrency = (int) $moneyExchange->getOriginal('to_currency');

    // عكس العملية السابقة (إعادة المبلغ للمصدر، وخصمه من الوجهة)
    $this->updateFundBalance($exchangeable, $fromCurrency, $amount, 'add');
    $this->updateFundBalance($exchangeable, $toCurrency, $convertedAmount, 'subtract');
  }

  protected function getFundRecord(Model $exchangeable, int $currencyId): ?Model
  {
    if ($exchangeable instanceof CurrencyFund) {
      return CurrencyFund::where('fund_id', $exchangeable->fund_id)
        ->where('currency_id', $currencyId)
        ->first();
    } elseif ($exchangeable instanceof CompanyFundCurrency) {
      return CompanyFundCurrency::where('company_fund_id', $exchangeable->company_fund_id)
        ->where('currency_id', $currencyId)
        ->first();
    } elseif ($exchangeable instanceof ProjectFundCurrency) {
      return ProjectFundCurrency::where('project_fund_id', $exchangeable->project_fund_id)
        ->where('currency_id', $currencyId)
        ->first();
    }

    return null;
  }

  protected function updateFundBalance(Model $exchangeable, int $currencyId, float $amount, string $action): void
  {
    $targetRecord = null;

    if ($exchangeable instanceof CurrencyFund) {
      $targetRecord = CurrencyFund::firstOrCreate(
        ['fund_id' => $exchangeable->fund_id, 'currency_id' => $currencyId],
        ['balance' => 0]
      );
    } elseif ($exchangeable instanceof CompanyFundCurrency) {
      $targetRecord = CompanyFundCurrency::firstOrCreate(
        ['company_fund_id' => $exchangeable->company_fund_id, 'currency_id' => $currencyId],
        ['balance' => 0]
      );
    } elseif ($exchangeable instanceof ProjectFundCurrency) {
      $targetRecord = ProjectFundCurrency::firstOrCreate(
        ['project_fund_id' => $exchangeable->project_fund_id, 'currency_id' => $currencyId],
        ['balance' => 0]
      );
    }

    if ($targetRecord) {
      $currentBalance = (float) $targetRecord->balance;
      $targetRecord->balance = ($action === 'add') ? ($currentBalance + $amount) : ($currentBalance - $amount);
      $targetRecord->save();
    }
  }
}
