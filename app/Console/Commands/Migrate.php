<?php

namespace Coyote\Console\Commands;

use Coyote\Pm;
use Coyote\Post;
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
        if ($url[0] === '@') {
            return '/' . str_replace('@forum', 'Forum', $url);
        }
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

        return '/' . $url;
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

    private function fixSequence($tables)
    {
        if (!is_array($tables)) {
            $tables = [$tables];
        }

        foreach ($tables as $table) {
            DB::unprepared("SELECT setval('${table}_id_seq', (SELECT MAX(id) FROM $table))");
        }
    }

    /**
     * 100%
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

                unset($row['permission'], $row['ip_invalid'], $row['submit_enter'], $row['flood']);
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

                if ($row['group_id'] <= 2) {
                    $row['group_id'] = null;
                }

                DB::table('users')->insert($row);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();

            $this->fixSequence('users');
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

            $this->fixSequence(['groups', 'group_permissions']);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     *
     * @todo dodac uprawnienia firm-update oraz firm-delete (nie trzeba ich migrowac bo nie ma ich w bazie mysql)
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
                    'j_edit' => 'job-update',
                    'j_delete' => 'job-delete'
                ];

                if (in_array($permission['name'], array_keys($mapping))) {
                    $permission['name'] = str_replace(array_keys($mapping), array_values($mapping), $permission['name']);
                    DB::table('permissions')->insert($permission);
                }
            }

            DB::commit();

            $this->fixSequence('permissions');
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

            $this->fixSequence('user_skills');
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

                $row['replacement'] = str_replace(
                    ['<ort>', '</ort>', '</font>', '<font color="green">', '<font color=green>', '<font color=blue>', '<b><url=http://pajacyk.pl>JESTEM GLUPIM SPAMEREM</url></b>'],
                    ['<span style="color: red">', '</span>', '</span>', '<span style="color: green;">', '<span style="color: green;">', '<span style="color: blue;">', '<b><a href="http://pajacyk.pl">JESTEM GLUPIM SPAMEREM</a></b>'],
                    $row['replacement']
                );
                DB::table('words')->insert($row);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();

            $this->fixSequence('words');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getMessage());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * Wymaga uzupelnienia tabeli alert_types
     * 100%
     *
     * @todo usuniecie duplikatow
     * DELETE FROM alert_settings USING alert_settings alias
    WHERE alert_settings.type_id = alias.type_id AND alert_settings.user_id = alias.user_id AND
    alert_settings."id" < alias."id"
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

            $this->fixSequence('alerts');

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

            $this->fixSequence('alert_senders');

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

            $this->fixSequence('alert_settings');

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
     * 100%
     */
    private function migratePm()
    {
        $this->info('Pms...');
        $count = $this->count(['pm', 'pm_text']);

        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            $sql = DB::connection('mysql')
                ->table('pm_text')
                ->select(['pm_text.*', 'pm.pm_time AS pm_time'])
                ->join('pm', 'pm.pm_text', '=', 'pm_text.pm_text')
                ->groupBy('pm_text')
                ->get();

            foreach ($sql as $row) {
                $row = $this->skipPrefix('pm_', (array) $row);

                $this->rename($row, 'text', 'id');
                $this->rename($row, 'message', 'text');
                $this->rename($row, 'time', 'created_at');

                $this->timestampToDatetime($row['created_at']);

                DB::table('pm_text')->insert($row);
                $bar->advance();
            }

            $this->fixSequence('pm_text');

            ///////////////////////////////////////////////////////////////////////////////

            $sql = DB::connection('mysql')->table('pm')->get();

            foreach ($sql as $row) {
                $row = $this->skipPrefix('pm_', (array) $row);

                $from = $row['from'];
                $to = $row['to'];

                $this->rename($row, 'read', 'read_at');
                $this->rename($row, 'trunk', 'root_id');
                $this->rename($row, 'text', 'text_id');

                if ($row['read_at'] == 1) {
                    $row['read_at'] = $row['time'];
                }

                if ($row['read_at']) {
                    $this->timestampToDatetime($row['read_at']);
                } else {
                    $row['read_at'] = null;
                }

                if ($row['folder'] == Pm::INBOX) {
                    $row['user_id'] = $to;
                    $row['author_id'] = $from;
                } else {
                    $row['user_id'] = $from;
                    $row['author_id'] = $to;
                }

                $row['root_id'] = $row['user_id'] + $row['author_id'];

                unset($row['type'], $row['subject'], $row['time'], $row['from'], $row['to']);

                DB::table('pm')->insert($row);

                $bar->advance();
            }

            $this->fixSequence('pm');

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
     *  WYMAGA DODANIA DANYCH DO TABELI REPUTATION_TYPES
     * 100%
     */
    public function migrateReputation()
    {
        $this->info('Reputations...');

        $sql = DB::connection('mysql')
            ->table('reputation_activity')
            ->select(['reputation_activity.*', 'module_name AS activity_module_name'])
            ->leftJoin('page', 'page_id', '=', 'activity_page')
            ->leftJoin('module', 'module_id', '=', 'page_module')
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = $this->skipPrefix('activity_', (array) $row);

                $this->rename($row, 'reputation', 'type_id');
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'time', 'created_at');
                $this->rename($row, 'subject', 'excerpt');

                $this->timestampToDatetime($row['created_at']);
                $metadata = [];

                if ($row['url']) {
                    $row['url'] = $this->skipHost($row['url']);
                }

                if (empty($row['module_name'])) {
                    $metadata['microblog_id'] = $row['item'];
                } elseif ($row['module_name'] == 'forum') {
                    $metadata['post_id'] = $row['item'];
                }

                $row['metadata'] = json_encode($metadata);
                unset($row['enable'], $row['page'], $row['item'], $row['module_name']);

                $row['excerpt'] = str_limit($row['excerpt'], 250);
                $row['url'] = str_limit($row['url'], 250);

                DB::table('reputations')->insert($row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('reputations');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     */
    private function migrateForum()
    {
        $this->info('Forum...');

        DB::beginTransaction();

        try {
            $sql = DB::connection('mysql')
                ->table('forum')
                ->select([
                    'forum.*',
                    'page_subject AS forum_name',
                    'page_title AS forum_title',
                    'page_path AS forum_path',
                    'page_order AS forum_order',
                    'page_depth AS forum_depth'
                ])
                ->leftJoin('page', 'page_id', '=', 'forum_page')
                ->orderBy('page_matrix')
                ->get();

            $parentId = null;
            $groups = [];

            foreach ($sql as $row) {
                $row = $this->skipPrefix('forum_', (array) $row);

                $this->rename($row, 'lock', 'is_locked');
                $this->rename($row, 'prune', 'prune_days');
                $this->rename($row, 'path', 'slug');

                if ($row['depth'] == 1) {
                    $parentId = $row['id'];
                } else {
                    $row['parent_id'] = $parentId;
                }

                $row['enable_prune'] = $row['prune_days'] > 0;
                $groups[$row['page']] = $row['id'];

                $permissions = unserialize($row['permission']);

                unset($row['depth'], $row['permission'], $row['page']);

                DB::table('forums')->insert($row);

                foreach ($permissions as $groupId => $rowset) {
                    if ($groupId > 2) {
                        foreach ($rowset as $permissionId => $value) {
                            DB::table('forum_permissions')->insert([
                                'forum_id' => $row['id'],
                                'group_id' => $groupId,
                                'permission_id' => $permissionId,
                                'value' => $value
                            ]);
                        }
                    }
                }
            }

            $sql = DB::connection('mysql')->table('page_group')->whereIn('page_id', array_keys($groups))->get();

            foreach ($sql as $row) {
                if ($row->group_id > 2) {
                    DB::table('forum_access')->insert(['forum_id' => $groups[$row->page_id], 'group_id' => $row->group_id]);
                }
            }

            $sql = DB::connection('mysql')->table('forum_marking')->get();
            $bar = $this->output->createProgressBar(count($sql));

            foreach ($sql as $row) {
                $row = (array) $row;

                $this->rename($row, 'mark_time', 'marked_at');
                $this->timestampToDatetime($row['marked_at']);

                DB::table('forum_track')->insert($row);
                $bar->advance();
            }

            $bar->finish();

            $sql = DB::connection('mysql')->table('forum_reason')->get();

            foreach ($sql as $row) {
                $row = $this->skipPrefix('reason_', (array) $row);

                $this->rename($row, 'content', 'description');

                DB::table('forum_reasons')->insert($row);
            }

            $sql = DB::connection('mysql')->table('forum_order')->get();

            foreach ($sql as $row) {
                $row = $this->skipPrefix('order_', (array) $row);

                $this->rename($row, 'forum', 'forum_id');
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'hidden', 'is_hidden');
                $this->rename($row, 'value', 'order');

                DB::table('forum_orders')->insert($row);
            }

            $this->fixSequence(['forums', 'forum_permissions', 'forum_track', 'forum_reasons', 'forum_orders']);

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
     * 100%
     */
    public function migrateTopic()
    {
        $this->info('Topic...');

        $count = $this->count(['topic', 'topic_marking', 'topic_user']);
        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            DB::connection('mysql')
                ->table('topic')
                ->select([
                    'topic.*',
                    'page_subject AS topic_subject',
                    'page_path AS topic_path',
                    'p1.post_time AS topic_created_at',
                    'p2.post_time AS topic_updated_at',
                ])
                ->leftJoin('page', 'page_id', '=', 'topic_page')
                ->join('post AS p1', 'p1.post_id', '=', 'topic_first_post_id')
                ->join('post AS p2', 'p2.post_id', '=', 'topic_first_post_id')
                ->orderBy('topic_id')
                ->chunk(100000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = $this->skipPrefix('topic_', (array) $row);

                        $this->rename($row, 'forum', 'forum_id');
                        $this->rename($row, 'vote', 'score');
                        $this->rename($row, 'sticky', 'is_sticky');
                        $this->rename($row, 'announcement', 'is_announcement');
                        $this->rename($row, 'lock', 'is_locked');
                        $this->rename($row, 'poll', 'poll_id');
                        $this->rename($row, 'moved_id', 'prev_forum_id');
                        $this->rename($row, 'last_post_time', 'last_post_created_at');
                        $this->rename($row, 'path', 'slug');

                        $this->timestampToDatetime($row['last_post_created_at']);
                        $this->timestampToDatetime($row['created_at']);
                        $this->timestampToDatetime($row['updated_at']);

                        if ($row['delete']) {
                            $row['deleted_at'] = $row['updated_at'];
                        } else {
                            $row['deleted_at'] = null;
                        }

                        unset($row['solved'], $row['page'], $row['delete']);

                        $row['subject'] = htmlspecialchars_decode($row['subject']);
                        $row['slug'] = explode('-', $row['slug'])[1];

                        $this->setNullIfEmpty($row['poll_id']);

                        DB::table('topics')->insert($row);
                        $bar->advance();
                    }
                });

            $this->fixSequence('topics');

            DB::connection('mysql')->table('topic_marking')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = (array) $row;

                    $this->rename($row, 'mark_time', 'marked_at');
                    $this->timestampToDatetime($row['marked_at']);

                    DB::table('topic_track')->insert($row);
                    $bar->advance();
                }
            });

            $this->fixSequence('topic_track');

            DB::connection('mysql')->table('topic_user')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    DB::table('topic_users')->insert((array) $row);
                    $bar->advance();
                }
            });

            $sql = DB::connection('mysql')
                ->table('watch')
                ->select(['topic_id', 'user_id', 'watch_time'])
                ->join('topic', 'topic_page', '=', 'page_id')
                ->groupBy(['user_id', 'page_id', 'watch_module', 'watch_plugin'])
                ->get();

            foreach ($sql as $row) {
                $row = (array) $row;

                $this->rename($row, 'watch_time', 'created_at');

                if ($row['created_at']) {
                    $this->timestampToDatetime($row['created_at']);
                } else {
                    $row['created_at'] = null;
                }

                DB::table('topic_subscribers')->insert($row);
            }

            $this->fixSequence('topic_subscribers');

            DB::connection('mysql')
                ->table('page_tag')
                ->select(['topic_id', 'tag_id'])
                ->join('page', 'page.page_id', '=', 'page_tag.page_id')
                ->join('topic', 'topic_page', '=', 'page.page_id')
                ->chunk(50000, function ($sql) {
                    foreach ($sql as $row) {
                        $row = (array) $row;

                        DB::table('topic_tags')->insert($row);
                    }
                });

            $this->fixSequence('topic_tags');

            $bar->finish();
            DB::commit();

            DB::statement('update topics set rank = LEAST(1000, 200 * topics.score) + LEAST(1000, 100 * topics.replies) + LEAST(1000, 15 * topics.views) - (extract(epoch from now()) - extract(epoch from topics.last_post_created_at)) / 4500 - (extract(epoch from now()) - extract(epoch from topics.created_at)) / 1000');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     */
    public function migratePost()
    {
        $this->info('Post...');

        $count = $this->count(['post_text']);
        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            DB::connection('mysql')
                ->table('post')
                ->select(['post.*', 'text_content AS post_content'])
                ->join('post_text', 'text_id', '=', 'post_text')
                ->chunk(50000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = $this->skipPrefix('post_', (array) $row);

                        $this->rename($row, 'forum', 'forum_id');
                        $this->rename($row, 'topic', 'topic_id');
                        $this->rename($row, 'user', 'user_id');
                        $this->rename($row, 'username', 'user_name');
                        $this->rename($row, 'time', 'created_at');
                        $this->rename($row, 'edit_time', 'updated_at');
                        $this->rename($row, 'edit_user', 'editor_id');
                        $this->rename($row, 'vote', 'score');
                        $this->rename($row, 'content', 'text');

                        $this->timestampToDatetime($row['created_at']);

                        if ($row['updated_at']) {
                            $this->timestampToDatetime($row['updated_at']);
                        } else {
                            $row['updated_at'] = null;
                        }

                        if ($row['delete']) {
                            $row['deleted_at'] = $this->timestampToDatetime($row['delete_time']);
                        } else {
                            $row['deleted_at'] = null;
                        }

                        unset($row['enable_smilies'], $row['enable_html'], $row['delete'], $row['delete_user'], $row['delete_time']);

                        DB::table('posts')->insert($row);
                        $bar->advance();
                    }
                });

            $this->fixSequence('posts');

            DB::connection('mysql')->table('post_comment')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('comment_', (array) $row);

                    $this->rename($row, 'user', 'user_id');
                    $this->rename($row, 'time', 'created_at');
                    $this->rename($row, 'post', 'post_id');

                    $row['updated_at'] = $row['created_at'];
                    $this->timestampToDatetime($row['created_at']);
                    $this->timestampToDatetime($row['updated_at']);

                    DB::table('post_comments')->insert($row);
                    $bar->advance();
                }
            });

            $this->fixSequence('post_comments');

            DB::connection('mysql')->table('post_subscribe')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    DB::table('post_subscribers')->insert((array) $row);
                    $bar->advance();
                }
            });

            $this->fixSequence('post_subscribers');

            DB::connection('mysql')->table('post_vote')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('vote_', $row);

                    $this->rename($row, 'post', 'post_id');
                    $this->rename($row, 'user', 'user_id');
                    $this->rename($row, 'time', 'created_at');
                    $this->rename($row, 'forum', 'forum_id');

                    if ($row['created_at']) {
                        $this->timestampToDatetime($row['created_at']);
                    }

                    unset($row['value']);

                    DB::table('post_votes')->insert($row);
                    $bar->advance();
                }
            });

            $this->fixSequence('post_votes');

            DB::connection('mysql')->table('post_accept')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('accept_', (array) $row);

                    $this->rename($row, 'post', 'post_id');
                    $this->rename($row, 'topic', 'topic_id');
                    $this->rename($row, 'user', 'user_id');
                    $this->rename($row, 'time', 'created_at');

                    if (empty($row['created_at'])) {
                        $row['created_at'] = null;
                    } else {
                        $this->timestampToDatetime($row['created_at']);
                    }

                    DB::table('post_accepts')->insert($row);
                    $bar->advance();
                }
            });

            $this->fixSequence('post_accepts');

            DB::connection('mysql')->table('post_attachment')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('attachment_', (array) $row);

                    $this->rename($row, 'post', 'post_id');
                    $this->rename($row, 'time', 'created_at');

                    $this->timestampToDatetime($row['created_at']);
                    $row['updated_at'] = $row['created_at'];

                    unset($row['width'], $row['height']);

                    DB::table('post_attachments')->insert($row);
                    $bar->advance();
                }
            });

            $this->fixSequence('post_attachments');

            DB::connection('mysql')->table('post_text')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('text_', (array) $row);

                    $this->rename($row, 'post', 'post_id');
                    $this->rename($row, 'time', 'created_at');
                    $this->rename($row, 'content', 'text');
                    $this->rename($row, 'user', 'user_id');

                    $this->timestampToDatetime($row['created_at']);

                    DB::table('post_log')->insert($row);
                    $bar->advance();
                }
            });

            $this->fixSequence('post_log');

            $bar->finish();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        Post::where('user_id', 0)->update(['user_id' => null]);
        Post\Log::where('user_id', 0)->update(['user_id' => null]);

        $this->line('');
        $this->info('Done');
    }

    public function migrateTags()
    {
        $this->info('Tag...');

        $sql = DB::connection('mysql')->table('tag')->get();

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = (array) $row;

                DB::table('tags')->insert(['id' => $row['tag_id'], 'name' => $row['tag_text'], 'created_at' => $this->timestampToDatetime($row['tag_time'])]);
            }

            DB::commit();
            $this->fixSequence('tags');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     */
    public function migrateMicroblogs()
    {
        $this->info('Microblog...');

        $tables = ['microblog', 'microblog_image', 'microblog_vote', 'microblog_tag', 'microblog_discuss'];
        $count = $this->count($tables);
        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            DB::connection('mysql')
                ->table('microblog')
                ->chunk(50000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = $this->skipPrefix('microblog_', (array) $row);

                        $this->rename($row, 'parent', 'parent_id');
                        $this->rename($row, 'user', 'user_id');
                        $this->rename($row, 'time', 'created_at');
                        $this->rename($row, 'vote', 'votes');
                        $this->rename($row, 'sponsored', 'is_sponsored');

                        $this->timestampToDatetime($row['created_at']);
                        $row['updated_at'] = $row['created_at'];

                        $row['score'] = $row['score'] ?: 0;

                        unset($row['recall'], $row['cache'], $row['edit_time'], $row['edit_user'], $row['ip']);

                        DB::table('microblogs')->insert($row);
                        $bar->advance();
                    }
                });

            DB::connection('mysql')->table('microblog_discuss')->distinct()->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = (array) $row;

                    $this->rename($row, 'discuss_id', 'microblog_id');

                    DB::table('microblog_subscribers')->insert($row);
                    $bar->advance();
                }
            });

            DB::connection('mysql')->table('microblog_vote')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('vote_', (array) $row);

                    $this->rename($row, 'microblog', 'microblog_id');
                    $this->rename($row, 'user', 'user_id');
                    $this->rename($row, 'time', 'created_at');

                    $this->timestampToDatetime($row['created_at']);

                    DB::table('microblog_votes')->insert((array) $row);
                    $bar->advance();
                }
            });

            DB::connection('mysql')->table('microblog_image')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $media = json_encode(['image' => [$row->image_file]]);

                    DB::table('microblogs')->where('id', $row->image_microblog)->update(['media' => $media]);
                    $bar->advance();
                }
            });

            $sql = DB::connection('mysql')
                ->table('microblog_tag')
                ->select(['microblog_id', 'tag_name', 'tag_text', 'tag.tag_id', 'microblog_parent'])
                ->join('microblog', 'microblog_id', '=', 'tag_microblog')
                ->leftJoin('tag', 'tag_text', '=', 'tag_name')
                ->get();

            foreach ($sql as $row) {
                $row = (array) $row;

                $tagId = DB::table('tags')->select(['id'])->where('name', $row['tag_name'])->value('id');
                if (!$tagId) {
                    $tagId = DB::table('tags')->insertGetId(['name' => $row['tag_name']]);
                }

                DB::table('microblog_tags')->insert(['tag_id' => $tagId, 'microblog_id' => $row['microblog_id']]);
            }

            $bar->finish();

            $this->fixSequence(['microblogs', 'microblog_subscribers', 'microblog_votes', 'microblog_tags']);
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
     * @todo usunac zduplikowane firmy
     */
    public function migrateFirms()
    {
        $this->info('Firms...');

        $firms = DB::connection('mysql')->table('firm')
                    ->select([
                        'firm.*',
                        'page_subject AS firm_name',
                        'page_time AS firm_created_at',
                        'page_edit_time AS firm_updated_at',
                        'page_delete AS firm_deleted_at'
                    ])
                    ->join('page', 'page_id', '=', 'firm_page')
                    ->get();
        $bar = $this->output->createProgressBar(count($firms));

        DB::beginTransaction();

        try {
            foreach ($firms as $row) {
                $row = $this->skipPrefix('firm_', (array) $row);

                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'agency', 'is_agency');
                $this->rename($row, 'country', 'country_id');

                $this->timestampToDatetime($row['created_at']);
                $this->timestampToDatetime($row['updated_at']);

                $this->setNullIfEmpty($row['deleted_at']);
                if (!empty($row['deleted_at'])) {
                    $row['deleted_at'] = $row['updated_at'];
                }

                unset($row['phone'], $row['page'], $row['technology']);
                $row['name'] = htmlspecialchars_decode($row['name']);

                DB::table('firms')->insert($row);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();

            $this->fixSequence('firms');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->info('Firm benefits...');

        $benefits = DB::connection('mysql')->table('firm_benefit')->get();
        $bar = $this->output->createProgressBar(count($benefits));

        DB::beginTransaction();

        try {
            foreach ($benefits as $row) {
                $row = $this->skipPrefix('benefit_', (array) $row);

                $this->rename($row, 'firm', 'firm_id');
                $row['name'] = htmlspecialchars_decode($row['name']);

                DB::table('firm_benefits')->insert($row);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();

            $this->fixSequence('firm_benefits');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * 100%
     */
    public function migrateJobs()
    {
        $this->info('Jobs...');

        $jobs = DB::connection('mysql')
            ->table('job')
            ->select([
                'job.*',
                'page_subject AS job_title',
                'page_time AS job_created_at',
                'page_edit_time AS job_updated_at',
                'page_delete AS job_deleted_at',
                'page_views AS job_visits'
            ])
            ->join('page', 'page_id', '=', 'job_page')
            ->get();
        $bar = $this->output->createProgressBar(count($jobs));

        DB::beginTransaction();

        $stripPar = function ($text) {
            return str_replace(['<p>', '</p>'], ['', '<br><br>'], $text);
        };

        try {
            foreach ($jobs as $row) {
                $row = $this->skipPrefix('job_', (array) $row);

                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'firm', 'firm_id');
                $this->rename($row, 'remote', 'is_remote');
                $this->rename($row, 'country', 'country_id');
                $this->rename($row, 'salary_currency', 'currency_id');
                $this->rename($row, 'salary_duration', 'rate_id');
                $this->rename($row, 'type', 'employment_id');
                $this->rename($row, 'apply', 'enable_apply');
                $this->rename($row, 'deadline', 'deadline_at');
                $this->rename($row, 'order', 'rank');
                $this->rename($row, 'visits', 'views');

                $row['title'] = htmlspecialchars_decode($row['title']);
                $row['slug'] = str_slug($row['title'], '_');

                $this->timestampToDatetime($row['created_at']);
                $this->timestampToDatetime($row['updated_at']);
                $this->timestampToDatetime($row['deadline_at']);

                $this->setNullIfEmpty($row['deleted_at']);
                if (!empty($row['deleted_at'])) {
                    $row['deleted_at'] = $row['updated_at'];
                }

                unset($row['incognito'], $row['page'], $row['searchable']);

                if (!empty($row['requirements'])) {
                    $row['description'] .= "\n\n<h2>Wymagania</h2>\n\n" . $row['requirements'];
                }

                $row['description'] = $stripPar($row['description']);
                $row['requirements'] = null;
                $this->setNullIfEmpty($row['recruitment']);

                DB::table('jobs')->insert($row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('jobs');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Job locations...');

        $locations = DB::connection('mysql')
            ->table('job_location')
            ->get();

        $bar = $this->output->createProgressBar(count($locations));

        DB::beginTransaction();
        $replace = ['Warsaw' => 'Warszawa', 'Krakow' => 'Kraków', 'Wroclaw' => 'Wrocław', 'Lodz' => 'Łódź', 'Warszawa (ścisłe centrum)' => 'Warszawa', 'Waraszawa' => 'Warszawa'];

        try {
            foreach ($locations as $row) {
                $row = $this->skipPrefix('location_', (array) $row);
                $this->rename($row, 'job', 'job_id');

                $row['city'] = str_ireplace(array_keys($replace), array_values($replace), $row['city']);

                DB::table('job_locations')->insert($row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('job_locations');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Tags...');

        $tags = DB::connection('mysql')
            ->table('page_tag')
            ->select(['job_id', 'tag_id'])
            ->join('page', 'page.page_id', '=', 'page_tag.page_id')
            ->join('job', 'job_page', '=', 'page.page_id')
            ->get();

        $bar = $this->output->createProgressBar(count($tags));

        DB::beginTransaction();

        try {
            foreach ($tags as $row) {
                $row = (array) $row;
                $row['priority'] = 1;

                DB::table('job_tags')->insert($row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('job_tags');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Candidates...');

        $candidates = DB::connection('mysql')
            ->table('job_apply')
            ->get();

        $bar = $this->output->createProgressBar(count($candidates));

        DB::beginTransaction();

        try {
            foreach ($candidates as $row) {
                $row = $this->skipPrefix('apply_', (array) $row);

                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'time', 'created_at');
                $this->rename($row, 'session', 'session_id');
                $this->rename($row, 'job', 'job_id');

                $this->timestampToDatetime($row['created_at']);
                $this->setNullIfEmpty($row['user_id']);
                unset($row['ip']);

                DB::table('job_applications')->insert((array) $row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('job_applications');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Subscribers...');

        $subscribers = DB::connection('mysql')
            ->table('watch')
            ->select(['watch.*', 'job_id'])
            ->join('job', 'job_page', '=', 'page_id')
            ->whereNull('watch_plugin')
            ->get();

        $bar = $this->output->createProgressBar(count($subscribers));

        DB::beginTransaction();

        try {
            foreach ($subscribers as $row) {
                $row = (array) $row;
                $this->rename($row, 'watch_time', 'created_at');

                $this->timestampToDatetime($row['created_at']);
                unset($row['page_id'], $row['watch_page'], $row['watch_module'], $row['watch_plugin']);

                $this->setNullIfEmpty($row['created_at']);

                DB::table('job_subscribers')->insert((array) $row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('job_subscribers');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Referers...');

        $referers = DB::connection('mysql')
            ->table('job_referer')
            ->get();

        $bar = $this->output->createProgressBar(count($referers));

        DB::beginTransaction();

        try {
            foreach ($referers as $row) {
                $row = $this->skipPrefix('referer_', (array) $row);
                $this->rename($row, 'job', 'job_id');

                if (strlen($row['url']) <= 250) {
                    DB::table('job_referers')->insert((array) $row);
                }

                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('job_referers');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->info('Done');
    }

    /**
     * 100%
     */
    public function migratePastebin()
    {
        $pastebin = DB::connection('mysql')
            ->table('pastebin')
            ->select(['pastebin.*', 'user_name AS pastebin_user_name'])
            ->leftJoin('user', 'user_id', '=', 'pastebin_user')
            ->get();

        $bar = $this->output->createProgressBar(count($pastebin));

        DB::beginTransaction();

        try {
            foreach ($pastebin as $row) {
                $row = $this->skipPrefix('pastebin_', (array) $row);
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'time', 'created_at');
                $this->rename($row, 'expire', 'expires');
                $this->rename($row, 'content', 'text');
                $this->rename($row, 'syntax', 'mode');

                $row['title'] = $row['user_id'] > 0 ? $row['user_name'] : $row['username'];

                $this->setNullIfEmpty($row['expires']);
                if (!empty($row['expires'])) {
                    $row['expires'] = round(($row['expires'] - $row['created_at']) / 60 / 60);
                }

                $this->timestampToDatetime($row['created_at']);
                $this->setNullIfEmpty($row['user_id']);

                if (empty($row['title'])) {
                    $row['title'] = 'Anonim';
                }

                unset($row['username'], $row['cache'], $row['prev'], $row['user_name']);

                DB::table('pastebin')->insert((array) $row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();

            $this->fixSequence('pastebin');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->info('Done');
    }

    /**
     * 100%
     */
    public function migratePoll()
    {
        $polls = DB::connection('mysql')
            ->table('poll')
            ->get();

        $bar = $this->output->createProgressBar(count($polls));

        DB::beginTransaction();

        try {
            foreach ($polls as $row) {
                $row = $this->skipPrefix('poll_', (array) $row);
                $this->rename($row, 'start', 'created_at');
                $this->rename($row, 'max_item', 'max_items');

                $this->timestampToDatetime($row['created_at']);
                $row['updated_at'] = $row['created_at'];

                unset($row['user'], $row['votes'], $row['enable']);

                DB::table('polls')->insert((array) $row);
                $bar->advance();
            }

            $bar->finish();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Poll items...');

        $items = DB::connection('mysql')
            ->table('poll_item')
            ->get();

        $bar = $this->output->createProgressBar(count($items));
        $idMappings = [];

        DB::beginTransaction();

        try {
            foreach ($items as $row) {
                $row = $this->skipPrefix('item_', (array) $row);
                $this->rename($row, 'poll', 'poll_id');

                if (!isset($idMappings[$row['poll_id']])) {
                    $idMappings[$row['poll_id']] = [];
                }

                $oldId = $row['id'];
                unset($row['id']);

                $id = DB::table('poll_items')->insertGetId((array) $row);
                $idMappings[$row['poll_id']][$oldId] = $id;

                $bar->advance();
            }

            $bar->finish();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Poll votes...');

        $items = DB::connection('mysql')
            ->table('poll_vote')
            ->get();

        $bar = $this->output->createProgressBar(count($items));

        DB::beginTransaction();

        try {
            foreach ($items as $row) {
                $row = $this->skipPrefix('vote_', (array) $row);
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'item', 'item_id');
                $this->rename($row, 'poll', 'poll_id');

                $this->setNullIfEmpty($row['user_id']);

                if (!empty($idMappings[$row['poll_id']][$row['item_id']])) {
                    $row['item_id'] = $idMappings[$row['poll_id']][$row['item_id']];

                    DB::table('poll_votes')->insert((array)$row);
                }
                $bar->advance();
            }

            $bar->finish();

            DB::table('poll_votes')->update(['created_at' => null]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }


        $this->fixSequence(['polls', 'poll_items', 'poll_votes']);

        $this->info('Done');
    }

    /**
     * 100%
     */
    public function migrateWiki()
    {
        $wiki = DB::connection('mysql')
            ->table('page')
            ->select(['page.*', 'page_text.text_content AS page_content', 'location_text AS page_location'])
            ->leftJoin('page_text', 'text_id', '=', 'page_text')
            ->join('location', 'location_page', '=', 'page_id')
            ->where('page_module', '=', 3)
            ->orderBy('page_order')
            ->get();

        $bar = $this->output->createProgressBar(count($wiki));

        DB::beginTransaction();
        $mapping = [];

        try {
            foreach ($wiki as $row) {
                $row = (array) $row;

                if (is_null($row['page_text']) || !isset($mapping[$row['page_text']])) {
                    $mapping[$row['page_text']] = $row['page_id'];
                    $textId = $row['page_text'];

                    $row = $this->skipPrefix('page_', $row);
                    $this->rename($row, 'title', 'long_title');
                    $this->rename($row, 'subject', 'title');
                    $this->rename($row, 'path', 'slug');
                    $this->rename($row, 'time', 'created_at');
                    $this->rename($row, 'edit_time', 'updated_at');
                    $this->rename($row, 'content', 'text');

                    $this->timestampToDatetime($row['created_at']);
                    $this->timestampToDatetime($row['updated_at']);

                    if ($row['delete']) {
                        $row['deleted_at'] = $row['updated_at'];
                    }

                    $parentId = $row['parent'];
                    $path = $row['location'];

                    $row['template'] = str_replace(['wikiView.php', 'wikiCategory.php', 'help.php', 'helpView.php', 'documentView.php', 'wikiEmpty.php'], ['show', 'category', 'help.home', 'help.show', 'show', 'show'], $row['template']);

                    $row['title'] = htmlspecialchars_decode($row['title']);
                    $row['long_title'] = htmlspecialchars_decode($row['long_title']);

                    unset($row['parent'], $row['location'], $row['module'], $row['connector'], $row['depth'], $row['order'], $row['matrix'], $row['content'], $row['publish'], $row['published'], $row['unpublished'], $row['richtext'], $row['cache'], $row['tags'], $row['delete']);
                    DB::table('wiki_pages')->insert((array)$row);

                    DB::table('wiki_paths')->insert(['path_id' => $row['id'], 'wiki_id' => $row['id'], 'parent_id' => $parentId, 'path' => $path]);

                    $texts = DB::connection('mysql')
                        ->table('page_version')
                        ->select(['page_text.*'])
                        ->join('page_text', 'page_text.text_id', '=', 'page_version.text_id')
                        ->where('page_version.page_id', $row['id'])
                        ->orderBy('text_id')
                        ->get();

                    foreach ($texts as $text) {
                        $text = $this->skipPrefix('text_', (array) $text);
                        $this->rename($text, 'content', 'text');
                        $this->rename($text, 'time', 'created_at');
                        $this->rename($text, 'log', 'comment');
                        $this->rename($text, 'user', 'user_id');
                        $this->rename($text, 'restored', 'is_restored');

                        $this->timestampToDatetime($text['created_at']);

                        $text['wiki_id'] = $row['id'];
                        $text['title'] = $row['title'];

                        unset($text['id']);
                        DB::table('wiki_log')->insert($text);
                    }

                    $attachments = DB::connection('mysql')
                        ->table('page_attachment')
                        ->select(['attachment.*'])
                        ->join('attachment', 'attachment.attachment_id', '=', 'page_attachment.attachment_id')
                        ->where('text_id', $textId)
                        ->groupBy(['text_id', 'page_attachment.attachment_id'])
                        ->get();

                    foreach ($attachments as $attachment) {
                        $attachment = $this->skipPrefix('attachment_', (array) $attachment);
                        $this->rename($attachment, 'time', 'created_at');

                        $this->timestampToDatetime($attachment['created_at']);

                        $attachment['wiki_id'] = $row['id'];
                        unset($attachment['id'], $attachment['user'], $attachment['image'], $attachment['width'], $attachment['height']);

                        DB::table('wiki_attachments')->insert($attachment);
                    }
                } else {
                    DB::table('wiki_paths')->insert(['path_id' => $row['page_id'], 'wiki_id' => $mapping[$row['page_text']], 'parent_id' => $row['page_parent'], 'path' => $row['page_location']]);
                }

                $bar->advance();
            }

            $bar->finish();

            $this->line('');
            $this->line('Links');

            $accessor = DB::connection('mysql')
                ->table('accessor')
                ->join('location', 'location_page', '=', 'accessor_to')
                ->get();

            foreach ($accessor as $row) {
                $row = (array) $row;
                DB::table('wiki_links')->insert([
                    'path_id' => $row['accessor_from'],
                    'ref_id' => $row['accessor_to'],
                    'path' => $row['location_text']
                ]);
            }

            $broken = DB::connection('mysql')
                ->table('broken')
                ->get();

            foreach ($broken as $row) {
                $row = (array) $row;
                DB::table('wiki_links')->insert(['path_id' => $row['broken_from'], 'path'=> $row['broken_path']]);
            }

            $this->line('');
            $this->line('Authors');

            $author = DB::connection('mysql')
                ->table('page_author')
                ->get();

            foreach ($author as $row) {
                $row = $this->skipPrefix('author_', (array) $row);
                $this->rename($row, 'page', 'wiki_id');
                $this->rename($row, 'user', 'user_id');

                DB::table('wiki_authors')->insert($row);
            }

            $this->line('');
            $this->line('Rates');

            $rates = DB::connection('mysql')
                ->table('page_rate')
                ->get();

            foreach ($rates as $row) {
                $row = $this->skipPrefix('rate_', (array) $row);
                $this->rename($row, 'page', 'wiki_id');
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'time', 'created_at');

                $this->timestampToDatetime($row['created_at']);

                DB::table('wiki_rates')->insert($row);
            }

            $this->line('');
            $this->line('Subsribers');

            $sql = DB::connection('mysql')
                ->table('watch')
                ->select(['page_id', 'user_id', 'watch_time'])
                ->where('watch_module', 3)
                ->groupBy(['page_id', 'user_id'])
                ->get();

            foreach ($sql as $row) {
                $row = (array) $row;

                $this->rename($row, 'watch_time', 'created_at');
                $this->rename($row, 'page_id', 'wiki_id');

                if ($row['created_at']) {
                    $this->timestampToDatetime($row['created_at']);
                } else {
                    $row['created_at'] = null;
                }

                DB::table('wiki_subscribers')->insert($row);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->fixSequence(['wiki_pages', 'wiki_log', 'wiki_rates', 'wiki_subscribers', 'wiki_authors', 'wiki_links', 'wiki_attachments']);
        DB::unprepared("SELECT setval('wiki_paths_path_id_seq', (SELECT MAX(path_id) FROM wiki_paths))");

        $this->info('Done');
    }

    public function fillPagesTable()
    {
        $sql = DB::table('wiki')->get();
        $bar = $this->output->createProgressBar(count($sql));

        $this->line('Wiki pages...');

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = (array) $row;

                DB::table('pages')->insert([
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'title' => $row['title'],
                    'path' => '/' . $row['path'],
                    'content_id' => $row['id'],
                    'content_type' => 'Coyote\Wiki',
                    'allow_sitemap' => 1
                ]);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->line('Topic pages...');

        $sql = DB::table('topics')
            ->selectRaw('DISTINCT ON (topics.id) topics.*, forum_access.group_id, f.slug AS f_slug, p.slug AS p_slug')
            ->leftJoin('forum_access', 'forum_access.forum_id', '=', 'topics.forum_id')
            ->join('forums AS f', 'f.id', '=', 'topics.forum_id')
            ->leftJoin('forums AS p', 'p.id', '=', 'f.parent_id')
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = (array) $row;

                DB::table('pages')->insert([
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'title' => $row['subject'],
                    'path' => route('forum.topic', [implode('/', array_filter([$row['p_slug'], $row['f_slug']])), $row['id'], $row['slug']], false),
                    'content_id' => $row['id'],
                    'content_type' => 'Coyote\Topic',
                    'allow_sitemap' => empty($row['group_id'])
                ]);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->line('Microblog pages...');

        $sql = DB::table('microblogs')
            ->whereNull('parent_id')
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = (array) $row;

                $parser = app('parser.microblog');

                DB::table('pages')->insert([
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'title' => excerpt($parser->parse($row['text'])),
                    'path' => route('microblog.view', [$row['id']], false),
                    'content_id' => $row['id'],
                    'content_type' => 'Coyote\Topic',
                    'allow_sitemap' => empty($row['group_id'])
                ]);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->line('Job pages...');

        $sql = DB::table('jobs')
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = (array) $row;

                DB::table('pages')->insert([
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'title' => $row['title'],
                    'path' => route('job.offer', [$row['id'], $row['slug']], false),
                    'content_id' => $row['id'],
                    'content_type' => 'Coyote\Job',
                    'allow_sitemap' => 1
                ]);

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->fixSequence(['pages']);
    }

    /**
     *
     */
    public function migratePageVisits()
    {
        $this->info('Page visits...');

        $count = DB::connection('mysql')->table('page_track')->count();
        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            DB::connection('mysql')
                ->table('page_track')
                ->select(['page_track.*', 'topic_id AS track_topic_id'])
                ->join('topic', 'topic_page', '=', 'track_page')
                ->chunk(50000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = (array) $row;

                        $row = $this->skipPrefix('track_', $row);

                        $this->rename($row, 'user', 'user_id');
                        $this->rename($row, 'time', 'created_at');
                        $this->rename($row, 'last_visit', 'updated_at');
                        $this->rename($row, 'count', 'visits');

                        $this->timestampToDatetime($row['created_at']);
                        $this->timestampToDatetime($row['updated_at']);

                        $page = DB::table('pages')->where('content_id', $row['topic_id'])->where('content_type', 'Coyote\Topic')->first();

                        if (empty($page)) {
                            continue;
                        }

                        unset($row['page'], $row['topic_id']);
                        $row['page_id'] = $page->id;

                        DB::table('page_visits')->insert($row);
                        $bar->advance();
                    }
                });


            /////////////////////////////////////////

            DB::connection('mysql')
                ->table('page_track')
                ->select(['page_track.*'])
                ->join('page', 'page_id', '=', 'track_page')
                ->where('page_module', 3)
                ->chunk(50000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = (array) $row;

                        $row = $this->skipPrefix('track_', $row);

                        $this->rename($row, 'user', 'user_id');
                        $this->rename($row, 'time', 'created_at');
                        $this->rename($row, 'last_visit', 'updated_at');
                        $this->rename($row, 'count', 'visits');

                        $this->timestampToDatetime($row['created_at']);
                        $this->timestampToDatetime($row['updated_at']);

                        $page = DB::table('pages')->where('content_id', $row['page'])->where('content_type', 'Coyote\Wiki')->first();

                        if (empty($page)) {
                            continue;
                        }
                        $row['page_id'] = $page->id;

                        unset($row['page']);
                        DB::table('page_visits')->insert($row);
                        $bar->advance();
                    }
                });

            /////////////////////////////////////////

            DB::connection('mysql')
                ->table('page_track')
                ->select(['page_track.*', 'job_id AS track_job_id'])
                ->join('job', 'job_page', '=', 'track_page')
                ->join('location', 'location_page', '=', 'track_page')
                ->chunk(50000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = (array) $row;

                        $row = $this->skipPrefix('track_', $row);

                        $this->rename($row, 'user', 'user_id');
                        $this->rename($row, 'time', 'created_at');
                        $this->rename($row, 'last_visit', 'updated_at');
                        $this->rename($row, 'count', 'visits');

                        $this->timestampToDatetime($row['created_at']);
                        $this->timestampToDatetime($row['updated_at']);

                        $page = DB::table('pages')->where('content_id', $row['job_id'])->where('content_type', 'Coyote\Job')->first();

                        if (empty($page)) {
                            continue;
                        }
                        $row['page_id'] = $page->id;

                        unset($row['page'], $row['job_id']);
                        DB::table('page_visits')->insert($row);
                        $bar->advance();
                    }
                });


            DB::commit();
            $bar->finish();

            $this->fixSequence('page_visits');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    public function migrateBan()
    {
        $sql = DB::connection('mysql')->table('ban')
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = (array) $row;

                $row = $this->skipPrefix('ban_', $row);
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'expire', 'expire_at');
                $this->rename($row, 'creator', 'moderator_id');

                if ($row['expire_at']) {
                    $this->timestampToDatetime($row['expire_at']);
                } else {
                    $row['expire_at'] = null;
                }

                $this->setNullIfEmpty($row['user_id']);

                unset($row['flood']);

                DB::table('firewall')->insert($row);
                $bar->advance();
            }

            DB::statement('UPDATE users SET is_blocked = 1 WHERE id IN(SELECT user_id FROM firewall WHERE user_id IS NOT NULL GROUP BY user_id)');

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->fixSequence(['firewall']);
    }

    public function migrateRedirect()
    {
        $sql = DB::connection('mysql')->table('redirect')
            ->select(['redirect.*'])
            ->join('page', 'page_id', '=', 'redirect_page')
            ->where('page_module', 3)
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                DB::table('wiki_redirects')->insert(['path_id' => $row->redirect_page, 'path' => $row->redirect_path]);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->fixSequence(['wiki_redirects']);
    }

    public function migrateLogs()
    {
        $count = DB::connection('mysql')->table('log')->count();
        $mongo = DB::connection('mongodb')->collection('streams');

        $bar = $this->output->createProgressBar($count);

        $parseJson = function ($row) {
            $object = ['objectType' => 'topic', 'id' => $row['topic_id']];

            if (!empty($row['location_text'])) {
                $object = array_merge($object, [
                    'url' => '/' . str_replace('@forum', 'Forum', $row['location_text']),
                    'displayName' => htmlspecialchars_decode($row['page_subject'])
                ]);
            } elseif (!empty($row['meta'])) {
                $json = json_decode($row['meta'], true);

                $link = $json['subject'];

                if (empty($link)) {
                    return $object;
                }

                $link = mb_convert_encoding($link, 'HTML-ENTITIES', "UTF-8");

                $dom = new \DOMDocument('1.0', 'UTF-8');
                $dom->loadHTML($link);
                $nodes = $dom->getElementsByTagName('a');

                foreach ($nodes as $node) {
                    $object['displayName'] = $node->nodeValue;
                    $object['url'] = '/Forum' . parse_url($node->getAttribute('href'), PHP_URL_PATH);
                }
            } else {
                $object['displayName'] = $row['message'];
            }

            return $object;
        };

        DB::connection('mysql')->table('log')
            ->select(['log.*', 'page_module AS log_page_module', 'page_subject AS log_page_subject', 'page_path AS log_page_path', 'location_text AS log_location_text', 'user_id AS log_user_id', 'user_name AS log_user_name', 'user_photo AS log_user_photo', 'topic_id AS log_topic_id'])
            ->leftJoin('user', 'user_id', '=', 'log_user')
            ->leftJoin('page', 'page_id', '=', 'log_page')
            ->leftJoin('location', 'location_page', '=', 'log_page')
            ->leftJoin('topic', 'topic_page', '=', 'log_page')
            ->orderBy('log_id')
            ->chunk(50000, function ($result) use ($mongo, $bar, $parseJson) {
                foreach ($result as $row) {
                    $row = (array) $row;

                    $row = $this->skipPrefix('log_', $row);
                    $this->timestampToDatetime($row['time']);

                    $json = ['ip' => $row['ip'], 'created_at' => $row['time']];

                    if ($row['user_id']) {
                        $json['actor'] = [
                            'id' => $row['user_id'],
                            'url' => '/Profile/' . $row['user_id'],
                            'displayName' => $row['user_name'],
                            'objectType' => 'actor',
                            'image' => $row['user_photo']
                        ];
                    }

                    switch ($row['type']) {
                        // logowanie
                        case 65534:
                            $json['verb'] = 'login';
                            break;

                        case 65533: // nieudane logowanie
                            if (!empty($row['message'])) {
                                $json['verb'] = 'throttle';
                                $json['login'] = $row['message'];
                            }

                            break;

                        case 65532: // logowanie do pa
                            break;

                        case 65531: // nieudane logowanie do pa
                            break;

                        case 65530: // rejestracja
                            $json['verb'] = 'create';
                            $json['object'] = ['objectType' => 'person', 'displayName' => $row['message']];
                            break;

                        case 65529: // potwierdzenie konta
                            $json['verb'] = 'confirm';
                            $json['object'] = ['objectType' => 'person', 'displayName' => $row['message']];

                            if (empty($row['user_id'])) {
                                $u = DB::connection('mysql')->table('user')->where('user_name', $row['message'])->first();

                                $json['actor'] = [
                                    'displayName' => $row['message'],
                                    'objectType' => 'actor'
                                ];

                                if ($u) {
                                    $json['actor']['id'] = $u->user_id;
                                    $json['actor']['url'] = '/Profile/' . $u->user_id;
                                }
                            }
                            break;

                        case 65520: // uaktualnienie konta
                            preg_match('~\#(\d+)~', $row['message'], $match);
                            if (empty($match[1])) {
                                continue;
                            }
                            $userId = (int) $match[1];

                            preg_match('~\(.*?\)~', $row['message'], $match);
                            $userName = '';

                            if (!empty($match[1])) {
                                $userName = $match[1];
                            }

                            $json['verb'] = 'update';
                            $json['object'] = ['objectType' => 'person', 'displayName' => $userName, 'id' => $userId, 'url' => '/Profile/' . $userId];
                            break;

                        case 65519: // uaktualnienie bana
                            $object = ['objectType' => 'firewall'];

                            if ($row['message'] == 'Dodano nową blokadę') {
                                $json['verb'] = 'create';
                            } else {
                                $json['verb'] = 'update';
                            }

                            $json['object'] = $object;
                            break;

                        case 65528: // dodanie strony
                            if ($row['page_module'] == 3 || $row['page_module'] == 11) {
                                if ($row['page_module'] == 3) {
                                    $first = DB::connection('mysql')->table('page_version')
                                        ->select(['text_user'])
                                        ->join('page_text', 'page_text.text_id', '=', 'page_version.text_id')
                                        ->where('page_version.page_id', $row['page'])
                                        ->orderBy('page_version.text_id')
                                        ->first();

                                    if (empty($first)) {
                                        $json['verb'] = 'create';
                                    } else {
                                        $json['verb'] = $first->text_user == $row['user_id'] ? 'create' : 'update';
                                    }

                                    $object = [
                                        'objectType' => 'wiki',
                                        'id' => $row['page'],
                                        'url' => route('wiki.show', [$row['location_text']], false),
                                        'displayName' => $row['message']
                                    ];

                                    $json['object'] = $object;
                                } else {
                                    $job = DB::connection('mysql')->table('job')->where('job_page', $row['page'])->first();

                                    if (!empty($job)) {
                                        $json['verb'] = $job->job_user == $row['user_id'] ? 'create' : 'update';
                                        $object = [
                                            'objectType' => 'job',
                                            'id' => $job->job_id,
                                            'url' => route('job.offer', [$job->job_id, $row['page_path']], false),
                                            'displayName' => htmlspecialchars_decode($row['page_subject'])
                                        ];

                                        $json['object'] = $object;
                                    }
                                }
                            }

                            break;

                        case 65527: // usuniecie strony
                            if ($row['page_module'] == 3) {
                                $json['verb'] = 'delete';
                                $object = [
                                    'objectType' => 'wiki',
                                    'id' => $row['page'],
                                    'url' => route('wiki.show', [$row['location_text']], false),
                                    'displayName' => htmlspecialchars_decode($row['page_subject'])
                                ];

                                $json['object'] = $object;
                            } elseif ($row['page_module'] == 11) {
                                $job = DB::connection('mysql')->table('job')->where('job_page', $row['page'])->first();

                                if (!empty($job)) {
                                    $json['verb'] = 'delete';
                                    $object = [
                                        'objectType' => 'job',
                                        'id' => $job->job_id,
                                        'url' => route('job.offer', [$job->job_id, $row['page_path']], false),
                                        'displayName' => htmlspecialchars_decode($row['page_subject'])
                                    ];

                                    $json['object'] = $object;
                                }
                            }

                            break;

                        case 'Utworzenie nowego wpisu mikroblogu':
                            preg_match('~\#(\d+)~', $row['message'], $match);
                            $microblogId = (int) $match[1];

                            $json['verb'] = 'create';
                            $json['object'] = [
                                'objectType' => 'microblog',
                                'id' => $microblogId,
                                'url' => route('microblog.view', [$microblogId], false)
                            ];

                            break;

                        case 'Edycja wpisu na mikroblogu':
                            preg_match('~\#(\d+)~', $row['message'], $match);
                            $microblogId = (int) $match[1];

                            $json['verb'] = 'update';
                            $json['object'] = [
                                'objectType' => 'microblog',
                                'id' => $microblogId,
                                'url' => route('microblog.view', [$microblogId], false)
                            ];

                            break;

                        case 'Usunięcie wpisu z mikroblogu':
                            preg_match('~\#(\d+)~', $row['message'], $match);
                            $microblogId = (int) $match[1];

                            $json['verb'] = 'delete';
                            $json['object'] = [
                                'objectType' => 'microblog',
                                'id' => $microblogId,
                                'url' => route('microblog.view', [$microblogId], false)
                            ];

                            break;

                        case 'Utworzenie nowego wątku':
                            $json['verb'] = 'create';
                            $object = $parseJson($row);

                            $json['object'] = $object;
                            break;

                        case 'Zablokowanie wątku':
                            $json['verb'] = 'lock';
                            $json['object'] = $parseJson($row);
                            break;

                        case 'Odblokowanie wątku':
                            $json['verb'] = 'unlock';
                            $json['object'] = $parseJson($row);
                            break;

                        case 'Przeniesienie wątku':
                            if (!empty($row['meta'])) {
                                $json['verb'] = 'move';

                                $meta = json_decode($row['meta'], true);

                                $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                                $dom = new \DOMDocument('1.0', 'UTF-8');
                                $dom->loadHTML($meta['subject']);
                                $nodes = $dom->getElementsByTagName('a');

                                $object = ['objectType' => 'topic', 'id' => $row['topic_id']];

                                foreach ($nodes as $node) {
                                    $object['displayName'] = $node->nodeValue;
                                    $object['url'] = '/Forum' . parse_url($node->getAttribute('href'), PHP_URL_PATH);
                                }

                                $json['object'] = $object;
                                $json['object']['reasonName'] = $meta['reason'];

                                $meta['path'] = mb_convert_encoding($meta['path'], 'HTML-ENTITIES', "UTF-8");

                                $target = [];
                                $dom = new \DOMDocument('1.0', 'UTF-8');
                                $dom->loadHTML($meta['path']);
                                $nodes = $dom->getElementsByTagName('a');

                                foreach ($nodes as $node) {
                                    $target['displayName'] = $node->nodeValue;
                                    $target['url'] = '/Forum' . parse_url($node->getAttribute('href'), PHP_URL_PATH);
                                }

                                $json['target'] = $target;
                            }

                            break;

                        case 'Edycja postu':
                            $json['verb'] = 'update';

                            $object = ['objectType' => 'post'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            if (empty($row['meta'])) {
                                preg_match('~\#(\d+)~', $row['message'], $match);
                                $postId = $match[1];

                                $object['id'] = (int) $postId;

                                if (!empty($row['location_text'])) {
                                    $object['url'] = '/' . str_replace('@forum', 'Forum', $row['location_text']) . '?p=' . $postId . '#id' . $postId;
                                    $target['url'] = '/' . str_replace('@forum', 'Forum', $row['location_text']);
                                }
                            } else {
                                $postId = $row['index'];
                                $meta = json_decode($row['meta'], true);

                                $object['id'] = (int) $postId;

                                $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                                $dom = new \DOMDocument('1.0', 'UTF-8');
                                $dom->loadHTML($meta['subject']);
                                $nodes = $dom->getElementsByTagName('a');

                                foreach ($nodes as $node) {
                                    $target['displayName'] = $node->nodeValue;
                                    $parseUrl = parse_url($node->getAttribute('href'));
                                    $object['url'] = '/Forum' . $parseUrl['path'] . '?' . $parseUrl['query'] . '#' . $parseUrl['fragment'];
                                    $target['url'] = '/Forum' . $parseUrl['path'];
                                }
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Usunięcie postu':
                            $json['verb'] = 'delete';

                            $object = ['objectType' => 'post'];

                            if (!empty($row['topic_id'])) {
                                $target = [
                                    'objectType' => 'topic',
                                    'id' => $row['topic_id']
                                ];
                            }

                            if (empty($row['meta'])) {
                                preg_match('~\#(\d+)~', $row['message'], $match);
                                if (empty($match[1])) {
                                    continue;
                                }

                                $postId = $match[1];

                                $object['id'] = (int) $postId;

                                preg_match('~Powód: (.*)~', $row['message'], $match);
                                if (!empty($match[1])) {
                                    $object['reasonName'] = $match[1];
                                }

                                if (!empty($row['location_text'])) {
                                    $object['url'] = '/' . str_replace('@forum', 'Forum', $row['location_text']) . '?p=' . $postId . '#id' . $postId;
                                    $target['url'] = '/' . str_replace('@forum', 'Forum', $row['location_text']);
                                }
                            } else {
                                $postId = $row['index'];
                                $meta = json_decode($row['meta'], true);

                                if (empty($meta['subject'])) {
                                    continue;
                                }

                                $object['id'] = (int) $postId;
                                $object['reasonName'] = $meta['reason'];

                                $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                                $dom = new \DOMDocument('1.0', 'UTF-8');

                                try {
                                    $dom->loadHTML(str_replace('&', '&amp;', $meta['subject']));
                                } catch (\Exception $e) {
                                    exit($meta['subject']);
                                }
                                $nodes = $dom->getElementsByTagName('a');

                                foreach ($nodes as $node) {
                                    $target['displayName'] = $node->nodeValue;
                                    $parseUrl = parse_url($node->getAttribute('href'));
                                    $target['url'] = '/Forum' . $parseUrl['path'];
                                }

                                if (!empty($meta['id'])) {
                                    $meta['id'] = mb_convert_encoding($meta['id'], 'HTML-ENTITIES', "UTF-8");
                                    $dom = new \DOMDocument('1.0', 'UTF-8');
                                    $dom->loadHTML($meta['id']);

                                    $nodes = $dom->getElementsByTagName('a');

                                    foreach ($nodes as $node) {
                                        $parseUrl = parse_url($node->getAttribute('href'));
                                        $object['url'] = '/Forum' . $parseUrl['path'];

                                        if (!empty($parseUrl['query'])) {
                                            $object['url'] .= '?' . $parseUrl['query'];
                                        }

                                        if (!empty($parseUrl['fragment'])) {
                                            $object['url'] .= '#' . $parseUrl['fragment'];
                                        }
                                    }
                                }
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Przywrócenie postu':
                            $json['verb'] = 'rollback';

                            $object = ['objectType' => 'post'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $postId = $row['index'];
                            $meta = json_decode($row['meta'], true);

                            $object['id'] = (int) $postId;

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;
                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                            }

                            $meta['id'] = mb_convert_encoding($meta['id'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['id']);

                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $parseUrl = parse_url($node->getAttribute('href'));
                                $object['url'] = '/Forum' . $parseUrl['path'];

                                if (!empty($parseUrl['query'])) {
                                    $object['url'] .= '?' . $parseUrl['query'];
                                }

                                if (!empty($parseUrl['fragment'])) {
                                    $object['url'] .= '#' . $parseUrl['fragment'];
                                }
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Napisano odpowiedź w temacie':
                            $json['verb'] = 'create';

                            $object = ['objectType' => 'post'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            if (empty($row['meta'])) {
                                preg_match('~\#(\d+)~', $row['message'], $match);
                                if (empty($match[1])) {
                                    continue;
                                }

                                $postId = $match[1];
                                $object['id'] = (int) $postId;

                                if (!empty($row['location_text'])) {
                                    $object['url'] = '/' . str_replace('@forum', 'Forum', $row['location_text']) . '?p=' . $postId . '#id' . $postId;
                                    $target['url'] = '/' . str_replace('@forum', 'Forum', $row['location_text']);
                                }
                            } else {
                                $postId = $row['index'];
                                $meta = json_decode($row['meta'], true);

                                if (empty($meta['subject'])) {
                                    continue;
                                }

                                $object['id'] = (int) $postId;

                                $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");
                                $dom = new \DOMDocument('1.0', 'UTF-8');

                                try {
                                    $dom->loadHTML(str_replace('&', '&amp;', $meta['subject']));
                                } catch (\Exception $e) {
                                    exit($meta['subject']);
                                }
                                $nodes = $dom->getElementsByTagName('a');

                                foreach ($nodes as $node) {
                                    $target['displayName'] = $node->nodeValue;
                                    $parseUrl = parse_url($node->getAttribute('href'));
                                    $object['url'] = '/Forum' . $parseUrl['path'] . '?p=' . $postId . '#id' . $postId;
                                    $target['url'] = '/Forum' . $parseUrl['path'];
                                }

                                if (isset($meta['username'])) {
                                    $json['actor'] = ['displayName' => $meta['username'], 'objectType' => 'actor'];
                                }
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Zmiana tytułu wątku':
                            if (empty($row['meta']) || empty($row['topic_id'])) {
                                continue;
                            }

                            $json['verb'] = 'update';

                            $object = ['objectType' => 'post'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $postId = DB::connection('mysql')->table('topic')->select(['topic_first_post_id'])->where('topic_id', $row['topic_id'])->first()->topic_first_post_id;
                            $object['id'] = $postId;

                            $meta = json_decode($row['meta'], true);

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;
                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                                $object['url'] = '/Forum' . $parseUrl['path'] . '?p=' . $postId . '#id' . $postId;
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Akceptacja odpowiedzi':
                            if (empty($row['index'])) {
                                continue;
                            }
                            $json['verb'] = 'accept';

                            $object = ['objectType' => 'post', 'id' => $postId = $row['index']];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $meta = json_decode($row['meta'], true);

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;
                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                                $object['url'] = '/Forum' . $parseUrl['path'] . '?p=' . $postId . '#id' . $postId;
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Ocena posta':
                            $json['verb'] = 'vote';

                            $object = ['objectType' => 'post', 'id' => $postId = $row['index']];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $meta = json_decode($row['meta'], true);

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;
                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                                $object['url'] = '/Forum' . $parseUrl['path'] . '?p=' . $postId . '#id' . $postId;
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Dodanie komentarza':
                            $json['verb'] = 'create';

                            $object = ['objectType' => 'comment'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $meta = json_decode($row['meta'], true);

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;

                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                                $object['url'] = '/Forum' . $parseUrl['path'] . '?' . $parseUrl['query'] . '#' . $parseUrl['fragment'];

                                preg_match('~comment\-(\d+)~', $parseUrl['fragment'], $match);
                                $object['id'] = (int) $match[1];
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Edycja komentarza':
                            $json['verb'] = 'update';

                            $object = ['objectType' => 'comment'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $meta = json_decode($row['meta'], true);

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;

                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                                $object['url'] = '/Forum' . $parseUrl['path'] . '?' . $parseUrl['query'] . '#' . $parseUrl['fragment'];

                                preg_match('~comment\-(\d+)~', $parseUrl['fragment'], $match);
                                $object['id'] = (int) $match[1];
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Usunięcie komentarza':
                            $json['verb'] = 'delete';

                            $object = ['objectType' => 'comment'];
                            $target = [
                                'objectType' => 'topic',
                                'id' => $row['topic_id']
                            ];

                            $meta = json_decode($row['meta'], true);

                            $meta['subject'] = mb_convert_encoding($meta['subject'], 'HTML-ENTITIES', "UTF-8");

                            $dom = new \DOMDocument('1.0', 'UTF-8');
                            $dom->loadHTML($meta['subject']);
                            $nodes = $dom->getElementsByTagName('a');

                            foreach ($nodes as $node) {
                                $target['displayName'] = $node->nodeValue;

                                $parseUrl = parse_url($node->getAttribute('href'));
                                $target['url'] = '/Forum' . $parseUrl['path'];
                                $object['url'] = '/Forum' . $parseUrl['path'] . '?' . $parseUrl['query'] . '#' . $parseUrl['fragment'];

                                preg_match('~comment\-(\d+)~', $parseUrl['fragment'], $match);
                                if (empty($match[1])) {
                                    continue;
                                }
                                $object['id'] = (int) $match[1];
                            }

                            $json['object'] = $object;
                            $json['target'] = $target;

                            break;

                        case 'Dodanie wpisu do Pastebin':
                            $json['verb'] = 'create';

                            $object = ['objectType' => 'pastebin'];

                            preg_match('~\#(\d+)~', $row['message'], $match);
                            $object['id'] = (int) $match[1];
                            $object['url'] = route('pastebin.show', [$object['id']], false);

                            $json['object'] = $object;
                            break;
                    }

                    if (!empty($json['verb'])) {
                        $mongo->insert($json);
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
    }

    public function migrateComments()
    {
        $sql = DB::connection('mysql')->table('comment')
            ->where('comment_module', 3)
            ->where('comment_user', '>', 0)
            ->get();

        $bar = $this->output->createProgressBar(count($sql));

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                $row = $this->skipPrefix('comment_', (array) $row);
                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'time', 'created_at');
                $this->rename($row, 'content', 'text');
                $this->rename($row, 'page', 'wiki_id');

                $row['created_at'] = $this->timestampToDatetime($row['created_at']);

                unset($row['username'], $row['module']);
                $row['updated_at'] = $row['created_at'];

                DB::table('wiki_comments')->insert($row);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->fixSequence(['wiki_comments']);
    }

    public function migrateUserTags()
    {
        $sql = DB::connection('mysql')->table('setting')->where('setting_name', 'forum.setting')->get();

        DB::beginTransaction();

        try {
            foreach ($sql as $row) {
                try {
                    $unserialize = unserialize($row->setting_value);

                    if (!empty($unserialize['userTags'])) {
                        $tags = array_map('trim', explode(',', $unserialize['userTags']));

                        DB::table('settings')->insert([
                            'created_at' => $row->setting_date,
                            'user_id' => $row->setting_user,
                            'session_id' => $row->setting_session_id,
                            'name' => 'forum.tags',
                            'value' => json_encode($tags)
                        ]);
                    }
                } catch (\Exception $e) {}
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->fixSequence(['settings']);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement('ALTER TABLE post_votes DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE alerts DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE alert_types DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE forums DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE groups DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE permissions DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE pm DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE posts DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE reputations DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE topics DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE users DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE forums DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE sessions DISABLE TRIGGER ALL');
        DB::statement('ALTER TABLE user_skills DISABLE TRIGGER ALL');

        DB::statement('SET session_replication_role = replica');

        try {
            $this->migrateUsers();
            $this->migrateTags();
            /* musi byc przed dodawaniem grup */
            $this->migratePermissions();
            $this->migrateGroups();
            $this->migrateSkills();
            $this->migrateWords();
            $this->migrateAlerts();
            $this->migratePm();
            $this->migrateReputation();
            $this->migrateForum();
            $this->migrateTopic();
            $this->migratePost();
            $this->migrateMicroblogs();
            $this->migrateFirms();
            $this->migrateJobs();
            $this->migratePastebin();
            $this->migratePoll();
            $this->migrateBan();
            $this->migrateRedirect();

            $this->migrateWiki();
            $this->fillPagesTable();
            $this->migratePageVisits();
            $this->migrateLogs();
            $this->migrateComments();
            $this->migrateUserTags();
        } finally {
            DB::statement('ALTER TABLE post_votes ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE alerts ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE alert_types ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE forums ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE groups ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE permissions ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE pm ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE posts ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE reputations ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE topics ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE users ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE forums ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE sessions ENABLE TRIGGER ALL');
            DB::statement('ALTER TABLE user_skills ENABLE TRIGGER ALL');

            DB::statement('SET session_replication_role = DEFAULT');
        }
    }
}
