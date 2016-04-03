<?php

namespace Coyote\Console\Commands;

use Coyote\Pm;
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
     * @todo ip_invalid zapisac do mongo
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

                if ($row['group_id'] <= 2) {
                    $row['group_id'] = null;
                }

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
     * @todo Co z subdomena forum? Jezeli nie zapisujemy hostow trzeba prowadic do prawidlowego aresu url
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
     * 90% (oprocz samej tresci wiadomosci - zmiana parsera)
     * W poprzedniej wersji nie bylo grupowania po polu "root"? :/
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

        DB::beginTransaction();

        try {
            $sql = DB::connection('mysql')
                    ->table('reputation_activity')
                    ->select(['reputation_activity.*', 'module_name AS activity_module_name'])
                    ->leftJoin('page', 'page_id', '=', 'activity_page')
                    ->leftJoin('module', 'module_id', '=', 'page_module')
                    ->get();

            $bar = $this->output->createProgressBar(count($sql));

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
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

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

            $this->fixSequence(['forums', 'forum_permissions', 'forum_track', 'forum_reasons']);

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
                        $row = $this->skipPrefix('topic_', (array)$row);

                        $this->rename($row, 'forum', 'forum_id');
                        $this->rename($row, 'vote', 'score');
                        $this->rename($row, 'sticky', 'is_sticky');
                        $this->rename($row, 'announcement', 'is_announcement');
                        $this->rename($row, 'lock', 'is_locked');
                        $this->rename($row, 'poll', 'poll_id');
                        $this->rename($row, 'moved_id', 'prev_forum_id');
                        $this->rename($row, 'last_post_time', 'last_post_created_at');

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
                        $row['path'] = explode('-', $row['path'])[1];

                        DB::table('topics')->insert($row);
                        $bar->advance();
                    }
                });

            DB::connection('mysql')->table('topic_marking')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = (array) $row;

                    $this->rename($row, 'mark_time', 'marked_at');
                    $this->timestampToDatetime($row['marked_at']);

                    DB::table('topic_track')->insert($row);
                    $bar->advance();
                }
            });

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
     * @todo Usuniecie posta trzeba przepisac do mongo
     * Poza tym jest ok
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
                        $row = $this->skipPrefix('post_', (array)$row);

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

            DB::connection('mysql')->table('post_subscribe')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    DB::table('post_subscribers')->insert((array) $row);
                    $bar->advance();
                }
            });

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

            DB::connection('mysql')->table('post_accept')->chunk(100000, function ($sql) use ($bar) {
                foreach ($sql as $row) {
                    $row = $this->skipPrefix('accept_', (array) $row);

                    $this->rename($row, 'post', 'post_id');
                    $this->rename($row, 'topic', 'topic_id');
                    $this->rename($row, 'user', 'user_id');
                    $this->rename($row, 'time', 'created_at');

                    $this->timestampToDatetime($row['created_at']);

                    DB::table('post_accepts')->insert($row);
                    $bar->advance();
                }
            });

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
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    /**
     * @todo edit_user przeniesc do mongo
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
                        $row = $this->skipPrefix('microblog_', (array)$row);

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

            DB::connection('mysql')->table('microblog_discuss')->chunk(100000, function ($sql) use ($bar) {
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

                $tagId = DB::table('tags')->select(['id'])->where('name', $row['tag_name'])->pluck('id');
                if (!$tagId) {
                    $tagId = DB::table('tags')->where(['name' => $row['tag_name']])->pluck('id');
                }

                DB::table('microblog_tags')->insert(['tag_id' => $tagId, 'microblog_id' => $row['microblog_parent'] ? $row['microblog_parent'] : $row['microblog_id']]);
            }

            $bar->finish();

            $this->fixSequence($tables);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

    public function migrateTopicVisits()
    {
        $this->info('Topic visits...');

        $count = DB::connection('mysql')->table('page_track')->join('topic', 'topic_page', '=', 'track_page')->count();
        $bar = $this->output->createProgressBar($count);

        DB::beginTransaction();

        try {
            DB::connection('mysql')
                ->table('page_track')
                ->select(['page_track.*', 'topic_id AS track_topic_id'])
                ->join('topic', 'topic_page', '=', 'track_page')
                ->chunk(50000, function ($sql) use ($bar) {
                    foreach ($sql as $row) {
                        $row = $this->skipPrefix('track_', (array) $row);

                        $this->rename($row, 'user', 'user_id');
                        $this->rename($row, 'time', 'created_at');
                        $this->rename($row, 'last_visit', 'updated_at');
                        $this->rename($row, 'count', 'visits');

                        $this->timestampToDatetime($row['created_at']);
                        $this->timestampToDatetime($row['updated_at']);

                        unset($row['page']);

                        DB::table('topic_visits')->insert($row);
                        $bar->advance();
                    }
                });


            DB::commit();
            $bar->finish();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

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
                $row = $this->skipPrefix('firm_', (array)$row);

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
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

        $this->line('');
        $this->info('Done');
    }

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

        try {
            foreach ($jobs as $row) {
                $row = $this->skipPrefix('job_', (array)$row);

                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'firm', 'firm_id');
                $this->rename($row, 'remote', 'is_remote');
                $this->rename($row, 'country', 'country_id');
                $this->rename($row, 'salary_currency', 'currency_id');
                $this->rename($row, 'salary_duration', 'rate_id');
                $this->rename($row, 'type', 'employment_id');
                $this->rename($row, 'apply', 'enable_apply');
                $this->rename($row, 'deadline', 'deadline_at');

                $row['path'] = str_slug($row['title']);

                $this->timestampToDatetime($row['created_at']);
                $this->timestampToDatetime($row['updated_at']);
                $this->timestampToDatetime($row['deadline_at']);

                $this->setNullIfEmpty($row['deleted_at']);
                if (!empty($row['deleted_at'])) {
                    $row['deleted_at'] = $row['updated_at'];
                }

                unset($row['incognito'], $row['page'], $row['searchable']);
                $row['title'] = htmlspecialchars_decode($row['title']);

                DB::table('jobs')->insert($row);
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
        $this->info('Job locations...');

        $locations = DB::connection('mysql')
            ->table('job_location')
            ->get();

        $bar = $this->output->createProgressBar(count($locations));

        DB::beginTransaction();
        $replace = ['Warsaw' => 'Warszawa', 'Krakow' => 'Kraków', 'Wroclaw' => 'Wrocław', 'Lodz' => 'Łódź'];

        try {
            foreach ($locations as $row) {
                $row = $this->skipPrefix('location_', (array)$row);
                $this->rename($row, 'job', 'job_id');

                $row['city'] = str_ireplace(array_keys($replace), array_values($replace), $row['city']);

                DB::table('job_locations')->insert($row);
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
                $row = $this->skipPrefix('apply_', (array)$row);

                $this->rename($row, 'user', 'user_id');
                $this->rename($row, 'time', 'created_at');
                $this->rename($row, 'session', 'session_id');
                $this->rename($row, 'job', 'job_id');

                $this->timestampToDatetime($row['created_at']);
                $this->setNullIfEmpty($row['user_id']);
                unset($row['ip']);

                DB::table('job_candidates')->insert((array) $row);
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
                    DB::table('job_referers')->insert((array)$row);
                }

                $bar->advance();
            }

            $bar->finish();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error($e->getFile() . ' [' . $e->getLine() . ']: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }

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
//        $this->migrateUsers();
//        $this->migrateTags();
        /* musi byc przed dodawaniem grup */
//        $this->migratePermissions();
//        $this->migrateGroups();
//        $this->migrateSkills();
//        $this->migrateWords();
//        $this->migrateAlerts();
//        $this->migratePm();
//        $this->migrateReputation();
//        $this->migrateForum();
//        $this->migrateTopic();
//        $this->migratePost();
//        $this->migrateMicroblogs();
//        $this->migrateTopicVisits();
//        $this->migrateFirms();
        $this->migrateJobs();

        DB::statement('SET session_replication_role = DEFAULT');
    }
}
