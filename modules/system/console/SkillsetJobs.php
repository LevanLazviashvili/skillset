<?php namespace System\Console;

use Cms\Classes\Theme;
use Illuminate\Console\Command;
use RainLab\User\Models\User;
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
class SkillsetJobs extends Command
{
    use \Illuminate\Console\ConfirmableTrait;

    /**
     * The console command name.
     * @var string
     */
    protected $name = 'skillset:jobs';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Switch the active theme.';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $User = (new User)->find(20);
        $User->balance = $User->balance + 1;
        $User->save();
        echo 'test';

    }

}
