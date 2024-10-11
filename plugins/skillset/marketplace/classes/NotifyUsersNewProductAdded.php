<?php

namespace skillset\Marketplace\Classes;

use RainLab\User\Models\User;
use skillset\Marketplace\Models\Application;
use skillset\Notifications\Models\Notification;

class NotifyUsersNewProductAdded
{
    /**
     * @throws \Exception
     */
    public function fire($job, $data)
    {
        if ($job->attempts() > 3) {
            throw new \Exception('Job failed after 3 attempts');
        }

        $app = Application::find($data['id']);

        $userIds = User::where('is_unactive', 0)
            ->where('id', '!=', $app->user_id)
            ->when($app->region_id, function ($query) use ($app) {
                $query->where('region_id', $app->region_id);
            })
            ->pluck('id')
            ->toArray();

        (new Notification())->sendTemplateNotifications(
            $userIds,
            'newMarketplaceApplication',
            [],
            ['type' => 'marketplace_application', 'id' => $app->id],
            'marketplace_application_details'
        );

        $job->delete();
    }
}