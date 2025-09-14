<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

final class IssueCacheAttributeController extends AbstractController
{
    #[Cache(vary: ['foo'], public: true, maxage: 15)]
    #[Route('/issue/cache/attribute', name: 'app_issue_cache_attribute')]
    public function index(): JsonResponse
    {
        return $this->json(rand(1,100));
    }
}
