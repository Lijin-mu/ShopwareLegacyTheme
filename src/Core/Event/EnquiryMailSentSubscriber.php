<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Event;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\MailTemplate\Service\Event\MailSentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EnquiryMailSentSubscriber implements EventSubscriberInterface
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
        // Log when enquiry form emails are sent
        $subject = $event->getSubject();
        
        if (str_contains($subject, 'Enquiry') || str_contains($subject, 'Anfrage')) {
            $this->logger->info('Enquiry form email sent successfully', [
                'subject' => $subject,
                'recipients' => $event->getRecipients(),
            ]);
        }
    }
}

