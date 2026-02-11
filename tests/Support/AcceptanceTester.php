<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /** use $I->amOnPage('...') or $I->reloadPage() for cookie update */
    public function forceLogin(User $user): void
    {
        $session = $this->grabService('session.factory')->createSession();
        $firewall = 'main';

        $token = new UsernamePasswordToken($user, $firewall, $user->getRoles());
        $session->set('_security_' . $firewall, \serialize($token));
        $session->save();

        $this->amOnPage('/login');
        $this->setCookie($session->getName(), $session->getId());
    }

    /**
     * Define custom actions here
     */
}
