<?php

namespace Coyote\Services\Session;

use Coyote\Session;
use Illuminate\Database;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class Renderer
{
    const USER = 'Użytkownik';

    public function __construct(
        private Database\Connection $db,
        private Registered          $registered,
        private Request             $request)
    {
    }

    public function render(string $requestUri = null): View
    {
        $collection = $this->data($requestUri);

        // zlicza liczbe userow
        $total = $collection->sum('count');
        // zlicza gosci online (niezalogowani uzytkownicy)
        $guests = $collection->where('user_id', null)->sum('count');
        // ilosc zalogowanych userow online
        $registered = $total - $guests;

        // zlicza ilosc robotow na stronie
        $robots = $collection->filter(fn($item) => $item->robot)->sum('count');

        // only number of human guests
        $guests -= $robots;
        $total -= $robots;

        $collection = $this->map($collection);

        if ($this->request->user()) {
            if (!$collection->contains('user_id', $this->request->user()->id)) {
                $collection->push(new Session(['user_id' => $this->request->user()->id, 'path' => $requestUri]));
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

        $groups = [self::USER => []];
        foreach ($collection->groupBy('group') as $name => $users) {
            if ($name === '') {
                $name = self::USER;
            } elseif (!isset($groups[$name])) {
                $groups[$name] = [];
            }
            foreach ($users as $user) {
                if ($user['user_id'] !== null) {
                    $groups[$name][] = $this->makeProfileLink($user['user_id'], $user['name']);
                }
            }
        }

        unset($groups[self::USER]);
        ksort($groups);

        return view('components.viewers', compact('groups', 'total', 'guests', 'registered'));
    }

    private function data(?string $requestUri): Collection
    {
        return $this
            ->db
            ->table('sessions')
            ->when($requestUri !== null, function (Builder $builder) use ($requestUri) {
                return $builder->where('path', 'LIKE', \mb_strToLower(\strTok($requestUri, '?')) . '%');
            })
            ->groupBy(['user_id', 'robot'])
            ->get(['user_id', 'robot', new Expression('COUNT(*)')]);
    }

    private function map(Collection $collection): Collection
    {
        return $collection->map(fn($item) => new Session((array)$item));
    }

    private function makeProfileLink(int $userId, string $userName): string
    {
        return link_to_route('profile', $userName, [$userId], ['data-user-id' => $userId]);
    }

    private function unique(Collection $sessions): Collection
    {
        $guests = $sessions->filter(fn(Session $item) => $item->userId === null);
        $sessions
            ->filter(fn(Session $item) => $item->userId !== null)
            ->unique('user_id')
            ->each(function (Session $item) use ($guests) {
                $guests->push($item);
            });
        return $guests;
    }
}
