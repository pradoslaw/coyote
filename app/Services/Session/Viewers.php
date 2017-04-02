<?php

namespace Coyote\Services\Session;

use Coyote\Session;
use Illuminate\Database\Connection as Db;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Generuje widok przedstawiajacy liste osob na danej stronie z podzialem na boty, zalogowane osoby itp
 */
class Viewers
{
    const USER = 'Użytkownik';
    const ROBOT = 'Robot';

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
        $groups = [self::USER => [], self::ROBOT => []];

        start_measure('total time');
        $collection = $this->collection($path);

        stop_measure('total time');

        // zlicza liczbe userow
        $total = $collection->count();
        // zlicza gosci online (niezalogowani uzytkownicy)
        $guests = $collection->where('user_id', null)->count();
        // ilosc zalogowanych userow online
        $registered = $total - $guests;

        // zlicza ilosc robotow na stronie
        $robots = $collection->filter(function ($item) {
            return $item['robot'];
        })
        ->count();

        // only number of human guests
        $guests -= $robots;

        $collection = $this->registered->setup($collection);

        foreach ($collection->groupBy('group') as $name => $rowset) {
            if ($name == '') {
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

        foreach ($collection->groupBy('robot') as $name => $rowset) {
            if ($name) {
                $groups[self::ROBOT][] = $name . (count($rowset) > 1 ? ' (' . count($rowset) . 'x)' : '');
            }
        }

        // moze sie okazac ze wsrod sesji nie ma ID sesji aktualnego requestu poniewaz tabela session
        // nie zostala jeszcze zaktualizowana. w takim przypadku bedziemy musieli dodac "recznie"
        // uzytkownika ktory aktualnie dokonal tego zadania
        if (!$collection->contains('id', $this->request->session()->getId())) {
            $total++;

            if ($this->request->user()) {
                $groupName = self::USER;
                $registered++;

                if ($this->request->user()->group_id) {
                    $groupName = $this->request->user()->group->name;
                }

                $groups[$groupName] = $this->makeProfileLink($this->request->user()->id, $this->request->user()->name);
            } else {
                $guests++;
            }
        }

        ksort($groups);

        return view('components.viewers', compact('groups', 'total', 'guests', 'registered', 'robots'));
    }

    /**
     * @param string|null $path
     * @return Collection
     */
    private function collection($path): Collection
    {
        $result = $this
            ->db
            ->table('sessions')
            ->when($path !== null, function (Builder $builder) use ($path) {
                return $builder->whereRaw('LOWER(path) = ?', [mb_strtolower($path)]);
            })
            ->get(['id', 'user_id', 'robot']);

        $collection = $result->map(function ($item) {
            return new Session((array) $item);
        });

        /** @var \Illuminate\Support\Collection $collection */
        return $this->unique($collection);
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
