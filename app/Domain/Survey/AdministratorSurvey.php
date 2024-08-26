<?php
namespace Coyote\Domain\Survey;

use Coyote\Models\Survey;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->surveyGuestsQuery($survey)
            ->selectRaw("guests.settings->>'surveyState' as survey_state, COUNT(*) as count")
            ->groupBy('survey_state')
            ->pluck('count', 'survey_state')
            ->toArray();
    }

    public function surveyResults(Survey $survey): array
    {
        return $this->surveyGuestsQuery($survey)
            ->selectRaw("guests.settings->>'postCommentStyle' as survey_choice, COUNT(*) as count")
            ->groupBy('survey_choice')
            ->pluck('count', 'survey_choice')
            ->toArray();
    }

    private function surveyGuestsQuery(Survey $survey): BelongsToMany
    {
        return $survey->users()
            ->join('guests', 'guests.id', '=', 'users.guest_id');
    }

    public function membersCountOfState(Survey $survey, string $string): int
    {
        return $this->membersStatistic($survey)[$string];
    }
}
