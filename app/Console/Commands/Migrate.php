<?php

namespace Coyote\Console\Commands;

use Illuminate\Console\Command;
use DB;

ini_set('memory_limit', '1G');

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Coyote from 1.x to 2.0';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function skipPrefix($prefix, $data)
    {
        $result = [];

        foreach ($data as $key => $value) {
            $key = substr_replace($key, '', 0, strlen($prefix));
            $result[$key] = $value;
        }

        return $result;
    }

    private function rename(&$data, $oldKey, $newKey)
    {
        $data[$newKey] = $data[$oldKey];
        unset($data[$oldKey]);

        return $data;
    }

    private function timestampToDatetime(&$value)
    {
        $value = date('Y-m-d H:i:s', $value);

        return $value;
    }

    private function setNullIfEmpty(&$value)
    {
        if (empty($value)) {
            $value = null;
        }

        return $value;
    }

    private function skipHost($url)
    {
        $parsed = parse_url($url);

        $url = trim($parsed['path'], '/');
        if (!empty($parsed['query'])) {
            $url .= '?' . $parsed['query'];
        }
        if (!empty($parsed['fragment'])) {
            $url .= '#' . $parsed['fragment'];
        }
        if (!empty($parsed['host']) && $parsed['host'] == 'forum.4programmers.net') {
            $url = 'Forum/' . $url;
        }

        return $url;
    }

    private function count($tables)
    {
        $result = 0;

        if (!is_array($tables)) {
            $tables = [$tables];
        }

        foreach ($tables as $table) {
            $result += DB::connection('mysql')->table($table)->count();
        }

        return $result;
    }

    /**
     * @todo ip_invalid zapisac do mongo
     * @todo Co z kolumna flood?
     */
    private function migrateUsers()
    {
        $this->info('Users...');

        $sql = DB::connection('mysql')->table('user')->where('user_id', '>', 0)->orderBy('user_id')->get();
        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = $this->skipPrefix('user_', $row);

                unset($row['permission'], $row['ip_invalid'], $row['submit_enter']);
                $this->rename($row, 'regdate', 'created_at');
                $this->rename($row, 'dateformat', 'date_format');
                $this->rename($row, 'lastvisit', 'visited_at');
                $this->rename($row, 'ip_login', 'browser');
                $this->rename($row, 'ip_access', 'access_ip');
                $this->rename($row, 'alert_access', 'alert_failure');
                $this->rename($row, 'notify', 'alerts');
                $this->rename($row, 'notify_unread', 'alerts_unread');
                $this->rename($row, 'allow_notify', 'allow_subscribe');
                $this->rename($row, 'active', 'is_active');
                $this->rename($row, 'confirm', 'is_confirm');
                $this->rename($row, 'group', 'group_id');
                $this->rename($row, 'post', 'posts');

                $this->timestampToDatetime($row['created_at']);

                if ($row['visited_at']) {
                    $this->timestampToDatetime($row['visited_at']);
                } else {
                    $row['visited_at'] = null;
                }

                $this->setNullIfEmpty($row['photo']);
                $row['updated_at'] = $row['visited_at'] ?: $row['created_at'];

                DB::table('users')->insert($row);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * @100%
     */
    public function migrateGroups()
    {
        $this->info('Groups...');
        $groups = DB::connection('mysql')->table('group')->where('group_id', '>', 2)->orderBy('group_id')->get();

        DB::beginTransaction();

        try {
            foreach ($groups as $group) {
                $group = $this->skipPrefix('group_', $group);

                unset($group['display'], $group['open'], $group['type']);
                $this->rename($group, 'desc', 'description');
                $this->rename($group, 'leader', 'leader_id');
                $this->rename($group, 'exposed', 'partner');

                $group['created_at'] = $group['updated_at'] = date('Y-m-d H:i:s');
                $this->setNullIfEmpty($group['leader_id']);

                DB::table('groups')->insert($group);

                $sql = DB::connection('mysql')->table('auth_group')->where('group_id', '=', $group['id'])->get();

                foreach ($sql as $row) {
                    DB::table('group_users')->insert((array) $row);
                }

                $sql = DB::connection('mysql')->table('auth_data')->where('data_group', '=', $group['id'])->get();

                foreach ($sql as $row) {
                    $row = (array) $row;

                    $this->rename($row, 'data_group', 'group_id');
                    $this->rename($row, 'data_option', 'permission_id');
                    $this->rename($row, 'data_value', 'value');

                    DB::table('group_permissions')->insert($row);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     */
    private function migratePermissions()
    {
        $this->info('Permissions...');
        $permissions = DB::connection('mysql')->table('auth_option')->get();

        DB::beginTransaction();

        try {
            foreach ($permissions as $permission) {
                $permission = $this->skipPrefix('option_', $permission);

                $this->rename($permission, 'text', 'name');
                $this->rename($permission, 'label', 'description');

                $mapping = [
                    'a_' => 'adm-access',
                    'f_sticky' => 'forum-sticky',
                    'f_edit' => 'forum-update',
                    'f_delete' => 'forum-delete',
                    'f_announcement' => 'forum-announcement',
                    'f_lock' => 'forum-lock',
                    'f_move' => 'forum-move',
                    'f_merge' => 'forum-merge',
                    'm_edit' => 'microblog-update',
                    'm_delete' => 'microblog-delete',
                ];

                if (in_array($permission['name'], array_keys($mapping))) {
                    $permission['name'] = str_replace(array_keys($mapping), array_values($mapping), $permission['name']);
                    DB::table('permissions')->insert($permission);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     */
    private function migrateSkills()
    {
        $this->info('User skills...');

        $sql = DB::connection('mysql')->table('user_skill')->get();
        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = $this->skipPrefix('skill_', $row);

                $this->rename($row, 'user', 'user_id');
                DB::table('user_skills')->insert($row);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * @todo Slowa zawieraja niewspierane znaczniki <ort> Trzeba to usunac z tekstu
     */
    private function migrateWords()
    {
        $this->info('Words...');

        $sql = DB::connection('mysql')->table('censore')->get();
        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = $this->skipPrefix('censore_', $row);

                $this->rename($row, 'text', 'word');
                DB::table('words')->insert($row);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * Wymaga uzupelnienia tabeli alert_types
     *
     * @todo Co z URL? Trzeba usunac z nich http:// na poczatku
     */
    private function migrateAlerts()
    {
        $this->info('Alerts...');
        $count = $this->count(['notify_header', 'notify_sender', 'notify_user']);

        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            DB::statement('TRUNCATE alert_settings');

            $sql = DB::connection('mysql')
                ->table('notify_header')
                ->select(['notify_header.*', 'notify_sender.sender_time AS header_sender_time'])
                ->join('notify_sender', 'sender_header', '=', 'header_id')
                ->groupBy('header_id')
                ->get();

            foreach ($sql as $row) {
                $row = $this->skipPrefix('header_', $row);

                $this->rename($row, 'notify', 'type_id');
                $this->rename($row, 'recipient', 'user_id');
                $this->rename($row, 'sender_time', 'created_at');
                $this->rename($row, 'read', 'read_at');
                $this->rename($row, 'mark', 'is_marked');

                unset($row['time'], $row['sender'], $row['headline']);

                $this->timestampToDatetime($row['created_at']);
                $row['object_id'] = substr(md5($row['type_id'] . $row['subject']), 16);

                $this->setNullIfEmpty($row['url']);
                $this->setNullIfEmpty($row['excerpt']);

                $row['read_at'] = !$row['read_at'] ? null : $this->timestampToDatetime($row['read_at']);

                if (empty($row['subject'])) {
                    $row['subject'] = '';
                }

                if ($row['url']) {
                    $row['url'] = $this->skipHost($row['url']);
                }

                DB::table('alerts')->insert($row);
                $bar->advance();
            }

            ///////////////////////////////////////////////////////////////////////////////

            DB::connection('mysql')->table('notify_sender')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('sender_', (array) $row);

                    $this->rename($row, 'user', 'user_id');
                    $this->rename($row, 'time', 'created_at');
                    $this->rename($row, 'header', 'alert_id');

                    $this->timestampToDatetime($row['created_at']);
                    DB::table('alert_senders')->insert($row);

                    $bar->advance();
                }
            });

            //////////////////////////////////////////////////////////////////////////////////

            DB::connection('mysql')->table('notify_user')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = (array) $row;

                    DB::table('alert_settings')->insert([
                        'type_id' => $row['notify_id'],
                        'user_id' => $row['user_id'],
                        'profile' => $row['notifier'] & 1,
                        'email' => $row['notifier'] & 2
                    ]);

                    $bar->advance();
                }
            });

            $bar->finish();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement('SET session_replication_role = replica');
        $this->migrateUsers();
        // musi byc przed dodawaniem grup
        $this->migratePermissions();
        $this->migrateGroups();
        $this->migrateSkills();
        $this->migrateWords();
        $this->migrateAlerts();

        DB::statement('SET session_replication_role = DEFAULT');
    }
}
