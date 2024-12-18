<?php

namespace App\Console;


use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\SendReminderNotification;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // استرجاع الطلبات التي تحتاج إلى تذكير
        $orders = Order::where('date', now()->toDateString())
            ->where('time', '>=', now()->toTimeString())->where('send',0)
            ->get();

        foreach ($orders as $order) {
               $order->update([
                   'send'=>1
                   ]) ;
                // dd($order);
            // أرسل تذكيرًا
            SendReminderNotification::dispatch($order);
        }
    })->everyMinute(); // يمكنك تغيير ذلك حسب الحاجة
}

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
