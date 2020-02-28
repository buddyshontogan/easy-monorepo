<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Modifiers;

use EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\ProviderProviderInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProviderFromJwtModifier extends AbstractContextModifier
{
    /**
     * @var \EonX\EasySecurity\Interfaces\ProviderProviderInterface
     */
    private $providerProvider;

    /**
     * ProviderFromJwtDataResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ProviderProviderInterface $providerProvider
     * @param null|int $priority
     */
    public function __construct(ProviderProviderInterface $providerProvider, ?int $priority = null)
    {
        $this->providerProvider = $providerProvider;

        parent::__construct($priority);
    }

    /**
     * Modify given context for given request.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextInterface $context
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return void
     */
    public function modify(ContextInterface $context, Request $request): void
    {
        $token = $context->getToken();

        // Work only for JWT
        if ($token instanceof JwtEasyApiTokenInterface === false) {
            return;
        }

        /** @var \EonX\EasyApiToken\Interfaces\Tokens\JwtEasyApiTokenInterface $token */

        $user = $this->getClaimSafely($token, ContextInterface::JWT_MANAGE_CLAIM, []);
        $providerId = $user['provider'] ?? null;

        // If no providerId given in token, skip
        if (empty($providerId)) {
            return;
        }

        $context->setProvider($this->providerProvider->getProvider($providerId));
    }
}