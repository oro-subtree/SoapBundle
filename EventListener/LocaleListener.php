<?php

namespace Oro\Bundle\SoapBundle\EventListener;

use Gedmo\Translatable\TranslatableListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleListener implements EventSubscriberInterface
{
    const API_PREFIX = '/api/rest/';

    /**
     * @var TranslatableListener
     */
    protected $translatableListener;

    /**
     * @param TranslatableListener $translatableListener
     */
    public function __construct(TranslatableListener $translatableListener)
    {
        $this->translatableListener = $translatableListener;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $locale = str_replace('-', '_', $request->query->get('locale'));
        if ($locale && $this->isApiRequest($request)) {
            $request->setLocale($locale);
            $this->translatableListener->setTranslatableLocale($locale);
        }
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function isApiRequest(Request $request)
    {
        return strpos($request->getPathInfo(), self::API_PREFIX) === 0;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after Symfony's original LocaleListener
            KernelEvents::REQUEST  => array(array('onKernelRequest', -17)),
        );
    }
}
