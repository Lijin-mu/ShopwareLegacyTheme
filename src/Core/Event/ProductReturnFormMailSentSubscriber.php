<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Event;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\MailTemplate\Service\Event\MailSentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductReturnFormMailSentSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MailSentEvent::EVENT_NAME => 'onMailSent',
        ];
    }

    public function onMailSent(MailSentEvent $event): void
    {
        // Log when product return form emails are sent
        $subject = $event->getSubject();

        if (str_contains($subject, 'Product Return') || str_contains($subject, 'Retourenanfrage')) {
            $this->logger->info('Product return form email sent successfully', [
                'subject' => $subject,
                'recipients' => $event->getRecipients(),
            ]);
        }
    }
}

