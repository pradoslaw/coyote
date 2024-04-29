<?php
namespace Neon\View;

readonly class Theme
{
    public string $bodyBackground;

    public string $sectionTitleColor;
    public string $sectionBackground;
    public string $subsectionTitleColor;

    public string $breadcrumbColor;
    public string $breadcrumbSeparator;

    public string $searchBarColor;
    public string $searchBarActiveColor;

    public string $attendanceHighlightBackground;

    public string $addEventBackground;
    public string $addEventHeadingColor;
    public string $addEventParagraphColor;
    public string $addEventLinkColor;

    public string $jobOffersSection;
    public string $jobOffersHeading;
    public string $jobOfferHeading;
    public string $jobOfferTag;

    public string $eventBorder;
    public string $eventStyle;
    public string $eventTag;
    public string $eventDetailsStyle;

    public string $navigationColor;
    public string $navigationControlItem;
    public string $navigationUserAvatarBorder;

    public string $githubButtonBorder;
    public string $githubButtonDivideX;
    public string $navigationDropdownStyle;

    public function __construct(public bool $dark)
    {
        $this->bodyBackground = $dark ? 'bg-[#0f0f0f]' : 'bg-[#f0f2f5]';

        $this->sectionTitleColor = $dark ? 'text-[#eeeeee]' : 'text-black';
        $this->sectionBackground = $dark ? 'bg-black' : 'bg-white';
        $this->subsectionTitleColor = $dark ? 'text-[#849b82]' : 'text-[#053b00]';

        $this->breadcrumbColor = $dark ? 'text-[#a6a6a6]' : '';
        $this->breadcrumbSeparator = 'text-[#00a538]';

        $this->searchBarColor = $dark ? 'text-[#bbbbbb]' : 'text-[#4e5973]';
        $this->searchBarActiveColor = $dark ? 'text-white' : 'text-black';
        $this->attendanceHighlightBackground = $this->dark ? 'bg-[#274707]' : 'bg-[#003211]';

        $this->addEventBackground = $dark ? 'bg-[#171717]' : 'bg-white';
        $this->addEventHeadingColor = $dark ? 'text-white' : 'text-[#070707]';
        $this->addEventParagraphColor = $dark ? 'text-[#949494]' : 'text-[#737578]';
        $this->addEventLinkColor = $dark ? 'text-[#7fff00]' : 'text-[#00a538]';

        $this->jobOffersSection = $dark ? 'p-4 rounded-lg bg-[#171717]' : '';
        $this->jobOffersHeading = $dark ? 'text-[#849b82]' : 'text-[#053b00]';
        $this->jobOfferHeading = $dark ? 'text-white' : 'text-[#4e5973]';

        $this->jobOfferTag = $dark ? 'text-[#808080] bg-[#212121]' : 'text-[#22488c] bg-[#e3e8f1]';

        $this->eventStyle = $dark ? 'text-white bg-[#171717]' : 'bg-white';
        $this->eventBorder = 'border-solid border-l-4 ' . ($dark ? 'border-[#80ff00]' : 'border-[#00a538]');
        $this->eventTag = $dark ? 'text-[#808080] bg-[#212121]' : 'text-[#22488c] bg-[#e3e8f1]';
        $this->eventDetailsStyle = $dark ? 'text-[#afafaf]' : 'text-[#4e5973]';

        $this->navigationColor = $dark ? 'text-[#eeeeee]' : 'text-[#4e5973]';
        $this->navigationControlItem = $dark ? 'text-black bg-[#7fff00] font-medium' : 'text-white bg-[#00a538]';
        $this->navigationUserAvatarBorder = 'border border-solid ' . ($dark ? 'border-[#2d2d2d]' : 'border-[#e2e2e2]');
        $this->navigationDropdownStyle = $dark ? 'bg-[#171717]' : 'bg-[#f0f2f5]';

        $this->githubButtonBorder = 'border border-solid ' . ($dark ? 'border-[#2d2d2d]' : 'border-[#e2e2e2]');
        $this->githubButtonDivideX = 'divide-x ' . ($dark ? 'divide-[#2d2d2d]' : '');
    }
}
