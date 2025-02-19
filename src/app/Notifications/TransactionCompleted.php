<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Purchase; // 追加

class TransactionCompleted extends Notification
{
    use Queueable;

    protected $purchase; // 追加

    /**
     * 通知のインスタンスを作成
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        // Purchaseモデルのインスタンスを受け取りプロパティに保存
        $this->purchase = $purchase;
    }

    /**
     * メール通知のチャンネルを指定
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail']; // メールで通知
    }

    /**
     * メール通知の内容を設定
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('【COACHTECHフリマ】取引が完了しました')
            ->greeting('こんにちは ' . $notifiable->profile_name . ' さん')
            ->line('あなたの出品した商品「' . $this->purchase->item->name . '」の取引が完了しました。')
            ->action('詳細を確認する', url('/mypage/?tab=in_progress'))
            ->line('COACHTECHフリマをご利用いただきありがとうございます。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
