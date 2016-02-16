<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        if($request->query->has('target')) {
            $target = $request->query->get('target');
            if($target[0] !== '/')
                $target = '/'.$target;
            $request->getSession()->set('_security.main.target_path', $request->getUriForPath($target));
        }
        return $this->redirectToRoute('hwi_oauth_service_redirect', ['service' => 'vl_auth_client']);
    }
}
