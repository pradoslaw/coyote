<?php
namespace Coyote\Services\Parser\Extensions;

class Emoji
{
    public static function exists(string $code): bool
    {
        return \array_key_exists($code, self::resourceFile()['emoticons']);
    }

    public static function fromUnicodeCharacter(string $unicode): ?Emoji
    {
        foreach (self::resourceFile()['emoticons'] as $emoticon) {
            if ($emoticon['native'] === $unicode) {
                return new Emoji($emoticon['id']);
            }
        }
        return null;
    }

    public static function all(): array
    {
        return self::resourceFile();
    }

    private static function resourceFile(): array
    {
        static $resourceFile;
        if ($resourceFile === null) {
            $resourceFile = self::loadEmojiFile();
        }
        return $resourceFile;
    }

    private static function loadEmojiFile(): mixed
    {
        return \json_decode(\file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'emoji.json'), true);
    }

    public string $title;
    public string $unified;
    public string $unicodeCharacter;

    public function __construct(string $name)
    {
        $file = self::resourceFile();
        $this->title = $file['emoticons'][$name]['name'];
        $this->unified = $file['emoticons'][$name]['unified'];
        $this->unicodeCharacter = $file['emoticons'][$name]['native'];
    }
}
