<?php

namespace Coyote\Services\Alert\Providers;

use Coyote\Services\Alert\Broadcasts\Db as Broadcast_Db;
use Coyote\Services\Alert\Broadcasts\Email as Broadcast_Email;
use Coyote\Repositories\Contracts\AlertRepositoryInterface as AlertRepository;

/**
 * Class Provider
 */
abstract class Provider implements ProviderInterface
{
    /**
     * @var AlertRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $usersId = [];

    /**
     * @var int
     */
    protected $typeId;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $excerpt;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $senderId;

    /**
     * @var string
     */
    protected $senderName;

    /**
     * @var string
     */
    protected $headline;

    /**
     * @param AlertRepository $repository
     * @param array $args
     * @throws \Exception
     */
    public function __construct(AlertRepository $repository, array $args = [])
    {
        $this->repository = $repository;
        $this->typeId = static::ID;

        $this->headline = $this->repository->headlinePattern($this->typeId);
        if (!$this->headline) {
            throw new \Exception('Uuups. Could not find record in alert_types table.');
        }

        $this->with($args);
    }

    /**
     * @param array $args
     * @return $this
     */
    public function with(array $args = [])
    {
        if (!empty($args)) {
            foreach ($args as $arg => $value) {
                $this->{'set' . camel_case($arg)}($value);
            }
        }

        return $this;
    }

    /**
     * Typ ID powiadomienia
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function addUserId($userId)
    {
        $this->usersId[] = $userId;
        return $this;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function setUserId($userId)
    {
        $this->usersId = [$userId];
        return $this;
    }

    /**
     * @param array $usersId
     * @return mixed
     */
    public function setUsersId(array $usersId)
    {
        $this->usersId = $usersId;
        return $this;
    }

    /**
     * @return array
     */
    public function getUsersId()
    {
        return $this->usersId;
    }

    /**
     * Tytul powiadomienia - np. tytul watku na forum czy nazwa oferty pracy
     *
     * @param string $subject
     * @return mixed
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Krótka zajawka powiadomienia (np. pierwsze kilkadziesiat znakow wpisu czy komentarza)
     *
     * @param string $excerpt
     * @return mixed
     */
    public function setExcerpt($excerpt)
    {
        $this->excerpt = $excerpt;
        return $this;
    }

    /**
     * @return string
     */
    public function getExcerpt()
    {
        return $this->excerpt;
    }

    /**
     * @param string $headline
     * @return mixed
     */
    public function setHeadline($headline)
    {
        $this->headline = $headline;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * URL do powiadonienie (najlepiej nierelatywny)
     *
     * @param string $url
     * @return mixed
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * ID usera ktory generuje powiadomienie (np. autora posta na forum)
     *
     * @param int $senderId
     * @return mixed
     */
    public function setSenderId($senderId)
    {
        $this->senderId = $senderId;
        return $this;
    }

    /**
     * @return int
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * Nazwa uzytkownika ktory geneuje powiadomienia. Moze to byc login uzytkownika albo nick podany
     * na forum jezeli uzytkownik nie jest zalogowany
     *
     * @param $senderName
     * @return mixed
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->getSenderName();
    }

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5($this->typeId . $this->subject), 16);
    }

    /**
     * Zwraca nazwe szablonu e-mail dla danego alertu
     *
     * @return string
     */
    public function emailTemplate()
    {
        return static::EMAIL;
    }

    /**
     * Konwertuje obiekt alertu to tablicy
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];

        foreach (get_class_methods($this) as $methodName) {
            if (substr($methodName, 0, 3) == 'get') {
                $reflect = new \ReflectionMethod($this, $methodName);

                if (!$reflect->getNumberOfRequiredParameters() && $reflect->isPublic()) {
                    $value = $this->$methodName();

                    if (is_string($value) || is_numeric($value)) {
                        $array[snake_case(substr($methodName, 3))] = $value;
                    }
                }

                unset($reflect);
            }
        }

        return $array;
    }

    /**
     * Generuje powiadomienie oraz zwraca ID userow do ktorych zostalo wyslane
     *
     * @return int[]
     */
    public function notify()
    {
        $recipients = [];

        // remove duplicated values
        $this->usersId = array_unique($this->usersId);

        // we don't want to send a notification to ourselves
        $index = array_search($this->getSenderId(), $this->usersId);
        if (false !== $index) {
            unset($this->usersId[$index]);
        }

        if ($this->usersId) {
            $recipients = $this->send();
        }

        return array_unique($recipients);
    }

    /**
     * @return int[]
     */
    protected function send()
    {
        $recipients = [];

        // pobranie ustawien powiadomienia dla userow. byc moze maja oni wylaczone powiadomienie tego typu?
        $users = $this->getUsersSettings();
        $broadcast = ['profile' => app(Broadcast_Db::class), 'email' => app(Broadcast_Email::class)];

        foreach ($users as $user) {
            foreach ($broadcast as $type => $object) {
                if ($user[$type]) {
                    if ($object->send($user, $this)) {
                        $recipients[] = $user['user_id'];
                    }
                }
            }
        }

        return $recipients;
    }

    /**
     * @return \Coyote\Alert\Setting[]
     */
    protected function getUsersSettings()
    {
        // pobranie ustawien powiadomienia dla userow. byc moze maja oni wylaczone powiadomienie tego typu?
        return $this->repository->getUserSettings($this->usersId)->where('type_id', $this->typeId);
    }
}
