<?php
namespace Coyote\Domain;

readonly class InitialsSvg
{
    public function __construct(private string $initials)
    {
    }

    public function imageSvg(): string
    {
        return <<<svg
          <div class="user-avatar default-avatar">
            <svg viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
              <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="currentColor">
                $this->initials
              </text>
            </svg>
          </div>
          svg;
    }
}
