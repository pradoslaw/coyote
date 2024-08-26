<?php
namespace Coyote\Domain\Survey;

use Coyote\Models\Survey;

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

    public function newSurvey(string $title): int
    {
        /** @var Survey $survey */
        $survey = Survey::query()->create(['title' => $title]);
        return $survey->id;
    }

    public function updateMembers(Survey $survey, array $memberIds): void
    {
        $survey->users()->sync($memberIds);
    }
}
