<?php declare(strict_types = 1);

namespace App\Controller\Security;

use App\Controller\AbstractController;
use LogicException;

class LogoutController extends AbstractController
{
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
