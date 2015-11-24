<?php

namespace Coyote\Alert\Providers;

/**
 * Interface ProviderInterface
 * @package Coyote\Alert\Providers
 */
interface ProviderInterface
{
    /**
     * Typ ID powiadomienia
     *
     * @return int
     */
    public function getTypeId();

    /**
     * @param int $userId
     * @return mixed
     */
    public function addUserId($userId);

    /**
     * @param int $userId
     * @return mixed
     */
    public function setUserId($userId);

    /**
     * @param array $usersId
     * @return mixed
     */
    public function setUsersId(array $usersId);

    /**
     * @return array
     */
    public function getUsersId();

    /**
     * Tytul powiadomienia - np. tytul watku na forum czy nazwa oferty pracy
     *
     * @param string $subject
     * @return mixed
     */
    public function setSubject($subject);

    /**
     * @return string
     */
    public function getSubject();

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string
     */
    public function getContent();

    /**
     * Krótka zajawka powiadomienia (np. pierwsze kilkadziesiat znakow wpisu czy komentarza)
     *
     * @param string $excerpt
     * @return mixed
     */
    public function setExcerpt($excerpt);

    /**
     * @return string
     */
    public function getExcerpt();

    /**
     * @return string
     */
    public function getHeadline();

    /**
     * URL do powiadonienie (najlepiej nierelatywny)
     *
     * @param string $url
     * @return mixed
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * ID usera ktory generuje powiadomienie (np. autora posta na forum)
     *
     * @param int $senderId
     * @return mixed
     */
    public function setSenderId($senderId);

    /**
     * @return int
     */
    public function getSenderId();

    /**
     * Nazwa uzytkownika ktory geneuje powiadomienia. Moze to byc login uzytkownika albo nick podany
     * na forum jezeli uzytkownik nie jest zalogowany
     *
     * @param $senderName
     * @return mixed
     */
    public function setSenderName($senderName);

    /**
     * @return string
     */
    public function getSenderName();

    /**
     * @return string
     */
    public function getSender();

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId();

    /**
     * Zwraca nazwe szablonu e-mail dla danego alertu
     *
     * @return string
     */
    public function email();

    /**
     * Generuje powiadomienie oraz zwraca ID userow do ktorych zostalo wyslane
     *
     * @return array
     */
    public function notify();
}
