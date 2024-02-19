<?php
namespace Coyote\Domain\Github;

class GithubStars
{
    public function fetchStars(): ?int
    {
        $result = @\file_get_contents(
            'https://api.github.com/repos/pradoslaw/coyote',
            false,
            \stream_context_create([
                'http' => ['header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'],
            ]));
        if ($result !== false) {
            $data = @\json_decode($result, true);
            if ($data !== null) {
                return $data['stargazers_count'];
            }
        }
        return null;
    }
}
