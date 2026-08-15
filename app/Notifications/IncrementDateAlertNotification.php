<?php

namespace App\Notifications;

use App\Models\Increment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncrementDateAlertNotification extends Notification
{
  use Queueable;

  protected $increment;

  /**
   * Create a new notification instance.
   */
  public function __construct($increment)
  {
    if ($increment instanceof Increment) {
      $increment->loadMissing(['employee.user']);
    }

    $this->increment = $increment;
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
   * Get the array representation of the notification (Database Channel).
   *
   * @return array<string, mixed>
   */
  public function toDatabase(object $notifiable): array
  {
    $employeeName = $this->increment->employee?->user?->name ?? 'موظف';
    $amount       = $this->increment->amount ?? 0;
    $date         = $this->increment->date?->format('Y-m-d') ?? '';
    $reason       = $this->increment->reason ?? 'لا يوجد سبب مذكر';

    return [
      'title'         => 'تنبيه استحقاق زيادة موظف',
      'message'       => "موعد زيادة الموظف ({$employeeName}) بقيمة ({$amount}) قد حان بتاريخ ({$date}). السبب: {$reason}",
      'increment_id'  => $this->increment->id,
      'employee_id'   => $this->increment->employee_id,
      'amount'        => $amount,
      'date'          => $date,
      'employee_name' => $employeeName,
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
