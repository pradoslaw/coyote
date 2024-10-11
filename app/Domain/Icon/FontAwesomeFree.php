<?php
namespace Coyote\Domain\Icon;

readonly class FontAwesomeFree
{
    public function icons(): array
    {
        $genericDropdown = 'fa fa-ellipsis';

        return [
            // global
            'breadcrumbRoot'                            => 'fas fa-house',
            'scrollTop'                                 => 'fas fa-arrow-up',

            // alert
            'alertDanger'                               => 'fas fa-triangle-exclamation',
            'alertSuccess'                              => 'fas fa-check',

            // registration
            'loginGoogle'                               => 'fab fa-google',
            'loginFacebook'                             => 'fab fa-facebook',
            'loginGithub'                               => 'fab fa-github',

            // homepage
            'microblogsPopular'                         => 'far fa-comments',
            'reputationRanking'                         => 'fas fa-star',
            'forumNews'                                 => 'fas fa-star',
            'viewersOnlineLocal'                        => 'fas fa-eye',
            'viewersOnlineGlobal'                       => 'fas fa-users',

            // microblog page
            'recommendedUsers'                          => 'fas fa-tag',

            // microblog
            'microblogNew'                              => 'fa-solid fa-plus',
            'microblogMine'                             => 'fa-regular fa-address-book',

            // navigation dropdown
            'sponsorProfile'                            => 'fa fa-medal',
            'userProfile'                               => 'fas fa-user',
            'privateMessages'                           => 'fas fa-envelope',
            'userAccount'                               => 'fas fa-gear',
            'help'                                      => 'fas fa-circle-info',
            'adminPanel'                                => 'fas fa-user-tie',
            'logout'                                    => 'fas fa-right-from-bracket',

            // navigation menu
            'mobileMenuOpen'                            => 'fa-solid fa-bars',
            'mobileMenuClose'                           => 'fa-solid fa-xmark',

            // private messages
            'privateMessageTyping'                      => 'far fa-comment-dots',

            // user profile
            'userReputation'                            => 'fas fa-chart-line',
            'userLastVisitDate'                         => 'fas fa-right-to-bracket',
            'userRegisterDate'                          => 'fas fa-user-plus',
            'userIpAddress'                             => 'fas fa-house',
            'userVisits'                                => 'far fa-eye',

            // user account settings
            'userAccount.userAccount'                   => 'far fa-user',
            'userAccount.skills'                        => 'far fa-address-book',
            'userAccount.privateMessageList'            => 'far fa-envelope',
            'userAccount.notificationList'              => 'fas fa-bell',
            'userAccount.postVotes'                     => 'far fa-thumbs-up',
            'userAccount.postCategories'                => 'fas fa-chart-bar',
            'userAccount.postAccepts'                   => 'fas fa-check',
            'userAccount.subscribedPages'               => 'far fa-bell', // topics, jobOffers, microblogs
            'userAccount.relations'                     => 'fas fa-user-group',
            'userAccount.notificationSettings'          => 'fas fa-bell',
            'userAccount.miscellaneousSettings'         => 'fas fa-user-gear',
            'userAccount.passwordChange'                => 'fas fa-unlock-keyhole',
            'userAccount.access'                        => 'fas fa-door-open',
            'userAccount.apiTokens'                     => 'fas fa-key',
            'userAccount.accountDelete'                 => 'fas fa-trash-can',

            // payment
            'paymentSecureConnection'                   => 'fas fa-lock',
            'paymentNotNecessary'                       => 'fas fa-check',
            'paymentInvoiceData'                        => 'fas fa-lock',
            'paymentBackToOffer'                        => 'fas fa-angle-left',
            'paymentSaveAndPay'                         => 'fas fa-angle-right',
            'paymentCoupon'                             => 'fas fa-circle-dollar-to-slot',

            // error page
            'errorPageBackToHomepage'                   => 'fas fa-house',
            'errorPageContactUs'                        => 'fas fa-info',
            'errorPageNeedHelp'                         => 'fas fa-asterisk',

            // tags
            'tagsSubscribed'                            => 'fas fa-tag',
            'tagsSubscribedEdit'                        => 'fas fa-gear',
            'tagsPopularForum'                          => 'fas fa-tag',
            'tagsPopularMicroblog'                      => 'fas fa-tag',
            'tagsPopularLanguage'                       => 'fas fa-wrench',

            // forum
            'forumSidebarMobileMenu'                    => 'fa-solid fa-ellipsis-vertical',
            'forumChangeCategory'                       => 'fas fa-circle-arrow-right',
            'forumActions'                              => 'fa-solid fa-circle-dot',
            'forumGlobalMarkAsRead'                     => 'far fa-eye',
            'forumCategoryMarkAsRead'                   => 'far fa-eye',
            'forumTopicMarkAsRead'                      => 'far fa-eye',

            // topic
            'topicSubscribe'                            => 'far fa-bell',
            'topicSubscribed'                           => 'fas fa-bell',
            'topicLog'                                  => 'fa fa-chart-pie',
            'topicGoToBeginning'                        => 'fa-solid fa-backward-fast',
            'topicLogBackToTopic'                       => 'fas fa-backward-step',
            'topicActionRename'                         => 'fa fa-pencil',
            'topicActionMove'                           => 'fa fa-circle-arrow-right',

            // topic log
            "topicLogUserAgent"                         => "fas fa-globe",
            "topicLogUserFingerprint"                   => "fas fa-info",
            "topicLogUserIp"                            => "fas fa-laptop",

            // topic post
            'postSubscribe'                             => 'far fa-bell',
            'postShare'                                 => 'fas fa-share-nodes',
            'postComment'                               => 'far fa-comment',

            // profile
            'profileReputationHistory'                  => 'fa-solid fa-chart-line',
            'profileReputationActivity'                 => 'fa-solid fa-list-check',
            'profileReputationGain'                     => 'fas fa-level-up',
            'profileReputationLose'                     => 'fas fa-level-down',
            'profileActions'                            => 'fa-solid fa-handshake-simple',
            'profileUserSendMessage'                    => 'far fa-envelope',
            'profileUserFindPosts'                      => 'fas fa-magnifying-glass',
            'profileUserBan'                            => 'fas fa-user-lock',
            'profileUserShowInAdmin'                    => 'fas fa-eye',
            'profileMenuDropdown'                       => $genericDropdown,
            'profileUserBlock'                          => 'fas fa-user-slash',
            'profileUserUnblock'                        => 'fas fa-user',
            'profileUserResidence'                      => 'fas fa-location-dot',
            'profileUserWebsite'                        => 'fas fa-globe',
            'profileUserGithub'                         => 'fab fa-github',
            'profileUserLastVisitDate'                  => 'fas fa-right-to-bracket',
            'profileUserRegisterDate'                   => 'fas fa-user-plus',
            'profileUserVisits'                         => 'far fa-eye',
            'profileUserAge'                            => 'fas fa-calendar-days',
            'profileUserMicroblogs'                     => 'fa-regular fa-address-book',
            'profileUserPermissions'                    => 'fa fa-medal',
            'profileUserPermissionGranted'              => 'fa fa-check',
            'profileUserPermissionDenied'               => 'fa fa-xmark',

            // job board
            'jobBoardLoading'                           => 'fas fa-spinner',
            "jobBoardFilterRemote"                      => "fas fa-wifi",
            "jobBoardFilterSalary"                      => "far fa-credit-card",
            "jobBoardPackageBenefit"                    => "fas fa-check",
            "jobBoardSearch"                            => "fas fa-magnifying-glass",
            "jobBoardSearchLocation"                    => "fas fa-location-dot",
            "jobBoardSubscribedOffers"                  => "fas fa-heart",

            // job landing
            "jobLandingBenefit"                         => "fas fa-circle-check",
            "jobLandingCommunity"                       => "fas fa-users",
            "jobLandingForumIt"                         => "fas fa-graduation-cap",
            "jobLandingKnowledgeShareEstablishmentYear" => "fas fa-comments",
            "jobLandingMonthlyViews"                    => "fas fa-arrow-pointer",

            // job offer
            "jobOfferAdditionalQuestions"               => "fas fa-circle-info",
            "jobOfferBack"                              => "fas fa-backward",
            "jobOfferBenefit"                           => "fas fa-check",
            "jobOfferComments"                          => "fas fa-question-circle-o",
            "jobOfferContract"                          => "far fa-handshake",
            "jobOfferDetails"                           => "fas fa-circle-info",
            "jobOfferDraftSave"                         => "fas fa-floppy-disk",
            "jobOfferDraftTabNext"                      => "fas fa-angle-right",
            "jobOfferDraftTabPrev"                      => "fas fa-angle-left",
            "jobOfferEdit"                              => "fas fa-pen-to-square",
            "jobOfferExpiresInDays"                     => "far fa-clock",
            "jobOfferFeatureMissing"                    => "fas fa-xmark",
            "jobOfferFeaturePresent"                    => "fas fa-check",
            "jobOfferLocation"                          => "fas fa-location-dot",
            "jobOfferMoreLikeThis"                      => "fas fa-star",
            "jobOfferNew"                               => "fas fa-pencil",
            "jobOfferPaymentRequired"                   => "fas fa-credit-card",
            "jobOfferPublishDate"                       => "fas fa-calendar-days",
            "jobOfferRemove"                            => "fas fa-trash-can",
            "jobOfferReport"                            => "fas fa-flag",
            "jobOfferRequirementProgress"               => "fas fa-circle",
            "jobOfferSeniority"                         => "fas fa-fw fa-chart-line",
            "jobOfferSubscribe"                         => "fa-regular fa-bell",
            "jobOfferSubscribed"                        => "fa-solid fa-bell",
            "jobOfferViews"                             => "far fa-eye",
            "jobOfferCompanyEmployees"                  => "fas fa-users",
            "jobOfferCompanyEstablishmentYear"          => "far fa-calendar",
            "jobOfferCompanyWebsite"                    => "fas fa-link",

            // vcard
            "vCardLastVisitDate"                        => "fas fa-right-to-bracket",
            "vCardPosts"                                => "far fa-comments",
            "vCardRegisterDate"                         => "fas fa-user-plus",
            "vCardReputation"                           => "fas fa-chart-line",
            "vCardUserBlock"                            => "fas fa-user-lock",
            "vCardUserFindPosts"                        => "fas fa-magnifying-glass",
            "vCardUserPrivateMessage"                   => "fas fa-envelope",
            "vCardUserShowInAdmin"                      => "fas fa-eye",

            // wiki
            "wikiAttachmentRemove"                      => "fas fa-trash-can",
            "wikiAuthor"                                => "fas fa-person",
            "wikiAuthors"                               => "fas fa-users",
            "wikiCategories"                            => "fas fa-folder-open",
            "wikiCategory"                              => "fas fa-paragraph",
            "wikiChildCreate"                           => "fas fa-plus",
            "wikiClearCache"                            => "far fa-trash-can",
            "wikiCommentEdit"                           => "fas fa-pen-to-square",
            "wikiCommentRemove"                         => "fas fa-trash-can",
            "wikiComments"                              => "fas fa-comments",
            "wikiCopyCreate"                            => "far fa-clone",
            "wikiCopyRemove"                            => "fas fa-chain-broken",
            "wikiEdit"                                  => "fas fa-pen-to-square",
            "wikiMove"                                  => "fas fa-circle-right",
            "wikiRemove"                                => "fas fa-trash-can",
            "wikiSubscribe"                             => "fa-regular fa-bell",
            "wikiCreateDate"                            => "far fa-calendar-days",
            "wikiViews"                                 => "far fa-eye",
            "wikiLastUpdateDate"                        => "far fa-calendar",
            "wikiRecentChanges"                         => "far fa-calendar-o",
            "wikiMoreLikeThis"                          => "far fa-eye",
            "wikiRelated"                               => "fas fa-thumbtack",
            "wikiVersionCompare"                        => "far fa-calendar-o",
            "wikiVersionRestore"                        => "fas fa-arrow-rotate-left",
            "wikiVersionsAndAuthors"                    => "fas fa-clock-rotate-left",
            "wikiEditorActionAnchor"                    => "fas fa-link",
            "wikiEditorActionBold"                      => "fas fa-bold",
            "wikiEditorActionCodeBlock"                 => "fas fa-code",
            "wikiEditorActionHeading"                   => "fas fa-heading",
            "wikiEditorActionImage"                     => "fas fa-image",
            "wikiEditorActionInlineCode"                => "fas fa-text-width",
            "wikiEditorActionItalics"                   => "fas fa-italic",
            "wikiEditorActionListOrdered"               => "fas fa-list-ol",
            "wikiEditorActionListUnordered"             => "fas fa-list-ul",
            "wikiEditorActionQuote"                     => "fas fa-quote-left",
            "wikiEditorActionTable"                     => "fas fa-table",
            "wikiEditorActionUnderline"                 => "fas fa-underline",
            "wikiEditorHelp"                            => "fas fa-question",

            // survey
            'surveyExperiment'                          => 'fa fa-solid fa-flask',
            'surveyExperimentNew'                       => 'fa-solid fa-plus',
            'surveyExperimentBack'                      => 'fa-solid fa-arrow-left',
            'surveyExperimentMemberRemove'              => 'fa-solid fa-trash',
            'surveyExperimentMembersSave'               => 'fa-solid fa-check',

            // admin panel
            'adminTickMark'                             => 'fa-solid fa-check',
            'adminCrossMark'                            => 'fa-solid fa-xmark',
            'adminMaterialPost'                         => 'fas fa-arrow-right',
            'adminMaterialPostDropdown'                 => $genericDropdown,

            // admin material
            "adminMaterialPostBack"                     => "fas fa-arrow-left",
            "adminMaterialReported"                     => "far fa-flag",
            "adminMaterialSearch"                       => "fas fa-magnifying-glass",

            // admin material history type
            'logItemCreated'                            => 'far fa-comment', // posts, comments, microblogs
            'logItemDeleted'                            => 'far fa-trash-can',
            'logItemReported'                           => 'far fa-flag',
            'logReportClosed'                           => 'fas fa-check',

            // admin content marker
            'contentMarkerQuote'                        => 'fas fa-reply-all',
            'contentMarkerVideo'                        => 'fas fa-film',
            'contentMarkerTable'                        => 'fas fa-table',
            'contentMarkerIFrame'                       => 'far fa-window-maximize',
            'contentMarkerCode'                         => 'fas fa-code',
            'contentMarkerImage'                        => 'far fa-image',
            'contentMarkerHeading'                      => 'fas fa-heading',

            // admin censore
            "adminCensoreNew"                           => "fas fa-plus",
            "adminCensoreRemove"                        => "far fa-trash-can",

            // admin user
            "adminUserAccountSettings"                  => "fas fa-user-gear",
            "adminUserFindFingerprints"                 => "fas fa-fingerprint",
            "adminUserFindInLog"                        => "fa fa-newspaper",
            "adminUserGroupSettings"                    => "fa fa-users",
            "adminUserMicroblogs"                       => "far fa-comments",
            "adminUserPostComments"                     => "far fa-comments",
            "adminUserPosts"                            => "far fa-comment",
            "adminUserReportReceived"                   => "far fa-flag",
            "adminUserReportSent"                       => "fas fa-flag",
            "adminUserSettings"                         => "fa fa-gear",
            "adminUserShowProfile"                      => "fa fa-user",
            "adminUserStatistics"                       => "fas fa-chart-line",
            "adminUserStatisticsAllTime"                => "far fa-calendar-check",
            "adminUserStatisticsLastDay"                => "fas fa-calendar-day",
            "adminUserStatisticsLastMonth"              => "fas fa-calendar-days",
            "adminUserStatisticsLastWeek"               => "fas fa-calendar-week",
            "adminUserStatisticsLastYear"               => "far fa-calendar",

            // footer
            'footerContactUs'                           => 'fa fa-circle-info',
            'footerPromoteFacebook'                     => 'fab fa-facebook',
        ];
    }
}
