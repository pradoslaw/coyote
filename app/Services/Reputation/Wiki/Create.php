<?php
namespace Coyote\Services\Reputation\Wiki;

class Create extends Wiki
{
    const ID = \Coyote\Reputation::WIKI_CREATE;

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
     * @return $this
     */
    public function map($model)
    {
        parent::map($model);

        /** @var \Coyote\Wiki\Log $log */
        $log = $model->logs()->orderBy('id', 'DESC')->limit(1)->first();

        $this->setUserId($log->user_id);
        $this->setExcerpt(excerpt($this->parse($log->text)));
        $this->setValue(min(25, max(1, round($log->length * 0.02))));

        return $this;
    }
}
