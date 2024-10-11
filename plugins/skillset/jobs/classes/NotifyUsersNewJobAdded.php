<?php

namespace skillset\Jobs\Classes;

use GuzzleHttp\Exception\GuzzleException;
use RainLab\User\Models\User;
use skillset\Jobs\Models\Job;
use skillset\Notifications\Models\Notification;

class NotifyUsersNewJobAdded
{
    /**
     * @throws \Exception
     * @throws GuzzleException
     */
    public function fire($job, $data)
    {
        if ($job->attempts() > 3) {
            throw new \Exception('Job failed after 3 attempts');
        }

        $app = Job::find($data['id']);

        $userIds = User::where('is_unactive', 0)
            ->where('id', '!=', $app->user_id)
            ->when($app->region_id, function ($query) use ($app) {
                $query->where('region_id', $app->region_id);
            })
            ->pluck('id')
            ->toArray();

        (new Notification())->sendTemplateNotifications(
            $userIds,
            'newJob',
            [],
            ['type' => 'job', 'id' => $app->id],
            'job_details',
            'new_job_' . $app->id
        );

        $job->delete();
    }
}