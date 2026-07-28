<?php

namespace App\Notifications;

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
    return [
      'title'   => 'تنبيه رصيد الصندوق',
      'message' => 'وصل رصيد الصندوق إلى المبلغ المحدد: ' . $this->fund->balance,
      'fund_id' => $this->fund->id,
      'balance' => $this->fund->balance,
    ];
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      'title'   => 'تنبيه رصيد الصندوق',
      'message' => 'وصل رصيد الصندوق إلى المبلغ المحدد: ' . $this->fund->balance,
      'fund_id' => $this->fund->id,
    ];
  }
}
