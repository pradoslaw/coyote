<?php
namespace Coyote\Domain\Survey;

use Coyote\Models\Survey;
use Illuminate\Database\Query\JoinClause;

class AdministratorSurvey
{
    public function experiments(): array
    {
        return Survey::query()->get()
            ->map(fn(Survey $survey) => [
                'title' => $survey->title,
                'url'   => route('adm.experiments.show', $survey->id),
            ])
            ->toArray();
    }

    public function newSurvey(string $title): Survey
    {
        /** @var Survey $survey */
        $survey = Survey::query()->create(['title' => $title]);
        return $survey;
    }

    public function updateMembers(Survey $survey, array $memberIds): void
    {
        $survey->users()->sync($memberIds);
    }

    public function membersCount(Survey $survey): int
    {
        return $survey->users()->count();
    }

    public function membersStatistic(Survey $survey): array
    {
        return [
            'survey-accepted' => $this->membersCountOfState($survey, 'survey-accepted'),
            'survey-declined' => $this->membersCountOfState($survey, 'survey-declined'),
            'survey-invited'  => $this->membersCountOfState($survey, 'survey-invited'),
        ];
    }

    public function membersCountOfState(Survey $survey, string $state): int
    {
        return $survey->users()
            ->join('guests', function (JoinClause $join): void {
                $join
                    ->on('guests.user_id', '=', 'users.id')
                    ->orOn('guests.id', '=', 'users.guest_id');
            })
            ->whereRaw("guests.settings->>'surveyState' = ?", [$state])
            ->count();
    }
}
