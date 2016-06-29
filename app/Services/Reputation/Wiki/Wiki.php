<?php

namespace Coyote\Services\Reputation\Wiki;

use Coyote\Services\Reputation\Reputation;

abstract class Wiki extends Reputation
{
    /**
     * @param int $wikiId
     * @return $this
     */
    public function setWikiId($wikiId)
    {
        $this->metadata['wiki_id'] = $wikiId;

        return $this;
    }

    /**
     * @param \Coyote\Wiki $model
     */
    public function map($model)
    {
        $this->setUrl('/' . $model->path); // "/" at the beginning for url compatibility
        $this->setWikiId($model->wiki_id);
    }

    /**
     * Cofniecie pkt reputacji za aktywnosc na danej stronie.
     *
     * @param int $wikiId
     */
    public function undo($wikiId)
    {
        $result = $this
            ->reputation
            ->where('type_id', static::ID)
            ->whereRaw("metadata->>'wiki_id' = ?", [$wikiId])
            ->get();

        foreach ($result as $row) {
            $this->setIsPositive(false);

            $this->save($row->toArray());
        }
    }
}
