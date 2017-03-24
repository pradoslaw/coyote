<?php

namespace Coyote\Services\Session;

use Coyote\Repositories\Contracts\UserRepositoryInterface as UserRepository;
use Coyote\Repositories\Criteria\User\InSession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as SessionRepository;

/**
 * Generuje widok przedstawiajacy liste osob na danej stronie z podzialem na boty, zalogowane osoby itp
 */
class Viewers
{
    const USER = 'Użytkownik';
    const ROBOT = 'Robot';

    /**
     * @var SessionRepository
     */
    private $session;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param SessionRepository $session
     * @param UserRepository $user
     * @param Request $request
     */
    public function __construct(SessionRepository $session, UserRepository $user, Request $request)
    {
        $this->session = $session;
        $this->user = $user;
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
        /** @var \Illuminate\Support\Collection $collection */
        $collection = $this->unique($this->session->getByPath($path));

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

        $collection = $this->setupUsersName($collection);

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
     * @param Collection $collection
     * @return Collection
     */
    private function setupUsersName(Collection $collection)
    {
        $registered = $collection->filter(function ($item) {
            return $item['user_id'] !== null;
        });

        // include group name and only few columns in query
        $this->user->pushCriteria(new InSession());
        $result = $this->user->findMany($registered->pluck('user_id')->toArray());

        foreach ($result as $row) {
            foreach ($collection as $key => $item) {
                if ($row->user_id == $item['user_id']) {
                    $collection[$key] = array_merge($item, $row->toArray());
                }
            }
        }

        return $collection;
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
     * @param Collection $collection
     * @return Collection
     */
    private function unique(Collection $collection)
    {
        $guests = $collection->filter(function (array $item) {
            return $item['user_id'] === null;
        });

        $collection
            ->filter(function (array $item) {
                return $item['user_id'] !== null;
            })
            ->unique('user_id')
            ->each(function (array $item) use ($guests) {
                $guests->push($item);
            });

        return $guests;
    }
}
