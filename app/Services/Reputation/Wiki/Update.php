<?php
namespace Coyote\Services\Reputation\Wiki;

class Update extends Wiki
{
    const ID = \Coyote\Reputation::WIKI_UPDATE;

    /**
     * @param \Coyote\Wiki $model
     * @return $this
     */
    public function map($model)
    {
        parent::map($model);

        /** @var \Coyote\Wiki\Log[] $logs */
        $logs = $model->logs()->orderBy('id', 'DESC')->limit(2)->get();

        $this->setUserId($logs[0]->user_id);
        $this->setExcerpt(excerpt($this->parse($logs[0]->text)));

        $length = $logs[0]->length;
        $diff = $logs[0]->diff;

        if ($diff > 0) {
            $this->setupValue($logs, $diff, $length);
        } else {
            $this->setValue(null); // don't save reputation
        }

        return $this;
    }

    /**
     * @param \Coyote\Wiki\Log[] $logs
     * @param int $diff
     * @param int $length
     */
    private function setupValue($logs, $diff, $length)
    {
        // roznica zmian w stosunku do poprzedniej wersji (w procentach)
        $percentage = round($diff / $length * 100);
        $value = $length > 1200 ? (min(25, max(1, $percentage))) : 1;

        if ($this->isFlooded($logs)) {
            $this->setValue($value);
        } else if ($length > 1200 && $percentage >= 10) {
            $this->setValue(1);
        } else {
            $this->setValue(0);
        }
    }

    /**
     * @param \Coyote\Wiki\Log[] $logs
     * @return bool
     */
    private function isFlooded($logs)
    {
        if (count($logs) < 2) {
            return true;
        } else {
            $diffHours = (strtotime($logs[0]->created_at) - strtotime($logs[1]->created_at)) / 3600;

            return $diffHours > 1 || ($logs[0]->user_id != $logs[1]->user_id);
        }
    }
}
