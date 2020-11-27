<?php

namespace Coyote\Console\Commands;

use Coyote\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixAvatarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:avatar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::whereNotNull('photo')->where('photo', '!=', '')->get();

        /** @var User $user */
        foreach ($users as $user) {
            $ext = pathinfo($user->photo, PATHINFO_EXTENSION);

            if (!$ext) {
                if (Storage::disk('public')->exists('photo/' . $user->photo)) {
                    $name = uniqid() . '.png';
                    $prefix = substr($name, 0, 2);

                    Storage::disk('public')->move('photo/' . $user->photo, 'photo/' . $prefix . '/' . $name);

                    $this->line($user->photo->path());

                    $user->photo = $name;
                    $user->save();
                }
            }
        }
    }
}
