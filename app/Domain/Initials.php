<?php
namespace Coyote\Domain;

readonly class Initials
{
    public function of(string $username): string
    {
        $tokens = $this->usernameTokens($username);
        if (\count($tokens) < 2) {
            return '4p';
        }
        return \mb_strToUpper($this->twoWordInitials($tokens[0], $tokens[1]));
    }

    private function usernameTokens(string $username): array
    {
        $words = $this->usernameWords($username);
        if (\count($words) === 1) {
            return \mb_str_split($words[0]);
        }
        return $words;
    }

    private function twoWordInitials(string $first, string $second): string
    {
        return \mb_subStr($first, 0, 1) . \mb_subStr($second, 0, 1);
    }

    public function usernameWords(string $username): array
    {
        return $this->splitUntilMany($username, [
            '/[^0-9\pL]/u', // split on any special character
            '/\p{Ll}(?=\p{Lu})/u', // split at camelCase
            '/(?=[0-9])/u', // split at digit
        ]);
    }

    private function splitUntilMany(string $string, array $regexes): array
    {
        foreach ($regexes as $regex) {
            $tokens = $this->split($regex, $string);
            if (count($tokens) !== 1) {
                break;
            }
        }
        return $tokens;
    }

    private function split(string $regex, string $subject): array
    {
        return \preg_split($regex, $subject, flags:\PREG_SPLIT_NO_EMPTY);
    }
}
