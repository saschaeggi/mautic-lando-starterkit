<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Test\Service;

use Mautic\CoreBundle\Model\NotificationModel;
use Mautic\CoreBundle\Service\FlashBag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag as SymfonyFlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class FlashBagTest extends TestCase
{
    /**
     * @var MockObject|SymfonyFlashBag
     */
    private $symfonyFlashBag;

    /**
     * @var MockObject|Session
     */
    private $session;

    /**
     * @var MockObject|TranslatorInterface
     */
    private $translator;

    /**
     * @var MockObject|RequestStack
     */
    private $requestStack;

    /**
     * @var NotificationModel|MockObject
     */
    private $notificationModel;

    /**
     * @var FlashBag
     */
    private $flashBag;

    protected function setUp(): void
    {
        $this->symfonyFlashBag  = $this->createMock(SymfonyFlashBag::class);

        $this->session = $this->createMock(Session::class);
        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($this->symfonyFlashBag);
        $this->translator        = $this->createMock(TranslatorInterface::class);
        $this->requestStack      = $this->createMock(RequestStack::class);
        $this->notificationModel = $this->createMock(NotificationModel::class);
        $this->flashBag          = new FlashBag($this->session, $this->translator, $this->requestStack, $this->notificationModel);

        parent::setUp();
    }

    public function testAddWithoutVars(): void
    {
        $message         = 'message';
        $messageVars     = [];
        $level           = FlashBag::LEVEL_NOTICE;
        $domain          = false;
        $addNotification = false;

        $this->symfonyFlashBag
            ->expects($this->once())
            ->method('add')
            ->with($level, $message);

        $this->flashBag->add($message, $messageVars, $level, $domain, $addNotification);
    }

    public function testAddWithChoices(): void
    {
        $message                    = 'message';
        $messageVars['pluralCount'] = 2;
        $translatedMessage          = 'translatedMessage';
        $level                      = FlashBag::LEVEL_NOTICE;
        $domain                     = 'flashes';
        $addNotification            = false;

        $this->symfonyFlashBag
            ->expects($this->once())
            ->method('add')
            ->with($level, $translatedMessage);

        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($this->symfonyFlashBag);

        $this->translator
            ->expects($this->once())
            ->method('transChoice')
            ->with($message, $messageVars['pluralCount'], $messageVars, $domain)
            ->willReturn($translatedMessage);

        $this->flashBag->add($message, $messageVars, $level, $domain, $addNotification);
    }

    public function testAddWithTranslation(): void
    {
        $message            = 'message';
        $messageVars        = [];
        $translatedMessage  = 'translatedMessage';
        $level              = FlashBag::LEVEL_NOTICE;
        $domain             = 'flashes';
        $addNotification    = false;

        $this->symfonyFlashBag
            ->expects($this->once())
            ->method('add')
            ->with($level, $translatedMessage);

        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($this->symfonyFlashBag);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($message, $messageVars, $domain)
            ->willReturn($translatedMessage);

        $this->flashBag->add($message, $messageVars, $level, $domain, $addNotification);
    }

    public function testReadStatusRead(): void
    {
        $this->assertReadStatus(1, true);
    }

    public function testReadStatusUnread(): void
    {
        $this->assertReadStatus(31, false);
    }

    public function testAddTypeError(): void
    {
        $this->assertAddTypeCases(FlashBag::LEVEL_ERROR, 'text-danger fa-exclamation-circle');
    }

    public function testAddTypeNotice(): void
    {
        $this->assertAddTypeCases(FlashBag::LEVEL_NOTICE, 'fa-info-circle');
    }

    public function testAddTypeDefault(): void
    {
        $this->assertAddTypeCases('default', 'fa-info-circle');
    }

    private function assertReadStatus(int $mauticUserLastActive, bool $isRead): void
    {
        $message            = 'message';
        $messageVars        = [];
        $level              = FlashBag::LEVEL_NOTICE;
        $translatedMessage  = 'translatedMessage';
        $domain             = 'flashes';
        $addNotification    = true;

        $this->symfonyFlashBag
            ->expects($this->once())
            ->method('add')
            ->with($level, $translatedMessage);

        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($this->symfonyFlashBag);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($message, $messageVars, $domain)
            ->willReturn($translatedMessage);

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('get')
            ->with('mauticUserLastActive', 0)
            ->willReturn($mauticUserLastActive);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->notificationModel
            ->expects($this->once())
            ->method('addNotification')
            ->with($message, $level, $isRead, null, 'fa-info-circle');

        $this->flashBag->add($message, $messageVars, $level, $domain, $addNotification);
    }

    private function assertAddTypeCases($level, $expectedIcon): void
    {
        $message              = 'message';
        $messageVars          = [];
        $translatedMessage    = 'translatedMessage';
        $domain               = 'flashes';
        $addNotification      = true; // <---
        $mauticUserLastActive = 1; // <---

        $this->symfonyFlashBag
            ->expects($this->once())
            ->method('add')
            ->with($level, $translatedMessage);

        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($this->symfonyFlashBag);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($message, $messageVars, $domain)
            ->willReturn($translatedMessage);

        $request = $this->createMock(Request::class);
        $request
            ->expects($this->once())
            ->method('get')
            ->with('mauticUserLastActive', 0)
            ->willReturn($mauticUserLastActive);

        $this->requestStack
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->notificationModel
            ->expects($this->once())
            ->method('addNotification')
            ->with($message, $level, 1, null, $expectedIcon);

        $this->flashBag->add($message, $messageVars, $level, $domain, $addNotification);
    }
}
