<?php

namespace Coyote\Session;

/**
 * Generuje widok przedstawiajacy liste osob na danej stronie z podzialem na boty, zalogowane osoby itp
 *
 * Class Viewers
 * @package Coyote\Session
 */
class Viewers
{
    const USER = 'Użytkownik';
    const ROBOT = 'Robot';

    private $session;

    /**
     * @param \Coyote\Session $session
     */
    public function __construct(\Coyote\Session $session)
    {
        $this->session = $session;
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
        $collection = $this->session->getViewers($path);

        // zlicza liczbe userow
        $total = $collection->count();
        // zlicza gosci online (niezalogowani uzytkownicy)
        $guests = $collection->where('user_id', null)->count();

        // zlicza ilosc robotow na stronie
        $robots = $collection->filter(function ($item) {
            return $item->robot;
        })
        ->count();

        // liczba rzeczywistych osob z pominieciem botow
        $people = $total - $robots;

        foreach ($collection->groupBy('group') as $name => $rowset) {
            if ($name == '') {
                $name = self::USER;
            } elseif (!isset($groups[$name])) {
                $groups[$name] = [];
            }

            foreach ($rowset as $user) {
                if ($user->user_id) {
                    $groups[$name][] = link_to_route(
                        'profile',
                        $user->name,
                        [$user->user_id],
                        ['data-user-id' => $user->user_id]
                    );
                }
            }
        }

        foreach ($collection->groupBy('robot') as $name => $rowset) {
            if ($name) {
                $groups[self::ROBOT][] = $name . (count($rowset) > 1 ? ' (' . count($rowset) . 'x)' : '');
            }
        }

        return view('components/viewers', compact('groups', 'total', 'guests', 'people', 'robots'));
    }
}
