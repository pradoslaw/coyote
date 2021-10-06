<?php

namespace Coyote\Http\Requests;

use Coyote\Poll;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class PollRequest extends FormRequest
{
    private Poll $poll;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->poll = $this->route('poll');

        if ($this->user() === null || $this->isFraudDetected()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'items' => 'required|array|max:' . $this->poll->max_items,
            'items.*' => 'required|integer|in:' . $this->poll->items()->pluck('id')->implode(',')
        ];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('Brak uprawnień do oddania głosu w ankiecie.');
    }

    private function isFraudDetected(): bool
    {
        /** @var \Illuminate\Support\Collection $collection */
        $collection = $this->poll->votes;

        $userId = $this->user()->id;
        $ip = $this->getClientIp();
        $fingerprint = request()->fingerprint;

        return $collection->contains('user_id', $userId) || $collection->contains('ip', $ip) || $collection->contains('fingerprint', $fingerprint);
    }
}
