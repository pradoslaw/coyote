<?php
namespace Coyote\Http\Forms;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Coyote\Repositories\Eloquent\UserRepository;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\FormEvents;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Coyote\User;
use Illuminate\Http\Request;

class FirewallForm extends Form implements ValidatesWhenSubmitted
{
    public function __construct(Request $request, private UserRepository $repository)
    {
        parent::__construct();
        $this->addEventListener(FormEvents::PRE_RENDER, function (Form $form) use ($request) {
            $userId = $this->data->user_id ?? $request->input('user');
            if (!empty($userId)) {
                $user = $this->repository->find($userId, ['name']);
                if (!empty($user)) {
                    $form->get('name')->setValue($user->name);
                }
            }
            if (empty($this->data->id)) {
                $this->get('ip')->setValue($request->input('ip'));
            }
        });
        $this->addEventListener(FormEvents::PRE_SUBMIT, fn(Form $form) => $form->remove('created_at'));
        $this->addEventListener(FormEvents::POST_SUBMIT, function (Form $form) {
            $username = $form->get('name')->getValue();
            $form->add('user_id', 'hidden', ['template' => 'hidden']);
            if ($username) {
                /** @var User $user */
                $user = $this->repository->findByName($username);
                if ($user) {
                    $form->get('user_id')->setValue($user->id);
                }
            }
        });
    }

    public function buildForm(): void
    {
        $this
            ->add('name', 'text', [
                'rules' => 'nullable|string|user_exist',
                'label' => 'Nazwa użytkownika',
                'attr'  => [
                    'id'           => 'username',
                    'autocomplete' => 'off',
                ],
            ])
            ->add('ip', 'text', [
                'label' => 'IP',
                'rules' => 'nullable|string|min:2|max:100',
                'help'  => 'IP może zawierać znak *',
            ])
            ->add('fingerprint', 'text', [
                'label' => 'Fingerprint',
                'rules' => 'nullable|string|max:255',
            ])
            ->add('reason', 'textarea', [
                'label' => 'Powód',
                'rules' => 'max:1000',
            ]);
        if (!empty($this->data->id)) {
            $this->add('created_at', 'datetime', [
                'label' => 'Data utworzenia',
                'attr'  => ['disabled' => 'disabled'],
                'value' => $this->dateFormatForFrontend($this->data?->created_at?->toImmutable()),
            ]);
        }
        $this->add('expire_at', 'ban_duration', [
            'label' => 'Data wygaśnięcia',
            'attr'  => [
                'expires_at'       => $this->dateFormatForFrontend($this->data?->expire_at?->toImmutable()),
                'expiration_dates' => $this->expirationDates(
                    ($this->data->created_at ?? Carbon::now())->toImmutable(),
                ),
            ],
        ]);

        if (!empty($this->data->id)) {
            $this
                ->add('duration', 'text', [
                    'label' => 'Długość bana',
                    'attr'  => ['disabled' => 'disabled'],
                    'value' => $this->periodLength(
                        $this->data->created_at->toImmutable(),
                        $this->data->expire_at?->toImmutable(),
                    ),
                ])
                ->add('remaining', 'text', [
                    'label' => 'Pozostało',
                    'attr'  => ['disabled' => 'disabled'],
                    'value' => $this->relativeDifference(
                        CarbonImmutable::now(),
                        $this->data->expire_at?->toImmutable(),
                    ),
                ]);
        }

        $this
            ->add('submit', 'submit_with_delete', [
                'label'             => 'Zapisz',
                'attr'              => [
                    'data-submit-state' => 'Zapisywanie...',
                ],
                'delete_url'        => empty($this->data->id) ? '' : route('adm.firewall.delete', [$this->data->id]),
                'delete_visibility' => !empty($this->data->id),
            ]);
    }

    private function expirationDates(CarbonImmutable $startDate): array
    {
        $values = [
            [$startDate->addMinutes(5)],
            [$startDate->addMinutes(10)],
            [$startDate->addMinutes(15)],
            [$startDate->addMinutes(30)],
            [$startDate->addMinutes(45)],
            [$startDate->addHours(1)],
            [$startDate->addHours(2)],
            [$startDate->addHours(3)],
            [$startDate->addHours(4)],
            [$startDate->addHours(5)],
            [$startDate->addHours(6)],
            [$startDate->addHours(12)],
            [$startDate->addHours(18)],
            [$startDate->addDays(1)],
            [$startDate->addDays(1.5), 'półtora dnia'],
            [$startDate->addDays(2)],
            [$startDate->addDays(3)],
            [$startDate->addDays(4)],
            [$startDate->addDays(5)],
            [$startDate->addDays(6)],
            [$startDate->addWeeks(1)],
            [$startDate->addWeeks(1.5), 'półtora tygodnia'],
            [$startDate->addWeeks(2)],
            [$startDate->addWeeks(3)],
            [$startDate->addWeeks(4)],
            [$startDate->addWeeks(5), '5 tygodni'],
            [$startDate->addWeeks(6), '6 tygodni'],
            [$startDate->addWeeks(7), '7 tygodni'],
            [$startDate->addMonths(2)],
            [$startDate->addMonths(2.5), 'dwa i pół mies.'],
            [$startDate->addMonths(3)],
            [$startDate->addMonths(3.5), 'trzy i pół miesiąca'],
            [$startDate->addMonths(4)],
            [$startDate->addMonths(5)],
            [$startDate->addMonths(6)],
            [$startDate->addMonths(7)],
            [$startDate->addMonths(8)],
            [$startDate->addMonths(9)],
            [$startDate->addYear(1)],
            [$startDate->addYear(1.5), 'półtora roku'],
            [$startDate->addYear(2)],
            [$startDate->addYear(3)],
            [$startDate->addYear(4)],
            [$startDate->addYear(5)],
            [null],
        ];
        $buttons = [];
        foreach ($values as $value) {
            $time = $value[0];
            $buttons[] = [
                'expires_at' => $this->dateFormatForFrontend($time),
                'label'      => $value[1] ?? $this->periodLength($startDate, $time),
            ];
        }
        return $buttons;
    }

    private function periodLength(CarbonImmutable $since, ?CarbonImmutable $date): string
    {
        if ($date === null) {
            return '∞';
        }
        return $date->longAbsoluteDiffForHumans($since);
    }

    private function relativeDifference(CarbonImmutable $since, ?CarbonImmutable $date): string
    {
        if ($date === null) {
            return '∞';
        }
        if ($date->isBefore($since)) {
            return 'upłynął ' . $date->longAbsoluteDiffForHumans($since) . ' temu';
        }
        return $date->diff($since);
    }

    private function dateFormatForFrontend(?CarbonImmutable $time): ?string
    {
        return $time?->format('Y-m-d\TH:i:s');
    }
}
