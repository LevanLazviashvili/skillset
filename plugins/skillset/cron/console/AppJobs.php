<?php namespace skillset\cron\console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use RainLab\User\Controllers\Users;
use RainLab\User\Models\User;
use RainLab\User\Models\Worker;
use skillset\Conversations\Models\Message;
use skillset\Jobs\Controllers\Jobs;
use skillset\Marketplace\Controllers\Applications;
use skillset\Marketplace\Controllers\Applications as MarketplaceApp;
use skillset\Notifications\Controllers\Notifications;
use skillset\Offers\Models\Offer;
use skillset\Offers\Models\OfferWorker;
use skillset\Orders\Models\Order;
use skillset\Rating\Models\Rating;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Console command to switch themes.
 *
 * This switches the active theme to another one, saved to the database.
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class AppJobs extends Command
{
    use \Illuminate\Console\ConfirmableTrait;

    /**
     * The console command name.
     * @var string
     */
    protected $name = 'app:jobs';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'skillset jobs';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        (new Notifications)->sendAutoNotifications();
        (new Offer)->cancelUnActiveOffers();
        (new Rating)->makeOldReviewsActive();
        (new Notifications)->notifyWorkersWithNegativeBalance();
        (new Notifications)->notifyClientWithUnPayedFinishedWork();
        (new Notifications)->notifyUnratedOrdersUsers();
        (new Notifications)->notifyUnratedJobOrderUsers();
        (new Worker)->makeWorkersUnActive();
        (new Notifications)->notifyWorkersAboutEndDate();
        (new Jobs)->makeJobsInactive();
        (new Applications)->makeAppsInactive();
        (new Jobs)->updateExpiredVipJobs();
        (new MarketplaceApp)->updateExpiredVipApps();
        (new Users)->makeJobUsersWithNegativeBalanceInactive();

        traceLog('running app:jobs');



//        (new Notifications)->notifyUsersAboutNewChat();
//        (new Notifications)->notifyUsersAboutUnreadMessages();
//        (new Message)->sendSystemMessage(31, 'offer_accepted_by_worker', ['offer_status_id' => 1]);
    }

}
