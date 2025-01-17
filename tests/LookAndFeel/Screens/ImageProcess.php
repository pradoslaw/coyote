<?php
namespace Tests\LookAndFeel\Screens;

class ImageProcess
{

    public function __construct(private string $basePath) {}

    public function mergeHorizontally(array $inputFiles, string $outputFile): void
    {
        $sizes = \array_map($this->getSize(...), $inputFiles);
        $outputHeight = max(array_map(fn(array $size) => $size[1], $sizes));
        $outputWidth = array_sum(array_map(fn(array $size) => $size[0], $sizes));

        $output = imagecreatetruecolor($outputWidth, $outputHeight);

        $lastX = 0;
        foreach ($inputFiles as $filePath) {
            $image = imagecreatefrompng($this->basePath . $filePath);
            $srcSize = $this->getSize($filePath);
            imagecopy($output, $image, $lastX, 0, 0, 0, $srcSize[0], $srcSize[1]);
            $lastX += $srcSize[0];
        }
        imagepng($output, $this->basePath . $outputFile, 0);
    }

    public function mergeVertically(array $inputFiles, string $outputFile): void
    {
        $sizes = \array_map($this->getSize(...), $inputFiles);
        $outputHeight = array_sum(array_map(fn(array $size) => $size[1], $sizes));
        $outputWidth = max(array_map(fn(array $size) => $size[0], $sizes));

        $output = imagecreatetruecolor($outputWidth, $outputHeight);

        $lastY = 0;
        foreach ($inputFiles as $filePath) {
            $image = imagecreatefrompng($this->basePath . $filePath);
            $srcSize = $this->getSize($filePath);
            imagecopy($output, $image, 0, $lastY, 0, 0, $srcSize[0], $srcSize[1]);
            $lastY += $srcSize[1];
        }
        imagepng($output, $this->basePath . $outputFile, 0);
    }

    private function getSize(string $path): array
    {
        [$width, $height] = getimagesize($this->basePath . $path);
        return [$width, $height];
    }
}
