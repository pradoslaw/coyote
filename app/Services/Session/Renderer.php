<?php

namespace Coyote\Services\Session;

use Coyote\Session;
use Illuminate\Database\Connection as Db;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Generuje widok przedstawiajacy liste osob na danej stronie z podzialem na boty, zalogowane osoby itp
 */
class Renderer
{
    const USER = 'Użytkownik';

    /**
     * @var Db
     */
    private $db;

    /**
     * @var Registered
     */
    private $registered;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param Db $db
     * @param Registered $registered
     * @param Request $request
     */
    public function __construct(Db $db, Registered $registered, Request $request)
    {
        $this->db = $db;
        $this->registered = $registered;
        $this->request = $request;
    }

    /**
     * Generuje widok userow online. W parametrze nalezy podac sciezke - np. /Forum
     *
     * @param null $path
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render($path = null)
    {
        $groups = [self::USER => []];

        $collection = $this->data($path);

        // zlicza liczbe userow
        $total = $collection->sum('count');
        // zlicza gosci online (niezalogowani uzytkownicy)
        $guests = $collection->where('user_id', null)->sum('count');
        // ilosc zalogowanych userow online
        $registered = $total - $guests;

        // zlicza ilosc robotow na stronie
        $robots = $collection->filter(fn ($item) => $item->robot)->sum('count');

        // only number of human guests
        $guests -= $robots;
        $total -= $robots;

        $collection = $this->map($collection);

        if ($this->request->user()) {
            if (!$collection->contains('user_id', $this->request->user()->id)) {
                $collection->push(new Session(['user_id' => $this->request->user()->id, 'path' => $path]));

                $total++;
                $registered++;
            }
        } elseif ($collection->count() === 0) {
            // we keep session in redis but also  - list of online users - in postgres.
            // we refresh table every 1 minute, so info about user's current page might be sometimes outdated.
            $total++;
            $guests++;
        }

        $collection = $this->unique($collection);
        $collection = $this->registered->setup($collection);

        foreach ($collection->groupBy('group') as $name => $rowset) {
            if ($name === '') {
                $name = self::USER;
            } elseif (!isset($groups[$name])) {
                $groups[$name] = [];
            }

            foreach ($rowset as $user) {
                if ($user['user_id'] !== null) {
                    $groups[$name][] = $this->makeProfileLink($user['user_id'], $user['name']);
                }
            }
        }

        ksort($groups);

        return view('components.viewers', compact('groups', 'total', 'guests', 'registered'));
    }

    /**
     * Return raw aggregated data from db.
     *
     * @param string|null $path
     * @return Collection
     */
    private function data($path): Collection
    {
        return $this
            ->db
            ->table('sessions')
            ->when($path !== null, function (Builder $builder) use ($path) {
                return $builder->where('path', 'LIKE', mb_strtolower(strtok($path, '?')) . '%');
            })
            ->groupBy(['user_id', 'robot'])
            ->get(['user_id', 'robot', new Expression('COUNT(*)')]);
    }

    /**
     * Map raw object into Session class model.
     *
     * @param Collection $collection
     * @return Collection
     */
    private function map(Collection $collection): Collection
    {
        return $collection->map(function ($item) {
            return new Session((array) $item);
        });
    }

    /**
     * @param int $userId
     * @param string $userName
     * @return string
     */
    private function makeProfileLink($userId, $userName)
    {
        return link_to_route(
            'profile',
            $userName,
            [$userId],
            ['data-user-id' => $userId]
        );
    }

    /**
     * @param Session[] $collection
     * @return Collection
     */
    private function unique(Collection $collection)
    {
        $guests = $collection->filter(function (Session $item) {
            return $item->userId === null;
        });

        $collection
            ->filter(function (Session $item) {
                return $item->userId !== null;
            })
            ->unique('user_id')
            ->each(function (Session $item) use ($guests) {
                $guests->push($item);
            });

        return $guests;
    }
}
