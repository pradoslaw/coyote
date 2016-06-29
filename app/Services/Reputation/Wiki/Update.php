<?php

namespace Coyote\Services\Reputation\Wiki;

/**
 * Class Update
 */
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
        $this->setExcerpt(excerpt($logs[0]->text));

        $length = $logs[0]->length;
        $diff = $logs[0]->diff;

        if ($diff > 0) {
            // roznica zmian w stosunku do poprzedniej wersji (w procentach)
            $percentage = round($diff / $length * 100);
            $value = $length > 1200 ? (min(25, max(1, $percentage))) : 1;

            if ($this->isFlooded($logs)) {
                $this->setValue($value);
            } elseif ($length > 1200 && $percentage >= 10) {
                $this->setValue(1);
            } else {
                $this->setValue(0);
            }
        } else {
            $this->setValue(null); // don't save reputation
        }

        return $this;
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

    /**
     * Cofniecie pkt reputacji za dany wpis (np. przy usuwaniu wpisu)
     *
     * @param int $microblogId
     */
//    public function undo($microblogId)
//    {
//        $result = $this->reputation
//            ->where('type_id', '=', self::ID)
//            ->whereRaw("metadata->>'microblog_id' = ?", [$microblogId])
//            ->first();
//
//        if ($result) {
//            $this->setIsPositive(false);
//
//            $this->save($result->toArray());
//        }
//    }
}
