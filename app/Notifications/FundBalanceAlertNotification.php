<?php

namespace App\Notifications;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\ProjectFundCurrency;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FundBalanceAlertNotification extends Notification
{
  use Queueable;

  protected $fund;

  /**
   * Create a new notification instance.
   */
  public function __construct($fund)
  {
    if ($fund instanceof CurrencyFund) {
      $fund->loadMissing(['currency', 'fund.user']);
    } elseif ($fund instanceof CompanyFundCurrency) {
      $fund->loadMissing(['currency', 'companyFund']);
    } elseif ($fund instanceof ProjectFundCurrency) {
      $fund->loadMissing(['currency', 'projectFund.project']);
    }

    $this->fund = $fund;
  }
  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['database'];
  }

  /**
   * Get the mail representation of the notification.
   */
  // public function toMail(object $notifiable): MailMessage
  // {
  //   return (new MailMessage)
  //     ->line('The introduction to the notification.')
  //     ->action('Notification Action', url('/'))
  //     ->line('Thank you for using our application!');
  // }

  /**
   * Get the array representation of the notification (Database Channel).
   *
   * @return array<string, mixed>
   */
  public function toDatabase(object $notifiable): array
  {
    $currencySymbol = $this->fund->currency?->symbol ?? '';
    $balance        = $this->fund->balance ?? 0;

    $fundName   = 'صندوق';
    $ownerName  = 'غير متوفر';

    if ($this->fund instanceof CurrencyFund) {
      $fundName  = $this->fund->fund?->name ?? 'صندوق شخصي';
      $ownerName = $this->fund->fund?->user?->name ?? 'مستخدم';
    } elseif ($this->fund instanceof CompanyFundCurrency) {
      $fundName  = $this->fund->companyFund?->name ?? 'صندوق الشركة';
      $ownerName = 'الشركة';
    } elseif ($this->fund instanceof ProjectFundCurrency) {
      $fundName  = $this->fund->projectFund?->name ?? 'صندوق المشروع';
      $ownerName = $this->fund->projectFund?->project?->name ?? 'مشروع';
    }

    return [
      'title'      => 'تنبيه رصيد الصندوق',
      'message'    => "وصل رصيد ({$fundName}) الخاص بـ ({$ownerName}) إلى الحد المحدد. الرصيد المتبقي: {$balance} {$currencySymbol}",
      'fund_id'    => $this->fund->id,
      'fund_type'  => get_class($this->fund),
      'balance'    => $balance,
      'currency'   => $currencySymbol,
      'user_name' => $ownerName,
    ];
  }
  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return $this->toDatabase($notifiable);
  }
}
