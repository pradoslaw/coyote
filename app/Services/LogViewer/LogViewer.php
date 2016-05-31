<?php

namespace Coyote\Services\LogViewer;

use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class LogViewer
{
    const REGEXP_DATE = '\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]';

    /**
     * @var array
     */
    protected $levels = [
        'DEBUG',
        'INFO',
        'NOTICE',
        'WARNING',
        'ERROR',
        'CRITICAL',
        'ALERT',
        'EMERGENCY'
    ];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        $result = [];

        foreach ($this->filesystem->files('/') as $file) {
            if (substr($file, 0, 7) === 'laravel') {
                $result[] = $file;
            }
        }

        return array_reverse($result);
    }

    /**
     * @param string $file
     * @return Data
     */
    public function read($file)
    {
        $result = [];

        if (!$this->filesystem->exists($file)) {
            throw new FileNotFoundException(sprintf('Could not find %s file in logs.', $file));
        }

        $lines = explode("\n", $this->filesystem->get($file));
        $lines = $this->removeUnnecessaryLines($lines);

        for ($i = 0, $count = count($lines); $i < $count; $i++) {
            $line = $lines[$i];

            if ($this->isStackMessage($line)) {
                $stack[] = $line;
                unset($current);
            } elseif ($this->isBeginningOfMessage($line)) {
                $message = $this->findEndOfMessage($i, $lines);

                $result[] = $this->init($message);
                $current = &$result[count($result) - 1];

                if ($this->parseMessage($message, $matches)) {
                    $current = $this->compile($matches);
                }

                $stack = &$result[count($result) - 1]['stack'];
            }
        }

        return new Data($result);
    }

    /**
     * @param array $lines
     * @return array
     */
    protected function removeUnnecessaryLines(array $lines)
    {
        foreach ($lines as $number => $line) {
            if ('Stack trace:' === $line) {
                unset($lines[$number]);
            }
        }

        return array_values($lines);
    }

    /**
     * @param string $line
     * @return bool
     */
    protected function isBeginningOfMessage($line)
    {
        return trim($line) === '' || preg_match('/^' . self::REGEXP_DATE . '.*/', $line);
    }

    /**
     * @param int $pointer
     * @param array $lines
     * @return string
     */
    protected function findEndOfMessage(&$pointer, &$lines)
    {
        $message = $lines[$pointer];

        while (!empty($lines[$pointer + 1]) && !$this->isStackMessage($lines[$pointer + 1])
            && !$this->isBeginningOfMessage($lines[$pointer + 1])) {
            $message .= $lines[++$pointer];
        }

        return $message;
    }

    /**
     * @param string $line
     * @return bool
     */
    protected function isStackMessage($line)
    {
        return strlen($line) > 0 && $line[0] === '#';
    }

    /**
     * @param $line
     * @param $matches
     * @return int
     */
    protected function parseMessage($line, &$matches)
    {
        return preg_match(
            sprintf(
                '/^%s [a-z]+\.(%s): (.*?)( in (.*)?:([0-9]+))?$/',
                self::REGEXP_DATE,
                implode('|', $this->levels)
            ),
            $line,
            $matches
        );
    }

    /**
     * @param null|string $message
     * @return array
     */
    protected function init($message = null)
    {
        return [
            'date' => '',
            'level' => '',
            'message' => $message,
            'file' => '',
            'line' => '',
            'stack' => []
        ];
    }

    /**
     * @param array $matches
     * @return array
     */
    protected function compile($matches)
    {
        return array_combine(
            array_keys($this->init()),
            [$matches[1], $matches[2], $matches[3], @$matches[5], @$matches[6], []]
        );
    }
}
